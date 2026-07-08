<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'sas.id',
    'CONCAT(s.firstname, " ", s.lastname) as staff_name',
    'sas.amount',
    'sas.request_date',
    'sas.status',
    'sas.reason',
    'CONCAT(approver.firstname, " ", approver.lastname) as approver_name',
    'sas.approved_date'
];

$sIndexColumn = 'sas.id';
$sTable = db_prefix() . 'staff_advance_salary sas';

$join = [
    'LEFT JOIN ' . db_prefix() . 'staff s ON s.staffid = sas.staff_id',
    'LEFT JOIN ' . db_prefix() . 'staff approver ON approver.staffid = sas.approved_by'
];

$where = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'sas.id',
    'sas.staff_id',
    'sas.status'
]);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Staff name
    $row[] = $aRow['staff_name'];

    // Amount
    $row[] = app_format_money($aRow['amount'], get_base_currency_fallback());

    // Request date
    $row[] = _d($aRow['request_date']);

    // Status with badge
    $status_class = '';
    switch ($aRow['status']) {
        case 'pending':
            $status_class = 'label-warning';
            break;
        case 'approved':
            $status_class = 'label-success';
            break;
        case 'rejected':
            $status_class = 'label-danger';
            break;
        case 'paid':
            $status_class = 'label-info';
            break;
    }
    $row[] = '<span class="label ' . $status_class . '">' . _l('advance_status_' . $aRow['status']) . '</span>';

    // Reason
    $row[] = $aRow['reason'];

    // Approver
    $row[] = $aRow['approver_name'] ?: '-';

    // Approved date
    $row[] = $aRow['approved_date'] ? _d($aRow['approved_date']) : '-';

    // Options
    $options = '';
    if (has_permission('salary', '', 'edit')) {
        $options .= '<a href="' . admin_url('salary/edit_advance/' . $aRow['id']) . '" class="btn btn-default btn-icon"><i class="fa fa-pencil"></i></a>';

        if ($aRow['status'] == 'pending') {
            $options .= '<a href="' . admin_url('salary/approve_advance/' . $aRow['id'] . '/approved') . '" class="btn btn-success btn-icon" onclick="return confirm(\'' . _l('confirm_action_prompt') . '\')"><i class="fa fa-check"></i></a>';
            $options .= '<a href="' . admin_url('salary/approve_advance/' . $aRow['id'] . '/rejected') . '" class="btn btn-danger btn-icon" onclick="return confirm(\'' . _l('confirm_action_prompt') . '\')"><i class="fa fa-times"></i></a>';
        }
    }

    if (has_permission('salary', '', 'delete')) {
        $options .= '<a href="' . admin_url('salary/delete_advance/' . $aRow['id']) . '" class="btn btn-danger btn-icon _delete" onclick="return confirm(\'' . _l('confirm_action_prompt') . '\')"><i class="fa fa-remove"></i></a>';
    }

    $row[] = $options;

    $output['aaData'][] = $row;
}