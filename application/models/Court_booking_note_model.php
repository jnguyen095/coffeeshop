<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Nhật ký ghi chú cho lịch đặt sân — xem [[049_create_court_booking_notes_table]].
 * Với lịch lặp lại, ghi chú gắn theo booking_group_id nên hiện ra ở mọi
 * buổi trong cùng chuỗi; lịch đặt đơn lẻ thì gắn trực tiếp vào booking_id.
 */
class Court_booking_note_model extends CI_Model
{
    protected $table = 'court_booking_notes';

    public function get_for_booking($booking)
    {
        $this->db->select('court_booking_notes.*, users.fullname AS created_by_name')
            ->from($this->table)
            ->join('users', 'users.id = court_booking_notes.created_by', 'left');

        if ( ! empty($booking['booking_group_id']))
        {
            $this->db->where('booking_group_id', $booking['booking_group_id']);
        }
        else
        {
            $this->db->where('booking_id', $booking['id']);
        }

        return $this->db->order_by('created_at', 'ASC')->get()->result_array();
    }

    public function add($booking, $note, $created_by)
    {
        $data = array(
            'note'       => $note,
            'created_by' => $created_by,
            'created_at' => date('Y-m-d H:i:s'),
        );

        if ( ! empty($booking['booking_group_id']))
        {
            $data['booking_group_id'] = $booking['booking_group_id'];
        }
        else
        {
            $data['booking_id'] = $booking['id'];
        }

        return $this->db->insert($this->table, $data);
    }
}
