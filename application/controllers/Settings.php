<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends MY_Controller
{
    protected $allowed_roles = array('ADMIN');

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Setting_model');
    }

    public function index()
    {
        $error = NULL;

        if ($this->input->method() === 'post')
        {
            $form = $this->input->post('form');

            if ($form === 'booking_hours')
            {
                $booking_start_time = $this->input->post('booking_start_time');
                $booking_end_time = $this->input->post('booking_end_time');

                if ( ! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', (string) $booking_start_time)
                    || ! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', (string) $booking_end_time))
                {
                    $error = 'Giờ bắt đầu/kết thúc không hợp lệ.';
                }
                elseif ($booking_end_time <= $booking_start_time)
                {
                    $error = 'Giờ kết thúc phải sau giờ bắt đầu.';
                }
                else
                {
                    $old = array(
                        'booking_start_time' => $this->Setting_model->get_booking_start_time(),
                        'booking_end_time'   => $this->Setting_model->get_booking_end_time(),
                    );
                    $this->Setting_model->set('booking_start_time', $booking_start_time);
                    $this->Setting_model->set('booking_end_time', $booking_end_time);
                    $this->audit('settings', 'UPDATE_BOOKING_HOURS', $old, array('booking_start_time' => $booking_start_time, 'booking_end_time' => $booking_end_time));
                    redirect('settings');
                    return;
                }
            }
            else
            {
                $vat_percent = $this->input->post('vat_percent');

                if ( ! is_numeric($vat_percent) || $vat_percent < 0 || $vat_percent > 100)
                {
                    $error = 'VAT phải là một số từ 0 đến 100.';
                }
                else
                {
                    $old = $this->Setting_model->get_vat_percent();
                    $this->Setting_model->set('vat_percent', (string) (float) $vat_percent);
                    $this->audit('settings', 'UPDATE_VAT', array('vat_percent' => $old), array('vat_percent' => (float) $vat_percent));
                    redirect('settings');
                    return;
                }
            }
        }

        $data = array(
            'page_title'          => 'Cài đặt',
            'current_user'        => $this->current_user,
            'vat_percent'         => $this->Setting_model->get_vat_percent(),
            'booking_start_time'  => $this->Setting_model->get_booking_start_time(),
            'booking_end_time'    => $this->Setting_model->get_booking_end_time(),
            'error'               => $error,
        );
        $this->load->view('layout/header', $data);
        $this->load->view('settings/index', $data);
        $this->load->view('layout/footer');
    }
}
