<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_model extends CI_Model
{
    protected $table = 'order_sessions';
    const VAT_RATE = 0.08;

    public function create_for_table_session($table_session_id)
    {
        $data = array(
            'order_no'          => gen_no('ORD'),
            'table_session_id'  => $table_session_id,
            'status'            => 'OPEN',
            'subtotal'          => 0,
            'discount_amount'   => 0,
            'vat_amount'        => 0,
            'total_amount'      => 0,
            'created_at'        => date('Y-m-d H:i:s'),
        );
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row_array();
    }

    public function get_open_by_table_session($table_session_id)
    {
        return $this->db->where('table_session_id', $table_session_id)
            ->where('status', 'OPEN')
            ->order_by('id', 'DESC')
            ->get($this->table)->row_array();
    }

    public function get_active_by_table_session($table_session_id)
    {
        return $this->db->where('table_session_id', $table_session_id)
            ->where_in('status', array('OPEN', 'WAIT_PAYMENT'))
            ->order_by('id', 'DESC')
            ->get($this->table)->row_array();
    }

    public function recalc_totals($order_id)
    {
        $order = $this->get_by_id($order_id);
        if ( ! $order) return;

        $subtotal = (float) $this->db->select_sum('amount')
            ->where('order_session_id', $order_id)
            ->where('status', 'ACTIVE')
            ->get('order_items')->row('amount');

        $vat = round($subtotal * self::VAT_RATE);
        $total = $subtotal - (float) $order['discount_amount'] + $vat;

        $this->db->where('id', $order_id)->update($this->table, array(
            'subtotal'     => $subtotal,
            'vat_amount'   => $vat,
            'total_amount' => max(0, $total),
        ));
    }

    public function set_discount($order_id, $discount_amount)
    {
        $this->db->where('id', $order_id)->update($this->table, array('discount_amount' => $discount_amount));
        $this->recalc_totals($order_id);
    }

    public function mark_wait_payment($id)
    {
        return $this->db->where('id', $id)->update($this->table, array('status' => 'WAIT_PAYMENT'));
    }

    public function mark_paid($id)
    {
        return $this->db->where('id', $id)->update($this->table, array('status' => 'PAID', 'paid_at' => date('Y-m-d H:i:s')));
    }

    public function cancel($id)
    {
        return $this->db->where('id', $id)->update($this->table, array('status' => 'CANCELLED'));
    }

    public function get_detail($id)
    {
        return $this->db->select('order_sessions.*, table_sessions.table_id, cafe_tables.table_name, cafe_tables.table_code')
            ->from($this->table)
            ->join('table_sessions', 'table_sessions.id = order_sessions.table_session_id')
            ->join('cafe_tables', 'cafe_tables.id = table_sessions.table_id')
            ->where('order_sessions.id', $id)
            ->get()->row_array();
    }

    public function get_list($filters = array())
    {
        $this->db->select('order_sessions.*, cafe_tables.table_name, cafe_tables.table_code')
            ->from($this->table)
            ->join('table_sessions', 'table_sessions.id = order_sessions.table_session_id')
            ->join('cafe_tables', 'cafe_tables.id = table_sessions.table_id');

        if ( ! empty($filters['status']))
        {
            $this->db->where('order_sessions.status', $filters['status']);
        }
        if ( ! empty($filters['date']))
        {
            $this->db->where('DATE(order_sessions.created_at)', $filters['date']);
        }

        return $this->db->order_by('order_sessions.id', 'DESC')->get()->result_array();
    }

    public function daily_revenue($date)
    {
        return $this->db->select_sum('total_amount')
            ->where('status', 'PAID')
            ->where('DATE(paid_at)', $date)
            ->get($this->table)->row('total_amount');
    }

    public function revenue_by_day($from, $to)
    {
        return $this->db->select('DATE(paid_at) as day, SUM(total_amount) as revenue, COUNT(*) as orders_count')
            ->where('status', 'PAID')
            ->where('paid_at >=', $from.' 00:00:00')
            ->where('paid_at <=', $to.' 23:59:59')
            ->group_by('DATE(paid_at)')
            ->order_by('day', 'ASC')
            ->get($this->table)->result_array();
    }
}
