<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    's.staffid',
    'CONCAT(s.firstname, " ", s.lastname) as full_name',
    's.email',
    's.initial_salary',
    's.current_salary',
    'COALESCE(SUM(sas.amount), 0) as total_advance_amount',
    's.salary_effective_date'
];

$sIndexColumn = 's.staffid';
$sTable = db_prefix() . 'staff s';

$join = [
    'LEFT JOIN ' . db_prefix() . 'staff_salary ss ON s.staffid = ss.staff_id AND ss.status = "active"',
    'LEFT JOIN ' . db_prefix() . 'staff_advance_salary sas ON s.staffid = sas.staff_id AND sas.status = "approved" AND MONTH(sas.approved_date) = ' . date('m') . ' AND YEAR(sas.approved_date) = ' . date('Y')
];

$where = [];
array_push($where, 'AND s.active=1');

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    's.staffid',
    's.firstname',
    's.lastname'
], 'GROUP BY s.staffid');

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Staff name with link
    $row[] = '<a href="' . admin_url('salary/staff_salary/' . $aRow['staffid']) . '">' . $aRow['full_name'] . '</a>';

    // Email
    $row[] = $aRow['email'];

    // Initial salary
    $row[] = app_format_money($aRow['initial_salary'], get_base_currency_fallback());

    // Current salary
    $row[] = app_format_money($aRow['current_salary'], get_base_currency_fallback());

    // Advance salary
    $row[] = app_format_money($aRow['total_advance_amount'], get_base_currency_fallback());

    // Effective date
    $row[] = _d($aRow['salary_effective_date']);

    // Options
    $options = '';
    if (has_permission('salary', '', 'edit')) {
        $options .= '<a href="' . admin_url('salary/staff_salary/' . $aRow['staffid']) . '" class="btn btn-default btn-icon"><i class="fa fa-pencil"></i></a>';
    }
    $row[] = $options;

    $output['aaData'][] = $row;
}