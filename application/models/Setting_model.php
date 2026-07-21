<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setting_model extends CI_Model
{
    protected $table = 'settings';

    public function get($key, $default = NULL)
    {
        $row = $this->db->where('setting_key', $key)->get($this->table)->row_array();
        return $row ? $row['setting_value'] : $default;
    }

    public function set($key, $value)
    {
        $exists = $this->db->where('setting_key', $key)->get($this->table)->row_array();
        $data = array('setting_value' => $value, 'updated_at' => date('Y-m-d H:i:s'));

        if ($exists)
        {
            return $this->db->where('setting_key', $key)->update($this->table, $data);
        }

        $data['setting_key'] = $key;
        return $this->db->insert($this->table, $data);
    }

    /** Tỷ lệ VAT dạng thập phân (vd 0.08), dùng trực tiếp trong tính toán hóa đơn. */
    public function get_vat_rate()
    {
        return ((float) $this->get('vat_percent', 8)) / 100;
    }

    public function get_vat_percent()
    {
        return (float) $this->get('vat_percent', 8);
    }

    /** Giờ mở cửa nhận đặt sân, định dạng 'HH:MM'. */
    public function get_booking_start_time()
    {
        return $this->get('booking_start_time', '06:00');
    }

    /** Giờ đóng cửa nhận đặt sân, định dạng 'HH:MM'. */
    public function get_booking_end_time()
    {
        return $this->get('booking_end_time', '22:00');
    }
}
