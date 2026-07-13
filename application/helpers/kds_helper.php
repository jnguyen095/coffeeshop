<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('json_response'))
{
    function json_response($data, $status_code = 200)
    {
        $ci =& get_instance();
        $ci->output
            ->set_status_header($status_code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data));
    }
}

if ( ! function_exists('money_format_vnd'))
{
    function money_format_vnd($amount)
    {
        return number_format((float) $amount, 0, ',', '.').'đ';
    }
}

if ( ! function_exists('order_status_badge'))
{
    function order_status_badge($status)
    {
        $map = array(
            'OPEN'          => 'primary',
            'WAIT_PAYMENT'  => 'warning',
            'PAID'          => 'success',
            'CANCELLED'     => 'secondary',
        );
        return isset($map[$status]) ? $map[$status] : 'light';
    }
}

if ( ! function_exists('table_status_badge'))
{
    function table_status_badge($status)
    {
        $map = array(
            'AVAILABLE'    => 'success',
            'OPEN'         => 'primary',
            'WAIT_PAYMENT' => 'warning',
            'PAID'         => 'info',
        );
        return isset($map[$status]) ? $map[$status] : 'light';
    }
}

if ( ! function_exists('kitchen_status_badge'))
{
    function kitchen_status_badge($status)
    {
        $map = array(
            'NEW'       => 'danger',
            'PREPARING' => 'warning',
            'COMPLETED' => 'success',
        );
        return isset($map[$status]) ? $map[$status] : 'light';
    }
}

if ( ! function_exists('role_label'))
{
    function role_label($role)
    {
        $map = array(
            'ADMIN'   => 'Quản trị viên',
            'CASHIER' => 'Thu ngân',
            'BARISTA' => 'Pha chế',
            'STAFF'   => 'Nhân viên phục vụ',
        );
        return isset($map[$role]) ? $map[$role] : $role;
    }
}

if ( ! function_exists('gen_token'))
{
    function gen_token($length = 32)
    {
        return bin2hex(random_bytes((int) ceil($length / 2)));
    }
}

if ( ! function_exists('gen_no'))
{
    function gen_no($prefix)
    {
        return $prefix.date('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    }
}
