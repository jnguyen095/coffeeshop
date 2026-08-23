<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Admin cấu hình khung giờ + giá tiền sân (dùng chung cho mọi sân) — xem [[051_create_court_time_slots_table]]. */
class Court_time_slots extends MY_Controller
{
    protected $allowed_roles = array('ADMIN');

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Court_time_slot_model');
    }

    public function index()
    {
        $data = array(
            'page_title'   => 'Khung giờ & giá sân',
            'current_user' => $this->current_user,
            'slots'        => $this->Court_time_slot_model->get_all(),
        );
        $this->load->view('layout/header', $data);
        $this->load->view('court_time_slots/index', $data);
        $this->load->view('layout/footer');
    }

    public function create()
    {
        $error = NULL;

        if ($this->input->method() === 'post')
        {
            list($ok, $error, $data) = $this->_read_and_validate();
            if ($ok)
            {
                $id = $this->Court_time_slot_model->create($data);
                $this->audit('court_time_slot', 'CREATE', NULL, array('id' => $id));
                redirect('court-time-slots');
                return;
            }
        }

        $data = array('page_title' => 'Thêm khung giờ', 'current_user' => $this->current_user, 'slot' => NULL, 'error' => $error);
        $this->load->view('layout/header', $data);
        $this->load->view('court_time_slots/form', $data);
        $this->load->view('layout/footer');
    }

    public function edit($id)
    {
        $slot = $this->Court_time_slot_model->get_by_id($id);
        if ( ! $slot) show_404();
        $error = NULL;

        if ($this->input->method() === 'post')
        {
            list($ok, $error, $data) = $this->_read_and_validate($id);
            if ($ok)
            {
                $this->Court_time_slot_model->update($id, $data);
                $this->audit('court_time_slot', 'UPDATE', $slot, array('id' => $id));
                redirect('court-time-slots');
                return;
            }
            $slot = array_merge($slot, $data);
        }

        $data = array('page_title' => 'Sửa khung giờ', 'current_user' => $this->current_user, 'slot' => $slot, 'error' => $error);
        $this->load->view('layout/header', $data);
        $this->load->view('court_time_slots/form', $data);
        $this->load->view('layout/footer');
    }

    public function delete($id)
    {
        $slot = $this->Court_time_slot_model->get_by_id($id);
        $this->Court_time_slot_model->delete($id);
        $this->audit('court_time_slot', 'DELETE', $slot, NULL);
        redirect('court-time-slots');
    }

    /** @return array [bool $ok, string|NULL $error, array $data] */
    private function _read_and_validate($exclude_id = NULL)
    {
        $label = $this->input->post('label', TRUE);
        $start_time = $this->input->post('start_time').':00';
        $end_time = $this->input->post('end_time').':00';
        $price_per_hour = $this->input->post('price_per_hour');
        $sort_order = (int) $this->input->post('sort_order');
        $status = $this->input->post('status') === 'INACTIVE' ? 'INACTIVE' : 'ACTIVE';

        $data = array(
            'label'          => $label,
            'start_time'     => $start_time,
            'end_time'       => $end_time,
            'price_per_hour' => $price_per_hour !== '' ? (float) $price_per_hour : 0,
            'sort_order'     => $sort_order,
            'status'         => $status,
        );

        if ( ! $label || $end_time <= $start_time)
        {
            return array(FALSE, 'Vui lòng nhập đầy đủ thông tin hợp lệ (giờ kết thúc phải sau giờ bắt đầu).', $data);
        }

        if ($this->Court_time_slot_model->has_overlap($start_time, $end_time, $exclude_id))
        {
            return array(FALSE, 'Khung giờ này bị trùng với 1 khung giờ khác đã có. Mỗi thời điểm chỉ được thuộc 1 khung giá.', $data);
        }

        return array(TRUE, NULL, $data);
    }
}
