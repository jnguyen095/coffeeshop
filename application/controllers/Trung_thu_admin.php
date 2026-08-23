<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Quản lý đăng ký nhận quà Trung Thu (xem/sửa/xóa) — xem form công khai ở [[Trung_thu]]. */
class Trung_thu_admin extends MY_Controller
{
    protected $allowed_roles = array('ADMIN');

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Trung_thu_registration_model');
    }

    public function index()
    {
        $data = array(
            'page_title'    => 'Đăng ký quà Trung Thu',
            'current_user'  => $this->current_user,
            'registrations' => $this->Trung_thu_registration_model->get_all(),
            'total_count'   => $this->Trung_thu_registration_model->count_total(),
            'total_kids'    => $this->Trung_thu_registration_model->sum_kids(),
        );
        $this->load->view('layout/header', $data);
        $this->load->view('trung_thu_admin/index', $data);
        $this->load->view('layout/footer');
    }

    public function edit($id)
    {
        $reg = $this->Trung_thu_registration_model->get_by_id($id);
        if ( ! $reg) show_404();
        $error = NULL;

        if ($this->input->method() === 'post')
        {
            $phone = trim((string) $this->input->post('phone', TRUE));
            $parent_name = trim((string) $this->input->post('parent_name', TRUE));
            $kid_count = (int) $this->input->post('kid_count');
            $phone_digits = preg_replace('/[^0-9]/', '', $phone);

            if ($phone === '' || $parent_name === '' || $kid_count < 1)
            {
                $error = 'Vui lòng điền đầy đủ thông tin.';
            }
            elseif (strlen($phone_digits) < 9 || strlen($phone_digits) > 11)
            {
                $error = 'Số điện thoại không hợp lệ.';
            }
            elseif ($this->Trung_thu_registration_model->phone_exists($phone_digits, $id))
            {
                $error = 'Số điện thoại này đã được đăng ký bởi 1 lượt khác.';
            }
            else
            {
                $this->Trung_thu_registration_model->update($id, array(
                    'phone'       => $phone_digits,
                    'parent_name' => $parent_name,
                    'kid_count'   => $kid_count,
                ));
                $this->audit('trung_thu_registration', 'UPDATE', $reg, array('id' => $id));
                redirect('trung-thu/admin');
                return;
            }

            $reg = array_merge($reg, array('phone' => $phone, 'parent_name' => $parent_name, 'kid_count' => $kid_count));
        }

        $data = array('page_title' => 'Sửa đăng ký', 'current_user' => $this->current_user, 'reg' => $reg, 'error' => $error);
        $this->load->view('layout/header', $data);
        $this->load->view('trung_thu_admin/form', $data);
        $this->load->view('layout/footer');
    }

    public function delete($id)
    {
        $reg = $this->Trung_thu_registration_model->get_by_id($id);
        $this->Trung_thu_registration_model->delete($id);
        $this->audit('trung_thu_registration', 'DELETE', $reg, NULL);
        redirect('trung-thu/admin');
    }
}
