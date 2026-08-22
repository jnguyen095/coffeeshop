<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Báo cáo doanh thu — nhập tay theo tháng cho 4 danh mục (bán hàng qua POS
 * ngoài, không tự tính từ orders trong app này). Thay thế Reports cũ (đã xóa).
 */
class Revenue_report extends MY_Controller
{
    protected $allowed_roles = array('ADMIN');

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('payroll'); // payroll_period_label() — định dạng "Tháng MM/YYYY" dùng chung
        $this->load->model('Monthly_revenue_model');
    }

    /** Tổng doanh thu theo tháng (xu hướng 12 tháng) + tỷ lệ theo danh mục cho 1 tháng được chọn. */
    public function index()
    {
        $period = $this->input->get('period') ?: date('Y-m');

        $periods = array();
        for ($i = 11; $i >= 0; $i--)
        {
            $periods[] = date('Y-m', strtotime($period.'-01 -'.$i.' months'));
        }
        $trend = $this->Monthly_revenue_model->get_totals_for_periods($periods);

        $by_category = $this->Monthly_revenue_model->get_revenue_by_category($period);
        $total = array_sum($by_category);

        $breakdown = array();
        foreach ($by_category as $category => $revenue)
        {
            $breakdown[] = array(
                'category' => $category,
                'revenue'  => $revenue,
                'percent'  => $total > 0 ? ($revenue / $total) * 100 : 0,
            );
        }

        $data = array(
            'page_title'   => 'Báo cáo doanh thu',
            'current_user' => $this->current_user,
            'period'       => $period,
            'periods'      => $periods,
            'trend'        => $trend,
            'breakdown'    => $breakdown,
            'total'        => $total,
        );
        $this->load->view('layout/header', $data);
        $this->load->view('revenue_report/index', $data);
        $this->load->view('layout/footer');
    }

    /** Nhập/sửa doanh thu 4 danh mục cho 1 tháng. */
    public function entry()
    {
        $period = $this->input->get('period') ?: date('Y-m');

        if ($this->input->method() === 'post')
        {
            $period = $this->input->post('period') ?: $period;
            foreach (Monthly_revenue_model::CATEGORIES as $category)
            {
                $revenue = (float) $this->input->post('revenue_'.$category);
                $note = $this->input->post('note_'.$category, TRUE);
                $this->Monthly_revenue_model->upsert($period, $category, $revenue, $note, $this->current_user['id']);
            }
            $this->audit('monthly_revenue', 'UPDATE', NULL, array('period' => $period));
            $this->session->set_flashdata('success', 'Đã lưu doanh thu '.payroll_period_label($period).'.');
            redirect('reports?period='.$period);
            return;
        }

        $existing = $this->Monthly_revenue_model->get_by_period($period);

        $data = array(
            'page_title'   => 'Nhập doanh thu',
            'current_user' => $this->current_user,
            'period'       => $period,
            'existing'     => $existing,
        );
        $this->load->view('layout/header', $data);
        $this->load->view('revenue_report/entry', $data);
        $this->load->view('layout/footer');
    }
}
