<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_log_model extends CI_Model
{
    protected $table = 'audit_logs';

    public function log($module, $action, $old_data = NULL, $new_data = NULL, $user_id = NULL)
    {
        $this->db->insert($this->table, array(
            'module'     => $module,
            'action'     => $action,
            'old_data'   => $old_data !== NULL ? json_encode($old_data) : NULL,
            'new_data'   => $new_data !== NULL ? json_encode($new_data) : NULL,
            'user_id'    => $user_id,
            'created_at' => date('Y-m-d H:i:s'),
        ));
        return $this->db->insert_id();
    }

    public function get_recent($limit = 100)
    {
        return $this->db->order_by('id', 'DESC')->limit($limit)->get($this->table)->result_array();
    }
}
