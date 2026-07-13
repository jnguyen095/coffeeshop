<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    protected $allowed_roles = array('STAFF', 'CASHIER', 'ADMIN');

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Table_model', 'Order_model', 'Kitchen_ticket_model', 'Payment_model'));
    }

    public function index()
    {
        $tables = $this->Table_model->get_all();
        $status_counts = array('AVAILABLE' => 0, 'OPEN' => 0, 'WAIT_PAYMENT' => 0, 'PAID' => 0);
        foreach ($tables as $t)
        {
            $status_counts[$t['status']] = (isset($status_counts[$t['status']]) ? $status_counts[$t['status']] : 0) + 1;
        }

        $today = date('Y-m-d');
        $data = array(
            'page_title'      => 'Tổng quan',
            'current_user'    => $this->current_user,
            'tables_total'    => count($tables),
            'status_counts'   => $status_counts,
            'today_revenue'   => (float) $this->Order_model->daily_revenue($today),
            'active_tickets'  => count($this->Kitchen_ticket_model->get_dashboard_tickets(array('NEW', 'PREPARING'))),
            'wait_payment'    => count($this->Order_model->get_list(array('status' => 'WAIT_PAYMENT'))),
        );

        $this->load->view('layout/header', $data);
        $this->load->view('dashboard/index', $data);
        $this->load->view('layout/footer');
    }
}
