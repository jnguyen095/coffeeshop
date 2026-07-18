<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    protected $allowed_roles = array('STAFF', 'CASHIER', 'ADMIN');

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Table_model', 'Order_model', 'Kitchen_ticket_model', 'Payment_model', 'Court_booking_model'));
    }

    public function index()
    {
        $tables = $this->Table_model->get_all();
        $status_counts = array('AVAILABLE' => 0, 'OPEN' => 0, 'WAIT_PAYMENT' => 0, 'PAID' => 0);
        foreach ($tables as $t)
        {
            $status_counts[$t['status']] = (isset($status_counts[$t['status']]) ? $status_counts[$t['status']] : 0) + 1;
        }

        $courts = $this->Table_model->get_courts();
        $courts_occupied = 0;
        foreach ($courts as $c)
        {
            if ($c['status'] !== 'AVAILABLE') $courts_occupied++;
        }

        $today = date('Y-m-d');
        $week_ago = date('Y-m-d', strtotime('-6 days'));
        $today_split = $this->Order_model->revenue_split($today, $today);

        $data = array(
            'page_title'          => 'Tổng quan',
            'current_user'        => $this->current_user,
            'tables_total'        => count($tables),
            'status_counts'       => $status_counts,
            'today_revenue'       => (float) $this->Order_model->daily_revenue($today),
            'drink_revenue_today' => $today_split['drink_revenue'],
            'active_tickets'      => count($this->Kitchen_ticket_model->get_dashboard_tickets(array('NEW', 'PREPARING'))),
            'wait_payment'        => count($this->Order_model->get_list(array('status' => 'WAIT_PAYMENT'))),
            'courts_total'        => count($courts),
            'courts_occupied'     => $courts_occupied,
            'court_revenue_today' => $today_split['court_revenue'],
            'court_revenue_trend' => array(),
        );

        if ($courts)
        {
            $data['court_revenue_trend'] = $this->Court_booking_model->revenue_trend($week_ago, $today);
        }

        $this->load->view('layout/header', $data);
        $this->load->view('dashboard/index', $data);
        $this->load->view('layout/footer');
    }
}
