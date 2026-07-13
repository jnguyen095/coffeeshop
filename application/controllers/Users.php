<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller
{
    protected $allowed_roles = array('ADMIN');

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
    }

    public function index()
    {
        $data = array(
            'page_title'   => 'Người dùng',
            'current_user' => $this->current_user,
            'users'        => $this->User_model->get_all(),
        );
        $this->load->view('layout/header', $data);
        $this->load->view('users/index', $data);
        $this->load->view('layout/footer');
    }

    public function create()
    {
        $error = NULL;
        if ($this->input->method() === 'post')
        {
            $username = $this->input->post('username', TRUE);
            if ($this->User_model->username_exists($username))
            {
                $error = 'Tên đăng nhập đã tồn tại.';
            }
            else
            {
                $id = $this->User_model->create(array(
                    'username' => $username,
                    'password' => $this->input->post('password'),
                    'fullname' => $this->input->post('fullname', TRUE),
                    'role'     => $this->input->post('role'),
                    'status'   => 'ACTIVE',
                ));
                $this->audit('user', 'CREATE', NULL, array('id' => $id));
                redirect('users');
                return;
            }
        }

        $data = array('page_title' => 'Thêm người dùng', 'current_user' => $this->current_user, 'user' => NULL, 'error' => $error);
        $this->load->view('layout/header', $data);
        $this->load->view('users/form', $data);
        $this->load->view('layout/footer');
    }

    public function edit($id)
    {
        $user = $this->User_model->get_by_id($id);
        if ( ! $user) show_404();
        $error = NULL;

        if ($this->input->method() === 'post')
        {
            $username = $this->input->post('username', TRUE);
            if ($this->User_model->username_exists($username, $id))
            {
                $error = 'Tên đăng nhập đã tồn tại.';
            }
            else
            {
                $this->User_model->update($id, array(
                    'username' => $username,
                    'password' => $this->input->post('password'),
                    'fullname' => $this->input->post('fullname', TRUE),
                    'role'     => $this->input->post('role'),
                    'status'   => $this->input->post('status'),
                ));
                $this->audit('user', 'UPDATE', array('id' => $id), NULL);
                redirect('users');
                return;
            }
        }

        $data = array('page_title' => 'Sửa người dùng', 'current_user' => $this->current_user, 'user' => $user, 'error' => $error);
        $this->load->view('layout/header', $data);
        $this->load->view('users/form', $data);
        $this->load->view('layout/footer');
    }

    public function delete($id)
    {
        $this->User_model->delete($id);
        $this->audit('user', 'DEACTIVATE', NULL, array('id' => $id));
        redirect('users');
    }
}
