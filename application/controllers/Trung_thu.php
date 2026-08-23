<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Form đăng ký nhận quà Trung Thu — công khai, không cần đăng nhập, phụ huynh
 * quét QR trên điện thoại để điền. Không kế thừa MY_Controller vì controller
 * đó bắt buộc đăng nhập (xem application/core/MY_Controller.php).
 */
class Trung_thu extends CI_Controller
{
    const EVENT_LABEL = '08:00 – 10:00 | Thứ Bảy, 26/09/2026';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Trung_thu_registration_model');
    }

    public function index()
    {
        $error = NULL;
        $old = array('phone' => '', 'parent_name' => '', 'kid_count' => 1);

        if ($this->input->method() === 'post')
        {
            $phone = trim((string) $this->input->post('phone', TRUE));
            $parent_name = trim((string) $this->input->post('parent_name', TRUE));
            $kid_count = (int) $this->input->post('kid_count');
            $old = array('phone' => $phone, 'parent_name' => $parent_name, 'kid_count' => $kid_count ?: 1);

            $phone_digits = preg_replace('/[^0-9]/', '', $phone);

            if ($phone === '' || $parent_name === '' || $kid_count < 1)
            {
                $error = 'Vui lòng điền đầy đủ thông tin.';
            }
            elseif (strlen($phone_digits) < 9 || strlen($phone_digits) > 11)
            {
                $error = 'Số điện thoại không hợp lệ.';
            }
            elseif ($this->Trung_thu_registration_model->phone_exists($phone_digits))
            {
                $error = 'Số điện thoại này đã đăng ký rồi. Mỗi số điện thoại chỉ đăng ký được 1 lần.';
            }
            else
            {
                $this->Trung_thu_registration_model->create(array(
                    'phone'       => $phone_digits,
                    'parent_name' => $parent_name,
                    'kid_count'   => $kid_count,
                ));

                $this->session->set_flashdata('tt_parent_name', $parent_name);
                $this->session->set_flashdata('tt_kid_count', $kid_count);
                redirect('trung-thu/thank-you');
                return;
            }
        }

        $data = array(
            'error'       => $error,
            'old'         => $old,
            'event_label' => self::EVENT_LABEL,
        );
        $this->load->view('trung_thu/form', $data);
    }

    public function thank_you()
    {
        $parent_name = $this->session->flashdata('tt_parent_name');
        if ($parent_name === NULL) redirect('trung-thu');

        $data = array(
            'parent_name' => $parent_name,
            'kid_count'   => $this->session->flashdata('tt_kid_count'),
            'event_label' => self::EVENT_LABEL,
        );
        $this->load->view('trung_thu/thank_you', $data);
    }
}
