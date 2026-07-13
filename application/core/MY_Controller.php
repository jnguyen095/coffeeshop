<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base controller for authenticated, browser-rendered staff/back-office screens.
 * Handles session auth + simple role-based access control (RBAC).
 */
class MY_Controller extends CI_Controller
{
    protected $current_user;

    /** @var string[] Roles allowed to access the controller. Empty = any logged-in role. */
    protected $allowed_roles = array();

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('kds');

        $this->current_user = $this->session->userdata('user');

        if ( ! $this->current_user)
        {
            redirect('login');
            return;
        }

        if ( ! empty($this->allowed_roles) && ! in_array($this->current_user['role'], $this->allowed_roles, TRUE))
        {
            $this->output->set_status_header(403);
            $this->load->view('errors/forbidden', array('current_user' => $this->current_user));
            exit;
        }
    }

    protected function audit($module, $action, $old_data = NULL, $new_data = NULL)
    {
        $this->load->model('Audit_log_model');
        $this->Audit_log_model->log($module, $action, $old_data, $new_data, $this->current_user['id']);
    }
}
