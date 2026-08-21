<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Lương — mọi nhân viên đã đăng nhập xem được lương của chính mình theo
 * tháng (index()); các thao tác quản trị (cấu hình lương, ngày nghỉ/giờ làm,
 * ứng lương/trạng thái chi lương) chỉ ADMIN, tự chặn bên trong từng method —
 * cùng cách Inventory_items::_require_admin().
 */
class Payroll extends MY_Controller
{
    // $allowed_roles để trống -> mọi role đã đăng nhập đều xem được lương của mình.

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('payroll');
        $this->load->model(array('Payroll_setting_model', 'Payroll_record_model', 'Payroll_hours_model', 'Payroll_absence_model', 'User_model'));
    }

    private function _require_admin()
    {
        if ($this->current_user['role'] !== 'ADMIN')
        {
            $this->output->set_status_header(403);
            echo $this->load->view('errors/forbidden', array('current_user' => $this->current_user), TRUE);
            exit;
        }
    }

    /** $date ('YYYY-MM-DD') có nằm trong khoảng làm việc của nhân viên không — thiếu start_date/end_date coi như không giới hạn phía đó. */
    private function _within_employment($date, $user)
    {
        if ( ! empty($user['start_date']) && $date < $user['start_date'])
        {
            return FALSE;
        }
        if ( ! empty($user['end_date']) && $date > $user['end_date'])
        {
            return FALSE;
        }
        return TRUE;
    }

    /** $total_hours cho HOURLY, $absence_days cho FIXED — luôn tính cả 2 và bỏ qua giá trị không dùng cho gọn code gọi. */
    private function _compute($settings, $record, $user_id, $period)
    {
        $total_hours = $settings['salary_type'] === 'HOURLY' ? $this->Payroll_hours_model->sum_hours($user_id, $period) : 0;
        $absence_days = $settings['salary_type'] === 'FIXED' ? $this->Payroll_absence_model->sum_by_user_period($user_id, $period) : 0;
        return payroll_compute($settings, $record, $total_hours, $absence_days, $period);
    }

    /** Xem lương của chính mình theo tháng — mọi role. */
    public function index()
    {
        $period = $this->input->get('period') ?: date('Y-m');
        $user_id = $this->current_user['id'];

        $settings = $this->Payroll_setting_model->get_by_user_or_default($user_id);
        $record = $this->Payroll_record_model->get_by_user_period_or_default($user_id, $period);

        $data = array(
            'page_title'   => 'Lương',
            'current_user' => $this->current_user,
            'period'       => $period,
            'settings'     => $settings,
            'salary'       => $this->_compute($settings, $record, $user_id, $period),
        );
        $this->load->view('layout/header', $data);
        $this->load->view('payroll/index', $data);
        $this->load->view('layout/footer');
    }

    /**
     * Chi tiết ngày làm/nghỉ của chính mình theo tháng — mọi role, chỉ xem
     * (sửa vẫn chỉ ADMIN qua hours()). HOURLY liệt kê từng ngày có giờ làm,
     * FIXED liệt kê từng ngày nghỉ (cả ngày/nửa ngày).
     */
    public function detail()
    {
        $period = $this->input->get('period') ?: date('Y-m');
        $user_id = $this->current_user['id'];
        $settings = $this->Payroll_setting_model->get_by_user_or_default($user_id);

        $weekday_labels = array(1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7', 7 => 'CN');
        $hour_entries = array();
        $off_entries = array();

        if ($settings['salary_type'] === 'HOURLY')
        {
            $hours_by_date = $this->Payroll_hours_model->get_by_user_period($user_id, $period);
            ksort($hours_by_date);
            foreach ($hours_by_date as $date => $hours)
            {
                $hour_entries[] = array(
                    'date'          => $date,
                    'day'           => (int) date('j', strtotime($date)),
                    'weekday_label' => $weekday_labels[(int) date('N', strtotime($date))],
                    'hours'         => $hours,
                );
            }
        }
        else
        {
            $off_by_date = $this->Payroll_absence_model->get_by_user_period($user_id, $period);
            ksort($off_by_date);
            foreach ($off_by_date as $date => $fraction)
            {
                $off_entries[] = array(
                    'date'          => $date,
                    'day'           => (int) date('j', strtotime($date)),
                    'weekday_label' => $weekday_labels[(int) date('N', strtotime($date))],
                    'fraction'      => $fraction,
                );
            }
        }

        $data = array(
            'page_title'    => 'Chi tiết lương',
            'current_user'  => $this->current_user,
            'period'        => $period,
            'settings'      => $settings,
            'hour_entries'  => $hour_entries,
            'off_entries'   => $off_entries,
            'total_hours'   => $settings['salary_type'] === 'HOURLY' ? $this->Payroll_hours_model->sum_hours($user_id, $period) : 0,
            'total_off'     => $settings['salary_type'] === 'FIXED' ? $this->Payroll_absence_model->sum_by_user_period($user_id, $period) : 0,
        );
        $this->load->view('layout/header', $data);
        $this->load->view('payroll/detail', $data);
        $this->load->view('layout/footer');
    }

    /** Bảng tổng hợp lương tất cả nhân viên đang hoạt động trong 1 tháng — ADMIN. */
    public function admin()
    {
        $this->_require_admin();
        $period = $this->input->get('period') ?: date('Y-m');
        $keyword = trim((string) $this->input->get('q'));

        $users = $this->User_model->get_all(NULL, 'ACTIVE', $keyword ?: NULL);
        $records_by_user = $this->Payroll_record_model->get_all_by_period($period);

        $rows = array();
        foreach ($users as $u)
        {
            $settings = $this->Payroll_setting_model->get_by_user_or_default($u['id']);
            $record = isset($records_by_user[$u['id']])
                ? $records_by_user[$u['id']]
                : $this->Payroll_record_model->get_by_user_period_or_default($u['id'], $period);

            $rows[] = array(
                'user'   => $u,
                'salary' => $this->_compute($settings, $record, $u['id'], $period),
            );
        }

        $data = array(
            'page_title'   => 'Quản lý lương',
            'current_user' => $this->current_user,
            'period'       => $period,
            'keyword'      => $keyword,
            'rows'         => $rows,
        );
        $this->load->view('layout/header', $data);
        $this->load->view('payroll/admin_index', $data);
        $this->load->view('layout/footer');
    }

    /** Cấu hình loại lương/mức lương + thông tin ngân hàng của 1 nhân viên — ADMIN. */
    public function settings($user_id)
    {
        $this->_require_admin();
        $user = $this->User_model->get_by_id($user_id);
        if ( ! $user) show_404();

        if ($this->input->method() === 'post')
        {
            $data = array(
                'salary_type'         => $this->input->post('salary_type') === 'HOURLY' ? 'HOURLY' : 'FIXED',
                'fixed_salary'        => (float) $this->input->post('fixed_salary'),
                'hourly_rate'         => (float) $this->input->post('hourly_rate'),
                'bank_name'           => $this->input->post('bank_name', TRUE),
                'bank_branch'         => $this->input->post('bank_branch', TRUE),
                'bank_account_number' => $this->input->post('bank_account_number', TRUE),
                'bank_account_name'   => $this->input->post('bank_account_name', TRUE),
            );
            $this->Payroll_setting_model->upsert($user_id, $data);
            $this->audit('payroll_settings', 'UPDATE', NULL, array('user_id' => $user_id));
            $this->session->set_flashdata('success', 'Đã lưu cấu hình lương cho '.$user['fullname'].'.');
            redirect('payroll/admin');
            return;
        }

        $data = array(
            'page_title'   => 'Cấu hình lương — '.$user['fullname'],
            'current_user' => $this->current_user,
            'target_user'  => $user,
            'settings'     => $this->Payroll_setting_model->get_by_user_or_default($user_id),
        );
        $this->load->view('layout/header', $data);
        $this->load->view('payroll/settings_form', $data);
        $this->load->view('layout/footer');
    }

    /** Ứng lương / trạng thái chi lương của 1 nhân viên trong 1 tháng — ADMIN. Ngày nghỉ/giờ làm sửa ở hours(). */
    public function record($user_id)
    {
        $this->_require_admin();
        $user = $this->User_model->get_by_id($user_id);
        if ( ! $user) show_404();

        $period = $this->input->get('period') ?: date('Y-m');

        if ($this->input->method() === 'post')
        {
            $data = array(
                'advance_amount' => (float) $this->input->post('advance_amount'),
                'paid_status'    => $this->input->post('paid_status') === 'PAID' ? 'PAID' : 'UNPAID',
                'note'           => $this->input->post('note', TRUE),
            );
            $this->Payroll_record_model->upsert($user_id, $period, $data, $this->current_user['id']);
            $this->audit('payroll_record', 'UPDATE', NULL, array('user_id' => $user_id, 'period' => $period));
            $this->session->set_flashdata('success', 'Đã lưu dữ liệu lương '.payroll_period_label($period).' cho '.$user['fullname'].'.');
            redirect('payroll/admin?period='.$period);
            return;
        }

        $settings = $this->Payroll_setting_model->get_by_user_or_default($user_id);
        $record = $this->Payroll_record_model->get_by_user_period_or_default($user_id, $period);

        $data = array(
            'page_title'   => 'Dữ liệu lương — '.$user['fullname'],
            'current_user' => $this->current_user,
            'target_user'  => $user,
            'settings'     => $settings,
            'record'       => $record,
            'salary'       => $this->_compute($settings, $record, $user_id, $period),
            'period'       => $period,
        );
        $this->load->view('layout/header', $data);
        $this->load->view('payroll/record_form', $data);
        $this->load->view('layout/footer');
    }

    /**
     * Nhập theo từng ngày trong tháng — lương theo giờ thì nhập số giờ làm,
     * lương cố định thì đánh dấu ngày nghỉ (số ngày nghỉ = số ngày đánh dấu,
     * dùng để trừ lương). Chỉ ADMIN, giới hạn trong khoảng ngày làm việc.
     */
    public function hours($user_id)
    {
        $this->_require_admin();
        $user = $this->User_model->get_by_id($user_id);
        if ( ! $user) show_404();

        $settings = $this->Payroll_setting_model->get_by_user_or_default($user_id);
        $is_hourly = $settings['salary_type'] === 'HOURLY';

        $period = $this->input->get('period') ?: date('Y-m');
        $days_in_month = (int) date('t', strtotime($period.'-01'));

        if ($this->input->method() === 'post')
        {
            if ($is_hourly)
            {
                $entries = array();
                for ($d = 1; $d <= $days_in_month; $d++)
                {
                    $date = $period.'-'.str_pad($d, 2, '0', STR_PAD_LEFT);
                    if ($this->_within_employment($date, $user))
                    {
                        $entries[$date] = $this->input->post('hours_'.$d);
                    }
                }
                $this->Payroll_hours_model->save_batch($user_id, $entries, $this->current_user['id']);
            }
            else
            {
                $entries = array();
                for ($d = 1; $d <= $days_in_month; $d++)
                {
                    $date = $period.'-'.str_pad($d, 2, '0', STR_PAD_LEFT);
                    if ($this->_within_employment($date, $user))
                    {
                        // 'off_<d>' là fraction dạng chuỗi: '' (đi làm), '1' (nghỉ cả ngày), '0.5' (nghỉ nửa ngày).
                        $entries[$date] = (float) $this->input->post('off_'.$d);
                    }
                }
                $this->Payroll_absence_model->save_batch($user_id, $entries, $this->current_user['id']);
            }

            $this->audit('payroll_hours', 'UPDATE', NULL, array('user_id' => $user_id, 'period' => $period));
            $this->session->set_flashdata('success', 'Đã lưu dữ liệu '.payroll_period_label($period).' cho '.$user['fullname'].'.');
            redirect('payroll/hours/'.$user_id.'?period='.$period);
            return;
        }

        $weekday_labels = array(1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7', 7 => 'CN');
        $existing_hours = $is_hourly ? $this->Payroll_hours_model->get_by_user_period($user_id, $period) : array();
        $existing_off = ! $is_hourly ? $this->Payroll_absence_model->get_by_user_period($user_id, $period) : array();

        $days = array();
        for ($d = 1; $d <= $days_in_month; $d++)
        {
            $date = $period.'-'.str_pad($d, 2, '0', STR_PAD_LEFT);
            $days[] = array(
                'day'           => $d,
                'date'          => $date,
                'hours'         => isset($existing_hours[$date]) ? $existing_hours[$date] : '',
                'off_value'     => isset($existing_off[$date]) ? $existing_off[$date] : 0,
                'weekday_label' => $weekday_labels[(int) date('N', strtotime($date))],
                'is_weekend'    => in_array((int) date('N', strtotime($date)), array(6, 7), TRUE),
                'in_range'      => $this->_within_employment($date, $user),
            );
        }

        $data = array(
            'page_title'    => ($is_hourly ? 'Nhập giờ làm — ' : 'Ngày nghỉ — ').$user['fullname'],
            'current_user'  => $this->current_user,
            'target_user'   => $user,
            'is_hourly'     => $is_hourly,
            'period'        => $period,
            'days'          => $days,
            'total_hours'   => $is_hourly ? $this->Payroll_hours_model->sum_hours($user_id, $period) : 0,
            'absence_days'  => ! $is_hourly ? array_sum($existing_off) : 0,
            'has_range_day' => count(array_filter($days, function($d) { return $d['in_range']; })) > 0,
        );
        $this->load->view('layout/header', $data);
        $this->load->view('payroll/hours_form', $data);
        $this->load->view('layout/footer');
    }
}
