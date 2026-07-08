<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    's.staffid',
    'CONCAT(s.firstname, " ", s.lastname) as full_name',
    's.email',
    's.initial_salary',
    's.current_salary',
    'COALESCE(ss.advance_salary, 0) as advance_salary',
    's.salary_effective_date'
];

$sIndexColumn = 's.staffid';
$sTable = db_prefix() . 'staff s';

$join = [
    'LEFT JOIN ' . db_prefix() . 'staff_salary ss ON s.staffid = ss.staff_id AND ss.status = "active"'
];

$where = ['s.active = 1'];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    's.staffid',
    's.firstname',
    's.lastname'
]);

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
    $row[] = app_format_money($aRow['advance_salary'], get_base_currency_fallback());

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