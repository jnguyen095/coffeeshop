<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Court_booking_model extends CI_Model
{
    protected $table = 'court_bookings';

    /** Khung giờ tính giá — sáng/chiều/tối. Chỉnh ở đây nếu quán đổi giờ hoạt động. */
    const SLOTS = array(
        'morning'   => array('label' => 'Sáng',  'start' => '06:00:00', 'end' => '12:00:00'),
        'afternoon' => array('label' => 'Chiều', 'start' => '12:00:00', 'end' => '18:00:00'),
        'evening'   => array('label' => 'Tối',   'start' => '18:00:00', 'end' => '23:00:00'),
    );

    /**
     * Tính tiền sân cho một khoảng [start_time, end_time), cộng dồn phần thời
     * lượng rơi vào từng khung giờ nhân với giá của khung đó (một buổi đặt có
     * thể chạy qua nhiều khung, ví dụ 11:00-13:00 tính nửa giờ giá sáng + 1 giờ giá chiều).
     */
    public function calc_fee($table, $start_time, $end_time)
    {
        $start = $this->_to_minutes($start_time);
        $end = $this->_to_minutes($end_time);
        $rates = array(
            'morning'   => (float) $table['rate_morning'],
            'afternoon' => (float) $table['rate_afternoon'],
            'evening'   => (float) $table['rate_evening'],
        );

        $fee = 0;
        foreach (self::SLOTS as $key => $slot)
        {
            $slot_start = $this->_to_minutes($slot['start']);
            $slot_end = $this->_to_minutes($slot['end']);
            $overlap_minutes = max(0, min($end, $slot_end) - max($start, $slot_start));
            $fee += ($overlap_minutes / 60) * $rates[$key];
        }
        return round($fee);
    }

    private function _to_minutes($time)
    {
        list($h, $m) = array_map('intval', explode(':', $time));
        return $h * 60 + $m;
    }

    /** Overlap check: two ranges [start,end) collide iff start1 < end2 AND start2 < end1. */
    public function has_conflict($table_id, $date, $start_time, $end_time, $exclude_id = NULL)
    {
        $this->db->where('table_id', $table_id)
            ->where('booking_date', $date)
            ->where_in('status', array('BOOKED', 'CHECKED_IN'))
            ->where('start_time <', $end_time)
            ->where('end_time >', $start_time);

        if ($exclude_id)
        {
            $this->db->where('id !=', $exclude_id);
        }

        return $this->db->get($this->table)->num_rows() > 0;
    }

    public function create_booking($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Creates one booking per matching weekday between $date_from and $date_to
     * (inclusive), skipping any date that already conflicts. Returns a summary:
     * ['group_id'=>, 'created'=>[dates], 'skipped'=>[dates]].
     */
    public function create_recurring($table_id, $customer_name, $customer_phone, $notes, array $weekdays, $start_time, $end_time, $date_from, $date_to, $created_by)
    {
        $group_id = gen_token(32);
        $created = array();
        $skipped = array();

        $cursor = strtotime($date_from);
        $end = strtotime($date_to);

        while ($cursor <= $end)
        {
            $day_of_week = (int) date('N', $cursor); // 1 (Mon) - 7 (Sun)
            if (in_array($day_of_week, $weekdays, TRUE))
            {
                $date = date('Y-m-d', $cursor);

                if ($this->has_conflict($table_id, $date, $start_time, $end_time))
                {
                    $skipped[] = $date;
                }
                else
                {
                    $this->create_booking(array(
                        'table_id'         => $table_id,
                        'customer_name'    => $customer_name,
                        'customer_phone'   => $customer_phone,
                        'booking_date'     => $date,
                        'start_time'       => $start_time,
                        'end_time'         => $end_time,
                        'status'           => 'BOOKED',
                        'booking_group_id' => $group_id,
                        'notes'            => $notes,
                        'created_by'       => $created_by,
                    ));
                    $created[] = $date;
                }
            }
            $cursor = strtotime('+1 day', $cursor);
        }

        return array('group_id' => $group_id, 'created' => $created, 'skipped' => $skipped);
    }

    public function get_by_id($id)
    {
        return $this->db->select('court_bookings.*, cafe_tables.table_name, cafe_tables.table_code, cafe_tables.rate_morning, cafe_tables.rate_afternoon, cafe_tables.rate_evening')
            ->from($this->table)
            ->join('cafe_tables', 'cafe_tables.id = court_bookings.table_id')
            ->where('court_bookings.id', $id)
            ->get()->row_array();
    }

    public function get_by_date($date, $table_id = NULL)
    {
        $this->db->select('court_bookings.*, cafe_tables.table_name, cafe_tables.table_code, cafe_tables.rate_morning, cafe_tables.rate_afternoon, cafe_tables.rate_evening')
            ->from($this->table)
            ->join('cafe_tables', 'cafe_tables.id = court_bookings.table_id')
            ->where('court_bookings.booking_date', $date)
            ->where_in('court_bookings.status', array('BOOKED', 'CHECKED_IN', 'COMPLETED'));

        if ($table_id)
        {
            $this->db->where('court_bookings.table_id', $table_id);
        }

        $bookings = $this->db->order_by('cafe_tables.table_code', 'ASC')->order_by('court_bookings.start_time', 'ASC')->get()->result_array();
        return $this->_with_estimated_fee($bookings);
    }

    /** Dùng cho lịch xem theo Tuần/Tháng — mọi lịch đặt còn hiệu lực trong khoảng ngày. */
    public function get_by_range($date_from, $date_to)
    {
        $bookings = $this->db->select('court_bookings.*, cafe_tables.table_name, cafe_tables.table_code, cafe_tables.rate_morning, cafe_tables.rate_afternoon, cafe_tables.rate_evening')
            ->from($this->table)
            ->join('cafe_tables', 'cafe_tables.id = court_bookings.table_id')
            ->where('court_bookings.booking_date >=', $date_from)
            ->where('court_bookings.booking_date <=', $date_to)
            ->where_in('court_bookings.status', array('BOOKED', 'CHECKED_IN', 'COMPLETED'))
            ->order_by('court_bookings.booking_date', 'ASC')
            ->order_by('court_bookings.start_time', 'ASC')
            ->get()->result_array();
        return $this->_with_estimated_fee($bookings);
    }

    private function _with_estimated_fee($bookings)
    {
        foreach ($bookings as &$b)
        {
            $b['estimated_fee'] = $this->calc_fee($b, $b['start_time'], $b['end_time']);
        }
        return $bookings;
    }

    public function get_by_group($group_id)
    {
        return $this->db->where('booking_group_id', $group_id)
            ->where_in('status', array('BOOKED', 'CHECKED_IN'))
            ->order_by('booking_date', 'ASC')
            ->get($this->table)->result_array();
    }

    public function cancel($id)
    {
        return $this->db->where('id', $id)->where('status', 'BOOKED')->update($this->table, array('status' => 'CANCELLED'));
    }

    public function cancel_group($group_id)
    {
        return $this->db->where('booking_group_id', $group_id)->where('status', 'BOOKED')->update($this->table, array('status' => 'CANCELLED'));
    }

    public function mark_checked_in($id, $table_session_id)
    {
        return $this->db->where('id', $id)->update($this->table, array(
            'status'           => 'CHECKED_IN',
            'table_session_id' => $table_session_id,
        ));
    }

    /** Doanh thu dịch vụ sân (tiền sân + thuê vợt/trang phục/nhặt bóng...) theo từng sân, đơn đã thanh toán. */
    public function revenue_by_court($from, $to)
    {
        return $this->db->select('cafe_tables.table_name, SUM(order_items.amount) as total_revenue')
            ->from('order_items')
            ->join('order_sessions', 'order_sessions.id = order_items.order_session_id')
            ->join('table_sessions', 'table_sessions.id = order_sessions.table_session_id')
            ->join('cafe_tables', 'cafe_tables.id = table_sessions.table_id')
            ->join('products', 'products.id = order_items.product_id')
            ->join('categories', 'categories.id = products.category_id')
            ->where('categories.court_only', 1)
            ->where('order_items.status', 'ACTIVE')
            ->where('order_sessions.status', 'PAID')
            ->where('order_sessions.paid_at >=', $from.' 00:00:00')
            ->where('order_sessions.paid_at <=', $to.' 23:59:59')
            ->group_by('cafe_tables.id')
            ->order_by('total_revenue', 'DESC')
            ->get()->result_array();
    }

    /** Xu hướng doanh thu dịch vụ sân theo ngày, dùng cho biểu đồ đường. */
    public function revenue_trend($from, $to)
    {
        return $this->db->select("DATE(order_sessions.paid_at) as day, SUM(order_items.amount) as total_revenue")
            ->from('order_items')
            ->join('order_sessions', 'order_sessions.id = order_items.order_session_id')
            ->join('products', 'products.id = order_items.product_id')
            ->join('categories', 'categories.id = products.category_id')
            ->where('categories.court_only', 1)
            ->where('order_items.status', 'ACTIVE')
            ->where('order_sessions.status', 'PAID')
            ->where('order_sessions.paid_at >=', $from.' 00:00:00')
            ->where('order_sessions.paid_at <=', $to.' 23:59:59')
            ->group_by('DATE(order_sessions.paid_at)')
            ->order_by('day', 'ASC')
            ->get()->result_array();
    }

    /** Số lượng booking theo trạng thái trong khoảng ngày, dùng cho biểu đồ tròn. */
    public function bookings_by_status($from, $to)
    {
        return $this->db->select('status, COUNT(*) as total')
            ->where('booking_date >=', $from)
            ->where('booking_date <=', $to)
            ->group_by('status')
            ->get($this->table)->result_array();
    }

    /** Tổng số phút chơi thực tế rơi vào từng khung giờ (sáng/chiều/tối), booking đã check-in/hoàn tất. */
    public function usage_by_slot($from, $to)
    {
        $bookings = $this->db->select('start_time, end_time')
            ->where('booking_date >=', $from)
            ->where('booking_date <=', $to)
            ->where_in('status', array('CHECKED_IN', 'COMPLETED'))
            ->get($this->table)->result_array();

        $totals = array('morning' => 0, 'afternoon' => 0, 'evening' => 0);
        foreach ($bookings as $b)
        {
            $start = $this->_to_minutes($b['start_time']);
            $end = $this->_to_minutes($b['end_time']);
            foreach (self::SLOTS as $key => $slot)
            {
                $slot_start = $this->_to_minutes($slot['start']);
                $slot_end = $this->_to_minutes($slot['end']);
                $totals[$key] += max(0, min($end, $slot_end) - max($start, $slot_start));
            }
        }
        return $totals;
    }

    /**
     * Tỷ lệ lấp đầy từng sân = tổng phút đã chơi (check-in/hoàn tất) / tổng phút
     * hoạt động có thể có trong khoảng ngày (dựa trên khung giờ SLOTS: 06:00-23:00).
     */
    public function utilization_by_court($from, $to)
    {
        $days = (int) round((strtotime($to) - strtotime($from)) / 86400) + 1;

        $available_minutes_per_day = 0;
        foreach (self::SLOTS as $slot)
        {
            $available_minutes_per_day += $this->_to_minutes($slot['end']) - $this->_to_minutes($slot['start']);
        }
        $available_minutes = $available_minutes_per_day * max(1, $days);

        $courts = $this->db->where('table_type', 'COURT')->order_by('table_code', 'ASC')->get('cafe_tables')->result_array();

        $bookings = $this->db->select('table_id, start_time, end_time')
            ->where('booking_date >=', $from)
            ->where('booking_date <=', $to)
            ->where_in('status', array('CHECKED_IN', 'COMPLETED'))
            ->get($this->table)->result_array();

        $used_minutes = array();
        foreach ($bookings as $b)
        {
            $minutes = $this->_to_minutes($b['end_time']) - $this->_to_minutes($b['start_time']);
            $used_minutes[$b['table_id']] = (isset($used_minutes[$b['table_id']]) ? $used_minutes[$b['table_id']] : 0) + $minutes;
        }

        $result = array();
        foreach ($courts as $c)
        {
            $used = isset($used_minutes[$c['id']]) ? $used_minutes[$c['id']] : 0;
            $result[] = array(
                'table_name'      => $c['table_name'],
                'used_minutes'    => $used,
                'utilization_pct' => $available_minutes > 0 ? round($used / $available_minutes * 100, 1) : 0,
            );
        }
        return $result;
    }
}
