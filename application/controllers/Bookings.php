<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Lịch đặt sân pickleball (gọi điện đặt trước). Một sân chỉ là một cafe_tables
 * với table_type=COURT — check-in một lịch đặt dùng lại đúng
 * Order_model::open_table_with_order() như mở bàn cafe bình thường, rồi cộng
 * thêm dòng "Tiền sân" vào order dựa trên khung giờ đặt (sáng/chiều/tối) x giá của sân.
 */
class Bookings extends MY_Controller
{
    protected $allowed_roles = array('STAFF', 'CASHIER', 'ADMIN', 'BOOKING');

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Court_booking_model', 'Court_booking_note_model', 'Court_time_slot_model', 'Table_model', 'Order_model', 'Order_item_model', 'Product_model', 'Setting_model'));
    }

    public function index()
    {
        $view = $this->input->get('view') ?: 'day';
        $date = $this->input->get('date') ?: date('Y-m-d');
        $search = trim((string) $this->input->get('search'));

        if ($view === 'week') { $this->_week_view($date, $search); return; }
        if ($view === 'month') { $this->_month_view($date, $search); return; }
        $this->_day_view($date, $search);
    }

    private function _enrich_with_order_id(&$bookings)
    {
        foreach ($bookings as &$b)
        {
            $b['order_id'] = NULL;
            if ($b['status'] === 'CHECKED_IN' && $b['table_session_id'])
            {
                $order = $this->Order_model->get_active_by_table_session($b['table_session_id']);
                if ($order) $b['order_id'] = $order['id'];
            }
        }
    }

    /** Giờ đặt sân (int, làm tròn ra ngoài) theo cấu hình ở /settings — vd '06:30' -> start 6, '21:30' -> end 22. */
    private function _booking_hour_bounds()
    {
        list($sh, $sm) = explode(':', $this->Setting_model->get_booking_start_time());
        list($eh, $em) = explode(':', $this->Setting_model->get_booking_end_time());
        return array((int) $sh, ((int) $eh) + ($em > 0 ? 1 : 0));
    }

    private function _day_view($date, $search = '')
    {
        $bookings = $this->Court_booking_model->get_by_date($date, NULL, $search);
        $this->_enrich_with_order_id($bookings);

        list($day_start_hour, $day_end_hour) = $this->_booking_hour_bounds();

        $data = array(
            'page_title'      => 'Lịch đặt sân',
            'current_user'    => $this->current_user,
            'view'            => 'day',
            'date'            => $date,
            'search'          => $search,
            'courts'          => $this->Table_model->get_courts(),
            'bookings'        => $bookings,
            'day_start_hour'  => $day_start_hour,
            'day_end_hour'    => $day_end_hour,
            'slots'           => \Court_booking_model::SLOTS,
        );
        $this->load->view('layout/header', $data);
        $this->load->view('bookings/day', $data);
        $this->load->view('layout/footer');
    }

    private function _week_view($date, $search = '')
    {
        $day_of_week = (int) date('N', strtotime($date)); // 1=Mon
        $week_start = date('Y-m-d', strtotime($date.' -'.($day_of_week - 1).' days'));
        $week_end = date('Y-m-d', strtotime($week_start.' +6 days'));

        $bookings = $this->Court_booking_model->get_by_range($week_start, $week_end, $search);
        $this->_enrich_with_order_id($bookings);

        $data = array(
            'page_title'   => 'Lịch đặt sân',
            'current_user' => $this->current_user,
            'view'         => 'week',
            'date'         => $date,
            'search'       => $search,
            'week_start'   => $week_start,
            'week_end'     => $week_end,
            'courts'       => $this->Table_model->get_courts(),
            'bookings'     => $bookings,
        );
        $this->load->view('layout/header', $data);
        $this->load->view('bookings/week', $data);
        $this->load->view('layout/footer');
    }

    private function _month_view($date, $search = '')
    {
        $month_start = date('Y-m-01', strtotime($date));
        $month_end = date('Y-m-t', strtotime($date));

        $bookings = $this->Court_booking_model->get_by_range($month_start, $month_end, $search);
        $this->_enrich_with_order_id($bookings);

        $by_day = array();
        foreach ($bookings as $b)
        {
            $by_day[$b['booking_date']][] = $b;
        }

        $data = array(
            'page_title'   => 'Lịch đặt sân',
            'current_user' => $this->current_user,
            'view'         => 'month',
            'date'         => $date,
            'search'       => $search,
            'month_start'  => $month_start,
            'month_end'    => $month_end,
            'by_day'       => $by_day,
            'courts'       => $this->Table_model->get_courts(),
        );
        $this->load->view('layout/header', $data);
        $this->load->view('bookings/month', $data);
        $this->load->view('layout/footer');
    }

    public function create()
    {
        $error = NULL;
        $result = NULL;
        $courts = $this->Table_model->get_courts();

        if ($this->input->method() === 'post')
        {
            $auto_assign = (bool) $this->input->post('auto_assign');
            $table_id = (int) $this->input->post('table_id');
            $customer_name = $this->input->post('customer_name', TRUE);
            $customer_phone = $this->input->post('customer_phone', TRUE);
            $notes = $this->input->post('notes', TRUE) ?: NULL;
            $start_time = $this->input->post('start_time').':00';
            $end_time = $this->input->post('end_time').':00';
            $repeat = $this->input->post('repeat'); // none|weekly|monthly
            $date_from = $this->input->post('date_from');
            $date_to = $this->input->post('date_to') ?: $date_from;
            $weekdays = $this->input->post('weekdays') ?: array();

            $booking_start_time = $this->Setting_model->get_booking_start_time().':00';
            $booking_end_time = $this->Setting_model->get_booking_end_time().':00';

            if ( ! $customer_name || ! $date_from || $end_time <= $start_time || ( ! $auto_assign && ! $table_id))
            {
                $error = 'Vui lòng nhập đầy đủ thông tin hợp lệ.';
            }
            elseif ($start_time < $booking_start_time || $end_time > $booking_end_time)
            {
                $error = 'Chỉ nhận đặt sân trong khung giờ '.substr($booking_start_time, 0, 5).' - '.substr($booking_end_time, 0, 5).'.';
            }
            elseif (empty($courts))
            {
                $error = 'Chưa có sân nào được cấu hình.';
            }
            elseif ($repeat === 'none')
            {
                if ($auto_assign)
                {
                    $table_id = $this->Court_booking_model->find_available_table($courts, $date_from, $start_time, $end_time);
                }

                if ($auto_assign && ! $table_id)
                {
                    $error = 'Không có sân nào trống trong khung giờ đã chọn.';
                }
                elseif ( ! $auto_assign && $this->Court_booking_model->has_conflict($table_id, $date_from, $start_time, $end_time))
                {
                    $error = 'Khung giờ này đã có lịch đặt khác cho sân đã chọn.';
                }
                else
                {
                    $booking_id = $this->Court_booking_model->create_booking(array(
                        'table_id'       => $table_id,
                        'customer_name'  => $customer_name,
                        'customer_phone' => $customer_phone,
                        'booking_date'   => $date_from,
                        'start_time'     => $start_time,
                        'end_time'       => $end_time,
                        'notes'          => $notes,
                        'created_by'     => $this->current_user['id'],
                    ));
                    $this->audit('court_booking', 'CREATE', NULL, array('booking_id' => $booking_id, 'auto_assign' => $auto_assign));
                    redirect('bookings?date='.$date_from);
                    return;
                }
            }
            else
            {
                $weekdays = array_map('intval', $weekdays);
                if (empty($weekdays))
                {
                    $error = 'Vui lòng chọn ít nhất 1 thứ trong tuần để lặp lại.';
                }
                else
                {
                    if ($auto_assign)
                    {
                        $dates = $this->Court_booking_model->occurrence_dates($weekdays, $date_from, $date_to);
                        $table_id = $this->Court_booking_model->find_best_table_for_dates($courts, $dates, $start_time, $end_time);
                    }

                    $result = $this->Court_booking_model->create_recurring(
                        $table_id, $customer_name, $customer_phone, $notes,
                        $weekdays, $start_time, $end_time, $date_from, $date_to,
                        $this->current_user['id']
                    );
                    $this->audit('court_booking', 'CREATE_RECURRING', NULL, array('group_id' => $result['group_id'], 'created' => count($result['created']), 'skipped' => count($result['skipped']), 'auto_assign' => $auto_assign));
                }
            }
        }

        $data = array(
            'page_title'          => 'Đặt lịch sân',
            'current_user'        => $this->current_user,
            'courts'              => $courts,
            'court_time_slots'    => $this->Court_time_slot_model->get_active(),
            'error'               => $error,
            'result'              => $result,
            'prefill_table'       => $this->input->get('table_id'),
            'prefill_date'        => $this->input->get('date_from') ?: date('Y-m-d'),
            'prefill_start'       => $this->input->get('start_time'),
            'booking_start_time'  => $this->Setting_model->get_booking_start_time(),
            'booking_end_time'    => $this->Setting_model->get_booking_end_time(),
        );
        $this->load->view('layout/header', $data);
        $this->load->view('bookings/create', $data);
        $this->load->view('layout/footer');
    }

    public function cancel($id)
    {
        $booking = $this->Court_booking_model->get_by_id($id);
        $this->Court_booking_model->cancel($id);
        $this->audit('court_booking', 'CANCEL', $booking, NULL);
        redirect('bookings?date='.($booking['booking_date'] ?? date('Y-m-d')));
    }

    public function cancel_group($group_id)
    {
        $this->Court_booking_model->cancel_group($group_id);
        $this->audit('court_booking', 'CANCEL_GROUP', NULL, array('group_id' => $group_id));
        redirect('bookings');
    }

    /**
     * Sửa 1 buổi đặt — nếu buổi này thuộc 1 chuỗi lặp lại, người dùng chọn
     * áp dụng cho riêng buổi này hay cả chuỗi (giữ nguyên ngày riêng từng
     * buổi, chỉ đổi giờ/sân/thông tin khách).
     */
    public function edit($id)
    {
        $booking = $this->Court_booking_model->get_by_id($id);
        if ( ! $booking || $booking['status'] !== 'BOOKED') show_404();

        $courts = $this->Table_model->get_courts();
        $group_bookings = $booking['booking_group_id'] ? $this->Court_booking_model->get_by_group($booking['booking_group_id']) : array();
        $error = NULL;

        if ($this->input->method() === 'post')
        {
            $scope = $this->input->post('scope') === 'group' && $booking['booking_group_id'] ? 'group' : 'single';
            $table_id = (int) $this->input->post('table_id');
            $customer_name = $this->input->post('customer_name', TRUE);
            $customer_phone = $this->input->post('customer_phone', TRUE);
            $notes = $this->input->post('notes', TRUE) ?: NULL;
            $start_time = $this->input->post('start_time').':00';
            $end_time = $this->input->post('end_time').':00';
            $is_paid = $this->input->post('is_paid') === 'YES' ? 'YES' : 'NO';
            $payment_order_no = $is_paid === 'YES' ? ($this->input->post('payment_order_no', TRUE) ?: NULL) : NULL;
            $payment_amount = $is_paid === 'YES' ? $this->input->post('payment_amount') : NULL;

            $booking_start_time = $this->Setting_model->get_booking_start_time().':00';
            $booking_end_time = $this->Setting_model->get_booking_end_time().':00';

            if ( ! $customer_name || ! $table_id || $end_time <= $start_time)
            {
                $error = 'Vui lòng nhập đầy đủ thông tin hợp lệ.';
            }
            elseif ($start_time < $booking_start_time || $end_time > $booking_end_time)
            {
                $error = 'Chỉ nhận đặt sân trong khung giờ '.substr($booking_start_time, 0, 5).' - '.substr($booking_end_time, 0, 5).'.';
            }
            elseif ($is_paid === 'YES' && ! ($payment_amount > 0))
            {
                $error = 'Vui lòng nhập số tiền đã thanh toán.';
            }
            else
            {
                $this->Court_booking_model->update_payment($id, array(
                    'is_paid'           => $is_paid,
                    'payment_order_no'  => $payment_order_no,
                    'payment_amount'    => $is_paid === 'YES' ? $payment_amount : NULL,
                ));

                $data = array(
                    'table_id'       => $table_id,
                    'customer_name'  => $customer_name,
                    'customer_phone' => $customer_phone,
                    'notes'          => $notes,
                    'start_time'     => $start_time,
                    'end_time'       => $end_time,
                );

                if ($scope === 'group')
                {
                    if ($this->Court_booking_model->has_conflict($table_id, $booking['booking_date'], $start_time, $end_time, $id))
                    {
                        $error = 'Khung giờ này đã có lịch đặt khác cho sân đã chọn vào ngày '.date('d/m/Y', strtotime($booking['booking_date'])).'.';
                    }
                    else
                    {
                        $result = $this->Court_booking_model->update_group($booking['booking_group_id'], $data);
                        $this->audit('court_booking', 'UPDATE_GROUP', $booking, array('group_id' => $booking['booking_group_id'], 'updated' => count($result['updated']), 'skipped' => count($result['skipped'])));
                        $msg = 'Đã cập nhật '.count($result['updated']).' buổi trong chuỗi.';
                        if ($result['skipped']) $msg .= ' Bỏ qua '.count($result['skipped']).' buổi bị trùng lịch với giờ/sân mới.';
                        $this->session->set_flashdata('success', $msg);
                        redirect('bookings?date='.$booking['booking_date']);
                        return;
                    }
                }
                else
                {
                    if ($this->Court_booking_model->has_conflict($table_id, $booking['booking_date'], $start_time, $end_time, $id))
                    {
                        $error = 'Khung giờ này đã có lịch đặt khác cho sân đã chọn.';
                    }
                    else
                    {
                        $this->Court_booking_model->update($id, $data);
                        $this->audit('court_booking', 'UPDATE', $booking, $data);
                        $this->session->set_flashdata('success', 'Đã cập nhật lịch đặt.');
                        redirect('bookings?date='.$booking['booking_date']);
                        return;
                    }
                }
            }
        }

        // Đọc lại booking mới nhất (có thể vừa cập nhật payment ở trên) để form hiện đúng giá trị hiện tại.
        $booking = $this->Court_booking_model->get_by_id($id);

        $data = array(
            'page_title'         => 'Sửa lịch đặt sân',
            'current_user'       => $this->current_user,
            'booking'             => $booking,
            'courts'              => $courts,
            'group_count'         => count($group_bookings),
            'notes_log'           => $this->Court_booking_note_model->get_for_booking($booking),
            'error'               => $error,
            'booking_start_time'  => $this->Setting_model->get_booking_start_time(),
            'booking_end_time'    => $this->Setting_model->get_booking_end_time(),
        );
        $this->load->view('layout/header', $data);
        $this->load->view('bookings/edit', $data);
        $this->load->view('layout/footer');
    }

    /** Thêm 1 ghi chú vào nhật ký — nếu buổi này thuộc chuỗi lặp lại, ghi chú hiện ra ở mọi buổi trong chuỗi. */
    public function add_note($id)
    {
        $booking = $this->Court_booking_model->get_by_id($id);
        if ( ! $booking) show_404();

        $note = trim((string) $this->input->post('note', TRUE));
        if ($note !== '')
        {
            $this->Court_booking_note_model->add($booking, $note, $this->current_user['id']);
            $this->audit('court_booking', 'ADD_NOTE', NULL, array('booking_id' => $id, 'group_id' => $booking['booking_group_id']));
        }

        redirect('bookings/'.$id.'/edit');
    }

    public function checkin($id)
    {
        if ($this->current_user['role'] === 'BOOKING')
        {
            // Check-in mở bàn + tạo đơn hàng (module Orders) — ngoài phạm vi của vai trò lễ tân đặt sân.
            $this->session->set_flashdata('error', 'Vai trò của bạn không có quyền check-in. Vui lòng nhờ nhân viên/thu ngân xử lý.');
            redirect('bookings?date='.date('Y-m-d'));
            return;
        }

        $booking = $this->Court_booking_model->get_by_id($id);
        if ( ! $booking || $booking['status'] !== 'BOOKED')
        {
            redirect('bookings?date='.date('Y-m-d'));
            return;
        }

        $table = $this->Table_model->get_by_id($booking['table_id']);
        if ( ! $table || $table['status'] !== 'AVAILABLE')
        {
            $this->session->set_flashdata('error', 'Sân hiện chưa trống (có thể lượt trước chưa đóng bill). Vui lòng xử lý bàn trước khi check-in.');
            redirect('bookings?date='.$booking['booking_date']);
            return;
        }

        $result = $this->Order_model->open_table_with_order($table['id'], $this->current_user['id']);
        $order_id = $result['order_id'];

        $amount = $this->Court_booking_model->calc_fee($booking['start_time'], $booking['end_time']);
        if ($amount > 0)
        {
            $court_fee_product = $this->Product_model->get_by_sku('COURT_FEE');
            if ($court_fee_product)
            {
                $note = 'Sân '.substr($booking['start_time'], 0, 5).'-'.substr($booking['end_time'], 0, 5);
                $this->Order_item_model->add($order_id, $court_fee_product['id'], 1, $amount, $note);
                $this->Order_model->recalc_totals($order_id);
            }
        }

        $this->Court_booking_model->mark_checked_in($id, $result['session_id']);
        $this->audit('court_booking', 'CHECK_IN', NULL, array('booking_id' => $id, 'order_id' => $order_id));

        redirect('orders/'.$order_id);
    }
}
