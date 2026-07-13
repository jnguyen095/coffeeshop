<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('kds');
        $this->load->model('User_model');
    }

    public function index()
    {
        redirect('login');
    }

    public function login()
    {
        if ($this->session->userdata('user'))
        {
            redirect('dashboard');
        }

        $error = NULL;

        if ($this->input->method() === 'post')
        {
            $this->form_validation->set_rules('username', 'Tên đăng nhập', 'required|trim');
            $this->form_validation->set_rules('password', 'Mật khẩu', 'required');

            if ($this->form_validation->run())
            {
                $user = $this->User_model->verify_login($this->input->post('username', TRUE), $this->input->post('password'));

                if ($user)
                {
                    unset($user['password']);
                    $this->session->set_userdata('user', $user);

                    $this->load->model('Audit_log_model');
                    $this->Audit_log_model->log('auth', 'LOGIN', NULL, array('username' => $user['username']), $user['id']);

                    redirect($this->_home_for_role($user['role']));
                }
                $error = 'Sai tên đăng nhập hoặc mật khẩu.';
            }
            else
            {
                $error = validation_errors();
            }
        }

        $this->load->view('auth/login', array('error' => $error));
    }

    public function logout()
    {
        $user = $this->session->userdata('user');
        if ($user)
        {
            $this->load->model('Audit_log_model');
            $this->Audit_log_model->log('auth', 'LOGOUT', NULL, NULL, $user['id']);
        }
        $this->session->unset_userdata('user');
        $this->session->sess_destroy();
        redirect('login');
    }

    private function _home_for_role($role)
    {
        switch ($role)
        {
            case 'BARISTA': return 'kitchen';
            case 'CASHIER': return 'cashier';
            default: return 'dashboard';
        }
    }
}
