<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'dashboard';
$route['404_override'] = 'errors/page_missing';
$route['translate_uri_dashes'] = FALSE;

// Auth
$route['login'] = 'auth/login';
$route['logout'] = 'auth/logout';

// Staff / Cashier / Barista / Admin screens
$route['dashboard'] = 'dashboard/index';
$route['tables'] = 'tables/index';
$route['tables/manage'] = 'tables/manage';
$route['tables/manage/create'] = 'tables/manage_create';
$route['tables/manage/(:num)/edit'] = 'tables/manage_edit/$1';
$route['tables/manage/(:num)/delete'] = 'tables/manage_delete/$1';
$route['tables/manage/(:num)/reset-status'] = 'tables/manage_reset_status/$1';

$route['tables/(:num)'] = 'tables/detail/$1';
$route['tables/(:num)/open'] = 'tables/open/$1';
$route['tables/(:num)/transfer'] = 'tables/transfer/$1';
$route['tables/(:num)/merge'] = 'tables/merge/$1';
$route['tables/(:num)/print-provisional'] = 'tables/print_provisional/$1';
$route['tables/(:num)/qr'] = 'tables/qr/$1';

$route['orders'] = 'orders/index';
$route['orders/(:num)'] = 'orders/detail/$1';
$route['orders/(:num)/add-item'] = 'orders/add_item/$1';
$route['orders/(:num)/update-item/(:num)'] = 'orders/update_item/$1/$2';
$route['orders/(:num)/cancel-item/(:num)'] = 'orders/cancel_item/$1/$2';
$route['orders/(:num)/checkout'] = 'orders/checkout/$1';

$route['takeaway/create'] = 'takeaway/create';

$route['kitchen'] = 'kitchen/index';
$route['kitchen/ticket/(:num)'] = 'kitchen/ticket/$1';
$route['kitchen/ticket/(:num)/status'] = 'kitchen/update_status/$1';

$route['cashier'] = 'cashier/index';
$route['cashier/(:num)'] = 'cashier/detail/$1';
$route['cashier/(:num)/close-bill'] = 'cashier/close_bill/$1';
$route['cashier/(:num)/pay'] = 'cashier/pay/$1';
$route['cashier/(:num)/invoice'] = 'cashier/invoice/$1';

$route['payments'] = 'payments/index';

$route['reports'] = 'reports/index';
$route['reports/daily-revenue'] = 'reports/daily_revenue';
$route['reports/monthly-revenue'] = 'reports/monthly_revenue';
$route['reports/top-products'] = 'reports/top_products';
$route['reports/table-usage'] = 'reports/table_usage';
$route['reports/kitchen-performance'] = 'reports/kitchen_performance';
$route['reports/payment-summary'] = 'reports/payment_summary';

$route['users'] = 'users/index';
$route['users/create'] = 'users/create';
$route['users/(:num)/edit'] = 'users/edit/$1';
$route['users/(:num)/delete'] = 'users/delete/$1';

$route['products'] = 'products/index';
$route['products/create'] = 'products/create';
$route['products/(:num)/edit'] = 'products/edit/$1';
$route['products/(:num)/delete'] = 'products/delete/$1';

$route['categories'] = 'categories/index';
$route['categories/create'] = 'categories/create';
$route['categories/(:num)/edit'] = 'categories/edit/$1';
$route['categories/(:num)/delete'] = 'categories/delete/$1';

// Customer QR Ordering (public, no auth)
$route['menu/(:any)'] = 'menu/index/$1';
$route['menu/(:any)/cart'] = 'menu/cart/$1';
$route['menu/(:any)/history'] = 'menu/history/$1';
$route['menu/(:any)/(:any)'] = 'menu/visit/$1/$2';

// JSON API — per SDS section 12 (customer-facing, token based)
$route['api/order/create'] = 'api_order/create';
$route['api/order/add-item'] = 'api_order/add_item';
$route['api/order/remove-item'] = 'api_order/remove_item';
$route['api/order/current-by-token/(:any)/(:any)'] = 'api_order/current_by_token/$1/$2';
$route['api/payment'] = 'api_payment/create';
$route['api/call/create'] = 'api_call/create';

// JSON API — internal polling for staff/KDS screens
$route['api/kitchen/tickets'] = 'api_kitchen/tickets';
$route['api/kitchen/ticket/(:num)/status'] = 'api_kitchen/update_status/$1';
$route['api/kitchen/ticket-item/(:num)/status'] = 'api_kitchen/update_item_status/$1';
$route['api/tables/status'] = 'api_tables/status';
$route['api/assistance/pending'] = 'api_assistance/pending';
$route['api/assistance/(:num)/resolve'] = 'api_assistance/resolve/$1';
