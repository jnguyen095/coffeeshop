<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Khung giờ + giá tiền sân do admin cấu hình — dùng chung cho mọi sân. Xem [[051_create_court_time_slots_table]]. */
class Court_time_slot_model extends CI_Model
{
    protected $table = 'court_time_slots';

    public function get_all()
    {
        return $this->db->order_by('sort_order', 'ASC')->order_by('start_time', 'ASC')->get($this->table)->result_array();
    }

    public function get_active()
    {
        return $this->db->where('status', 'ACTIVE')->order_by('sort_order', 'ASC')->order_by('start_time', 'ASC')->get($this->table)->result_array();
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row_array();
    }

    /** Overlap check giống Court_booking_model::has_conflict — 2 khung không được đè giờ lên nhau. */
    public function has_overlap($start_time, $end_time, $exclude_id = NULL)
    {
        $this->db->where('start_time <', $end_time)->where('end_time >', $start_time);
        if ($exclude_id)
        {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }
}
