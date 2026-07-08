<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Salary Helper Functions
 */

/**
 * Get base currency with fallback
 * @return object
 */
function get_base_currency_fallback()
{
    $CI = &get_instance();

    // Try to get base currency from database
    $CI->db->where('isdefault', 1);
    $currency = $CI->db->get(db_prefix() . 'currencies')->row();

    if ($currency) {
        return $currency;
    }

    // Fallback currency object
    return (object) [
        'symbol' => '$',
        'name' => 'USD',
        'decimal_separator' => '.',
        'thousand_separator' => ',',
        'placement' => 'before',
        'isdefault' => 1
    ];
}

/**
 * Get salary statistics for dashboard
 * @return array
 */
function get_salary_statistics()
{
    $CI = &get_instance();

    $stats = [];

    // Total staff with salary
    $CI->db->where('active', 1);
    $CI->db->where('initial_salary IS NOT NULL');
    $stats['total_staff'] = $CI->db->count_all_results(db_prefix() . 'staff');

    // Total salary amount
    $CI->db->select_sum('current_salary');
    $CI->db->where('active', 1);
    $CI->db->where('current_salary IS NOT NULL');
    $result = $CI->db->get(db_prefix() . 'staff')->row();
    $stats['total_salary'] = $result->current_salary ?: 0;

    // Pending advance requests
    $CI->db->where('status', 'pending');
    $stats['pending_advance_requests'] = $CI->db->count_all_results(db_prefix() . 'staff_advance_salary');

    // Monthly advance amount
    $CI->db->select_sum('amount');
    $CI->db->where('status', 'approved');
    $CI->db->where('MONTH(request_date)', date('m'));
    $CI->db->where('YEAR(request_date)', date('Y'));
    $result = $CI->db->get(db_prefix() . 'staff_advance_salary')->row();
    $stats['monthly_advance'] = $result->amount ?: 0;

    return $stats;
}

/**
 * Get recent salary updates
 * @param int $limit
 * @return array
 */
function get_recent_salary_updates($limit = 5)
{
    $CI = &get_instance();

    $CI->db->select('s.staffid, s.firstname, s.lastname, s.current_salary, s.salary_effective_date');
    $CI->db->from(db_prefix() . 'staff s');
    $CI->db->where('s.active', 1);
    $CI->db->where('s.salary_effective_date IS NOT NULL');
    $CI->db->order_by('s.salary_effective_date', 'DESC');
    $CI->db->limit($limit);

    return $CI->db->get()->result_array();
}

/**
 * Get recent advance requests
 * @param int $limit
 * @return array
 */
function get_recent_advance_requests($limit = 5)
{
    $CI = &get_instance();

    $CI->db->select('sas.*, s.firstname, s.lastname');
    $CI->db->from(db_prefix() . 'staff_advance_salary sas');
    $CI->db->join(db_prefix() . 'staff s', 's.staffid = sas.staff_id');
    $CI->db->order_by('sas.request_date', 'DESC');
    $CI->db->limit($limit);

    return $CI->db->get()->result_array();
}

/**
 * Get staff salary information
 * @param int $staff_id
 * @return object
 */
function get_staff_salary_info($staff_id)
{
    $CI = &get_instance();

    $CI->db->select('s.*, ss.advance_salary, ss.status as advance_status');
    $CI->db->from(db_prefix() . 'staff s');
    $CI->db->join(db_prefix() . 'staff_salary ss', 's.staffid = ss.staff_id AND ss.status = "active"', 'left');
    $CI->db->where('s.staffid', $staff_id);

    return $CI->db->get()->row();
}

/**
 * Get advance salary requests for staff
 * @param int $staff_id
 * @return array
 */
function get_staff_advance_requests($staff_id)
{
    $CI = &get_instance();

    $CI->db->where('staff_id', $staff_id);
    $CI->db->order_by('request_date', 'DESC');

    return $CI->db->get(db_prefix() . 'staff_advance_salary')->result_array();
}

/**
 * Check if advance amount is within limits
 * @param float $amount
 * @param float $current_salary
 * @return bool
 */
function is_advance_amount_valid($amount, $current_salary)
{
    $CI = &get_instance();

    // Get max advance percentage from settings
    $max_percentage = get_option('salary_max_advance_percentage', 50);

    $max_amount = ($current_salary * $max_percentage) / 100;

    return $amount <= $max_amount;
}

/**
 * Get salary settings
 * @return array
 */
function get_salary_settings()
{
    $CI = &get_instance();

    $settings = [];
    $settings['max_advance_percentage'] = get_option('salary_max_advance_percentage', 50);
    $settings['advance_approval_required'] = get_option('salary_advance_approval_required', 1);
    $settings['salary_currency'] = get_option('salary_currency', 1);
    $settings['salary_decimal_places'] = get_option('salary_decimal_places', 2);

    return $settings;
}

/**
 * Update salary settings
 * @param array $settings
 * @return bool
 */
function update_salary_settings($settings)
{
    $CI = &get_instance();

    foreach ($settings as $key => $value) {
        update_option('salary_' . $key, $value);
    }

    return true;
}

/**
 * Format salary amount with currency
 * @param float $amount
 * @param object $currency
 * @return string
 */
function format_salary_amount($amount, $currency = null)
{
    if (!$currency) {
        $currency = get_base_currency_fallback();
    }

    $formatted = number_format($amount, 2, $currency->decimal_separator, $currency->thousand_separator);

    if ($currency->placement == 'before') {
        return $currency->symbol . $formatted;
    } else {
        return $formatted . $currency->symbol;
    }
}

/**
 * Get salary module permissions
 * @return array
 */
function get_salary_permissions()
{
    return [
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
}