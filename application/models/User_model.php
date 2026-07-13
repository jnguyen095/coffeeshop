<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    protected $table = 'users';

    public function get_all()
    {
        return $this->db->order_by('fullname', 'ASC')->get($this->table)->result_array();
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row_array();
    }

    public function get_by_username($username)
    {
        return $this->db->where('username', $username)->get($this->table)->row_array();
    }

    public function verify_login($username, $password)
    {
        $user = $this->get_by_username($username);

        if ( ! $user || $user['status'] !== 'ACTIVE')
        {
            return FALSE;
        }

        return password_verify($password, $user['password']) ? $user : FALSE;
    }

    public function create($data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        if ( ! empty($data['password']))
        {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        else
        {
            unset($data['password']);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db->where('id', $id)->update($this->table, array('status' => 'INACTIVE', 'updated_at' => date('Y-m-d H:i:s')));
    }

    public function username_exists($username, $except_id = NULL)
    {
        $this->db->where('username', $username);
        if ($except_id)
        {
            $this->db->where('id !=', $except_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }
}
