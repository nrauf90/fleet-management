<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Salary Management
Description: Salary Management module for staff salary handling
Version: 1.0.0
Requires at least: 2.3.*
Author: FMS System
*/

define('SALARY_MODULE_NAME', 'salary');

define('SALARY_MODULE_UPLOAD_FOLDER', module_dir_path(SALARY_MODULE_NAME, 'uploads'));

hooks()->add_action('admin_init', 'salary_permissions');
hooks()->add_action('admin_init', 'salary_module_init_menu_items');

/**
 * Register activation module hook
 */
register_activation_hook(SALARY_MODULE_NAME, 'salary_module_activation_hook');

function salary_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    require_once(__DIR__ . '/activate.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(SALARY_MODULE_NAME, [SALARY_MODULE_NAME]);

$CI = &get_instance();
$CI->load->helper(SALARY_MODULE_NAME . '/salary');

/**
 * Init salary module menu items in setup in admin_init hook
 * @return null
 */
function salary_module_init_menu_items()
{
    $CI = &get_instance();

    // Add salary menu to sidebar
    $CI->app_menu->add_sidebar_menu_item('salary', [
        'collapse' => true,
        'name' => _l('salary'),
        'position' => 35,
        'icon' => 'fa fa-money-bill',
        'badge' => [],
    ]);

    // Add dashboard submenu
    $CI->app_menu->add_sidebar_children_item('salary', [
        'slug' => 'salary_dashboard',
        'name' => _l('dashboard'),
        'href' => admin_url('salary'),
        'position' => 1,
        'badge' => [],
    ]);

    // Add staff salary submenu
    $CI->app_menu->add_sidebar_children_item('salary', [
        'slug' => 'salary_staff',
        'name' => _l('staff_salary'),
        'href' => admin_url('salary/staff'),
        'position' => 5,
        'badge' => [],
    ]);

    // Add advance salary submenu
    $CI->app_menu->add_sidebar_children_item('salary', [
        'slug' => 'salary_advance',
        'name' => _l('advance_salary'),
        'href' => admin_url('salary/advance'),
        'position' => 10,
        'badge' => [],
    ]);

    // Add reports submenu
    $CI->app_menu->add_sidebar_children_item('salary', [
        'slug' => 'salary_reports',
        'name' => _l('salary_reports'),
        'href' => admin_url('salary/reports'),
        'position' => 15,
        'badge' => [],
    ]);

    // Add settings submenu (admin only)
    if (is_admin()) {
        $CI->app_menu->add_sidebar_children_item('salary', [
            'slug' => 'salary_settings',
            'name' => _l('settings'),
            'href' => admin_url('salary/settings'),
            'position' => 20,
            'badge' => [],
        ]);
    }
}

/**
 * Register activation module hook
 */
function salary_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('salary', $capabilities, _l('salary'));
}

function salary_add_head_components()
{
    $CI = &get_instance();
    $viewuri = $CI->uri->uri_string();

    if (strpos($viewuri, 'salary') !== false) {
        // Load CSS using alternative method
        if (function_exists('add_css')) {
            add_css([
                'modules/salary/assets/css/salary.css',
            ]);
        } else {
            // Alternative CSS loading method
            $CI->load->helper('url');
            $css_url = base_url('modules/salary/assets/css/salary.css');
            echo '<link rel="stylesheet" type="text/css" href="' . $css_url . '">';
        }
    }
}

function salary_add_footer_components()
{
    $CI = &get_instance();
    $viewuri = $CI->uri->uri_string();

    if (strpos($viewuri, 'salary') !== false) {
        // Load JS using alternative method
        if (function_exists('add_js')) {
            add_js([
                'modules/salary/assets/js/salary.js',
            ]);
        } else {
            // Alternative JS loading method
            $CI->load->helper('url');
            $js_url = base_url('modules/salary/assets/js/salary.js');
            echo '<script type="text/javascript" src="' . $js_url . '"></script>';
        }
    }
}