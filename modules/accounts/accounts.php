<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Accounts
Description: Single cash ledger with opening balance, manual credit/debit, and auto-sync from invoice payments and expenses
Version: 1.0.0
Requires at least: 2.3.*
Author: FMS System
*/

define('ACCOUNTS_MODULE_NAME', 'accounts');
define('ACCOUNTS_REVISION', 103);

hooks()->add_action('admin_init', 'accounts_permissions');
hooks()->add_action('admin_init', 'accounts_module_init_menu_items');
hooks()->add_action('admin_init', 'accounts_run_db_upgrades');
hooks()->add_action('app_admin_head', 'accounts_add_head_components');
hooks()->add_action('app_admin_footer', 'accounts_add_footer_components');

// Auto-sync hooks
hooks()->add_action('after_payment_added', 'accounts_on_payment_added');
hooks()->add_action('after_payment_updated', 'accounts_on_payment_updated');
hooks()->add_action('after_payment_deleted', 'accounts_on_payment_deleted');
hooks()->add_action('after_expense_added', 'accounts_on_expense_added');
hooks()->add_action('after_recurring_expense_created', 'accounts_on_recurring_expense_created');
hooks()->add_action('expense_updated', 'accounts_on_expense_updated');
hooks()->add_action('after_expense_deleted', 'accounts_on_expense_deleted');
hooks()->add_action('after_invoice_deleted', 'accounts_on_invoice_deleted');

// Expense payee (staff or custom name)
hooks()->add_action('before_expense_form_name', 'accounts_expense_payee_field');
hooks()->add_action('app_admin_footer', 'accounts_expense_payee_modal');
hooks()->add_filter('before_expense_added', 'accounts_prepare_expense_payee');
hooks()->add_filter('before_expense_updated', 'accounts_prepare_expense_payee_update');

hooks()->add_action('after_fleet_logbook_saved', 'accounts_on_fleet_logbook_saved');
hooks()->add_action('after_fleet_logbook_deleted', 'accounts_on_fleet_logbook_deleted');

register_activation_hook(ACCOUNTS_MODULE_NAME, 'accounts_module_activation_hook');

register_language_files(ACCOUNTS_MODULE_NAME, [ACCOUNTS_MODULE_NAME]);

$CI = &get_instance();
$CI->load->helper(ACCOUNTS_MODULE_NAME . '/accounts');

/**
 * Module activation
 */
function accounts_module_activation_hook()
{
    $CI = &get_instance();
    require_once __DIR__ . '/install.php';
}

/**
 * Idempotent DB upgrades on admin_init
 */
function accounts_run_db_upgrades()
{
    require_once __DIR__ . '/install.php';
}

/**
 * Staff permissions
 */
function accounts_permissions()
{
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'     => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create'   => _l('permission_create'),
        'edit'     => _l('permission_edit'),
        'delete'   => _l('permission_delete'),
        'settings' => _l('accounts_permission_settings'),
    ];

    register_staff_capabilities('accounts', $capabilities, _l('accounts'));
}

/**
 * Sidebar menu
 */
function accounts_module_init_menu_items()
{
    $CI = &get_instance();

    if (!has_permission('accounts', '', 'view') && !is_admin()) {
        return;
    }

    $CI->app_menu->add_sidebar_menu_item('accounts', [
        'collapse' => true,
        'name'     => _l('accounts'),
        'position' => 36,
        'icon'     => 'fa fa-university',
        'badge'    => [],
    ]);

    $CI->app_menu->add_sidebar_children_item('accounts', [
        'slug'     => 'accounts_dashboard',
        'name'     => _l('accounts_dashboard'),
        'href'     => admin_url('accounts'),
        'position' => 1,
        'badge'    => [],
    ]);

    $CI->app_menu->add_sidebar_children_item('accounts', [
        'slug'     => 'accounts_transactions',
        'name'     => _l('accounts_transactions'),
        'href'     => admin_url('accounts/transactions'),
        'position' => 5,
        'badge'    => [],
    ]);

    $CI->app_menu->add_sidebar_children_item('accounts', [
        'slug'     => 'accounts_suppliers',
        'name'     => _l('accounts_suppliers'),
        'href'     => admin_url('accounts/suppliers'),
        'position' => 7,
        'badge'    => [],
    ]);

    if (has_permission('accounts', '', 'settings') || is_admin()) {
        $CI->app_menu->add_sidebar_children_item('accounts', [
            'slug'     => 'accounts_settings',
            'name'     => _l('settings'),
            'href'     => admin_url('accounts/settings'),
            'position' => 10,
            'badge'    => [],
        ]);
    }
}

/**
 * Admin head CSS
 */
function accounts_add_head_components()
{
    $viewuri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($viewuri, '/admin/accounts') === false) {
        return;
    }

    echo '<link href="' . module_dir_url(ACCOUNTS_MODULE_NAME, 'assets/css/accounts.css') . '?v=' . ACCOUNTS_REVISION . '" rel="stylesheet" type="text/css" />';
}

/**
 * Admin footer JS
 */
function accounts_add_footer_components()
{
    $viewuri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($viewuri, '/admin/accounts') === false) {
        return;
    }

    echo '<script src="' . module_dir_url(ACCOUNTS_MODULE_NAME, 'assets/js/accounts.js') . '?v=' . ACCOUNTS_REVISION . '"></script>';
}

/**
 * Sync hooks — thin wrappers around helper functions
 */
function accounts_on_payment_added($payment_id)
{
    accounts_sync_from_payment($payment_id);
}

function accounts_on_payment_updated($data)
{
    $payment_id = is_array($data) && isset($data['id']) ? $data['id'] : $data;
    accounts_sync_from_payment($payment_id);
}

function accounts_on_payment_deleted($data)
{
    $payment_id = is_array($data) && isset($data['paymentid']) ? $data['paymentid'] : null;
    if ($payment_id) {
        accounts_delete_synced_transaction('payment', $payment_id);
    }
}

function accounts_on_expense_added($expense_id)
{
    accounts_sync_from_expense($expense_id);
}

function accounts_on_recurring_expense_created($data)
{
    $expense_id = is_array($data) && isset($data['new_expense_id']) ? $data['new_expense_id'] : null;
    if ($expense_id) {
        accounts_sync_from_expense($expense_id);
    }
}

function accounts_on_expense_updated($data)
{
    $expense_id = is_array($data) && isset($data['id']) ? $data['id'] : $data;
    accounts_sync_from_expense($expense_id);
}

function accounts_on_expense_deleted($expense_id)
{
    accounts_delete_synced_transaction('expense', $expense_id);
}

function accounts_on_invoice_deleted($invoice_id)
{
    accounts_delete_transactions_for_invoice($invoice_id);
}

function accounts_on_fleet_logbook_saved($logbook_id)
{
    accounts_sync_from_logbook($logbook_id);
}

function accounts_on_fleet_logbook_deleted($logbook_id)
{
    accounts_delete_synced_transaction('logbook', $logbook_id);
}
