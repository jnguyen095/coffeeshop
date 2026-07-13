<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends MY_Controller
{
    protected $allowed_roles = array('STAFF', 'ADMIN');

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Order_model', 'Order_item_model', 'Product_model', 'Category_model', 'Kitchen_ticket_model', 'Table_model'));
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
        $products_by_category = $this->Product_model->get_active_grouped_by_category();

        $data = array(
            'page_title'            => 'Đơn hàng '.$order['order_no'],
            'current_user'          => $this->current_user,
            'order'                 => $order,
            'items'                 => $items,
            'products_by_category'  => $products_by_category,
        );
        $this->load->view('layout/header', $data);
        $this->load->view('orders/detail', $data);
        $this->load->view('layout/footer');
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

        $ticket_items = array();
        foreach ($product_ids as $i => $pid)
        {
            $qty = max(1, (int) $qtys[$i]);
            $product = $this->Product_model->get_by_id($pid);
            if ( ! $product || $product['status'] !== 'ACTIVE') continue;

            $this->Order_item_model->add($id, $pid, $qty, $product['price'], $notes[$i] ?: NULL);
            $ticket_items[] = array('product_id' => $pid, 'qty' => $qty, 'note' => $notes[$i] ?: NULL);
        }

        if ($ticket_items)
        {
            $this->Kitchen_ticket_model->create_ticket($id, $order['table_id'], $ticket_items);
            $this->Order_model->recalc_totals($id);
            $this->audit('order', 'ADD_ITEM', NULL, array('order_id' => $id, 'items' => $ticket_items));
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
}
