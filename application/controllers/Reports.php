<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller
{
    protected $allowed_roles = array('ADMIN');

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Order_model', 'Product_model', 'Table_session_model', 'Kitchen_ticket_model', 'Payment_model', 'Court_booking_model', 'Table_model'));
    }

    public function index()
    {
        $data = array('page_title' => 'Báo cáo', 'current_user' => $this->current_user);
        $this->load->view('layout/header', $data);
        $this->load->view('reports/index', $data);
        $this->load->view('layout/footer');
    }

    public function daily_revenue()
    {
        $date = $this->input->get('date') ?: date('Y-m-d');
        $split = $this->Order_model->revenue_split($date, $date);

        $data = array(
            'page_title'    => 'Doanh thu theo ngày',
            'current_user'  => $this->current_user,
            'date'          => $date,
            'revenue'       => (float) $this->Order_model->daily_revenue($date),
            'drink_revenue' => $split['drink_revenue'],
            'court_revenue' => $split['court_revenue'],
        );
        $this->load->view('layout/header', $data);
        $this->load->view('reports/daily_revenue', $data);
        $this->load->view('layout/footer');
    }

    public function monthly_revenue()
    {
        $month = $this->input->get('month') ?: date('Y-m');
        $from = $month.'-01';
        $to = date('Y-m-t', strtotime($from));

        $rows = $this->Order_model->revenue_by_day($from, $to);
        $split_by_day = $this->Order_model->revenue_split_by_day($from, $to);
        foreach ($rows as &$r)
        {
            $r['drink_revenue'] = isset($split_by_day[$r['day']]) ? $split_by_day[$r['day']]['drink_revenue'] : 0;
            $r['court_revenue'] = isset($split_by_day[$r['day']]) ? $split_by_day[$r['day']]['court_revenue'] : 0;
        }
        unset($r);

        $data = array(
            'page_title'   => 'Doanh thu theo tháng',
            'current_user' => $this->current_user,
            'month'        => $month,
            'rows'         => $rows,
        );
        $this->load->view('layout/header', $data);
        $this->load->view('reports/monthly_revenue', $data);
        $this->load->view('layout/footer');
    }

    public function top_products()
    {
        $from = $this->input->get('from') ?: date('Y-m-01');
        $to = $this->input->get('to') ?: date('Y-m-d');

        $data = array(
            'page_title'   => 'Sản phẩm bán chạy',
            'current_user' => $this->current_user,
            'from'         => $from,
            'to'           => $to,
            'rows'         => $this->Product_model->top_products($from.' 00:00:00', $to.' 23:59:59', 15),
        );
        $this->load->view('layout/header', $data);
        $this->load->view('reports/top_products', $data);
        $this->load->view('layout/footer');
    }

    public function table_usage()
    {
        $from = $this->input->get('from') ?: date('Y-m-01');
        $to = $this->input->get('to') ?: date('Y-m-d');

        $data = array(
            'page_title'   => 'Sử dụng bàn',
            'current_user' => $this->current_user,
            'from'         => $from,
            'to'           => $to,
            'rows'         => $this->Table_session_model->usage_by_table($from, $to),
        );
        $this->load->view('layout/header', $data);
        $this->load->view('reports/table_usage', $data);
        $this->load->view('layout/footer');
    }

    public function kitchen_performance()
    {
        $from = $this->input->get('from') ?: date('Y-m-01');
        $to = $this->input->get('to') ?: date('Y-m-d');

        $data = array(
            'page_title'   => 'Hiệu suất bếp',
            'current_user' => $this->current_user,
            'from'         => $from,
            'to'           => $to,
            'rows'         => $this->Kitchen_ticket_model->performance_by_day($from, $to),
        );
        $this->load->view('layout/header', $data);
        $this->load->view('reports/kitchen_performance', $data);
        $this->load->view('layout/footer');
    }

    public function court_performance()
    {
        $from = $this->input->get('from') ?: date('Y-m-01');
        $to = $this->input->get('to') ?: date('Y-m-d');

        $data = array(
            'page_title'          => 'Hiệu suất sân pickleball',
            'current_user'        => $this->current_user,
            'from'                => $from,
            'to'                  => $to,
            'has_courts'          => (bool) $this->Table_model->get_courts(),
            'revenue_by_court'    => $this->Court_booking_model->revenue_by_court($from, $to),
            'revenue_trend'       => $this->Court_booking_model->revenue_trend($from, $to),
            'bookings_by_status'  => $this->Court_booking_model->bookings_by_status($from, $to),
            'usage_by_slot'       => $this->Court_booking_model->usage_by_slot($from, $to),
            'utilization'         => $this->Court_booking_model->utilization_by_court($from, $to),
        );
        $this->load->view('layout/header', $data);
        $this->load->view('reports/court_performance', $data);
        $this->load->view('layout/footer');
    }

    public function payment_summary()
    {
        $from = $this->input->get('from') ?: date('Y-m-01');
        $to = $this->input->get('to') ?: date('Y-m-d');

        $data = array(
            'page_title'   => 'Tổng hợp thanh toán',
            'current_user' => $this->current_user,
            'from'         => $from,
            'to'           => $to,
            'rows'         => $this->Payment_model->summary_by_method($from, $to),
        );
        $this->load->view('layout/header', $data);
        $this->load->view('reports/payment_summary', $data);
        $this->load->view('layout/footer');
    }
}
