<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends MY_Controller
{
    protected $allowed_roles = array('STAFF', 'CASHIER', 'ADMIN');

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Order_model', 'Order_item_model', 'Product_model', 'Category_model', 'Kitchen_ticket_model', 'Table_model', 'Court_booking_model'));
    }

    public function index()
    {
        $status = $this->input->get('status');
        $orders = $this->Order_model->get_list($status ? array('status' => $status) : array());

        $data = array(
            'page_title'   => 'Đơn hàng',
            'current_user' => $this->current_user,
            'orders'       => $orders,
            'status'       => $status,
        );
        $this->load->view('layout/header', $data);
        $this->load->view('orders/index', $data);
        $this->load->view('layout/footer');
    }

    public function detail($id)
    {
        $order = $this->Order_model->get_detail($id);
        if ( ! $order)
        {
            show_404();
        }

        $items = $this->Order_item_model->get_by_order($id);
        $products_by_category = $this->Product_model->get_active_grouped_by_category($order['table_type']);
        $tickets = $this->Kitchen_ticket_model->tickets_with_items_for_order($id);

        $data = array(
            'page_title'            => 'Đơn hàng '.$order['order_no'],
            'current_user'          => $this->current_user,
            'order'                 => $order,
            'items'                 => $items,
            'products_by_category'  => $products_by_category,
            'tickets'               => $tickets,
        );
        $this->load->view('layout/header', $data);
        $this->load->view('orders/detail', $data);
        $this->load->view('layout/footer');
    }

    /** JSON poll dùng để cập nhật trạng thái pha chế theo thời gian thực trên trang chi tiết đơn. */
    public function ticket_status($id)
    {
        $tickets = $this->Kitchen_ticket_model->tickets_with_items_for_order($id);
        json_response(array('success' => TRUE, 'tickets' => $tickets));
    }

    public function add_item($id)
    {
        $order = $this->Order_model->get_detail($id);
        if ( ! $order || $order['status'] !== 'OPEN')
        {
            redirect('orders/'.$id);
            return;
        }

        $product_ids = $this->input->post('product_id');
        $qtys = $this->input->post('qty');
        $notes = $this->input->post('note');

        if (empty($product_ids))
        {
            redirect('orders/'.$id);
            return;
        }

        $added_items = array();
        $ticket_items = array();
        foreach ($product_ids as $i => $pid)
        {
            $qty = max(1, (int) $qtys[$i]);
            $product = $this->Product_model->get_by_id($pid);
            if ( ! $product || $product['status'] !== 'ACTIVE') continue;

            $this->Order_item_model->add($id, $pid, $qty, $product['price'], $notes[$i] ?: NULL);
            $item = array('product_id' => $pid, 'qty' => $qty, 'note' => $notes[$i] ?: NULL);
            $added_items[] = $item;

            // Dịch vụ sân (thuê vợt, thuê trang phục, nhặt bóng...) không cần pha chế nên không tạo phiếu bếp.
            if ( ! $product['court_only'])
            {
                $ticket_items[] = $item;
            }
        }

        if ($added_items)
        {
            if ($ticket_items)
            {
                $this->Kitchen_ticket_model->create_ticket($id, $order['table_id'], $ticket_items);
            }
            $this->Order_model->recalc_totals($id);
            $this->audit('order', 'ADD_ITEM', NULL, array('order_id' => $id, 'items' => $added_items));
        }

        redirect('orders/'.$id);
    }

    /**
     * Thêm "Tiền sân" trực tiếp vào order bằng cách nhập giờ chơi thực tế —
     * dùng cho khách vãng lai/linh hoạt giờ, không qua lịch đặt trước và
     * không cần tên/SĐT khách. Có thể gọi nhiều lần nếu khách chơi thêm giờ.
     */
    public function add_timeslot($id)
    {
        $order = $this->Order_model->get_detail($id);
        if ( ! $order || $order['status'] !== 'OPEN' || $order['table_type'] !== 'COURT')
        {
            redirect('orders/'.$id);
            return;
        }

        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');

        if ( ! $start_time || ! $end_time || $end_time <= $start_time)
        {
            $this->session->set_flashdata('error', 'Giờ kết thúc phải sau giờ bắt đầu.');
            redirect('orders/'.$id);
            return;
        }

        $amount = $this->Court_booking_model->calc_fee($order, $start_time.':00', $end_time.':00');

        if ($amount > 0)
        {
            $court_fee_product = $this->Product_model->get_by_sku('COURT_FEE');
            if ($court_fee_product)
            {
                $note = 'Sân '.$start_time.'-'.$end_time;
                $this->Order_item_model->add($id, $court_fee_product['id'], 1, $amount, $note);
                $this->Order_model->recalc_totals($id);
                $this->audit('order', 'ADD_TIMESLOT', NULL, array('order_id' => $id, 'start_time' => $start_time, 'end_time' => $end_time, 'amount' => $amount));
            }
        }

        redirect('orders/'.$id);
    }

    public function update_item($order_id, $item_id)
    {
        $qty = max(1, (int) $this->input->post('qty'));
        $this->Order_item_model->update_qty($item_id, $qty);
        $this->Order_model->recalc_totals($order_id);
        $this->audit('order_item', 'UPDATE_QTY', NULL, array('item_id' => $item_id, 'qty' => $qty));
        redirect('orders/'.$order_id);
    }

    public function cancel_item($order_id, $item_id)
    {
        $this->Order_item_model->cancel($item_id);
        $this->Order_model->recalc_totals($order_id);
        $this->audit('order_item', 'CANCEL_ITEM', NULL, array('item_id' => $item_id));
        redirect('orders/'.$order_id);
    }

    /**
     * "Thanh toán" trên đơn mang đi: không có bàn để đóng bill qua Sơ đồ bàn,
     * nên chốt đơn (OPEN -> WAIT_PAYMENT) ngay tại đây rồi chuyển sang thu ngân.
     */
    public function checkout($order_id)
    {
        $order = $this->Order_model->get_by_id($order_id);
        if ($order && $order['status'] === 'OPEN')
        {
            $this->Order_model->mark_wait_payment($order_id);
            $this->audit('order', 'CHECKOUT', NULL, array('order_id' => $order_id));
        }
        redirect('cashier/'.$order_id);
    }
}
