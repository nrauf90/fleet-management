<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Activate Salary Module
 */
function activate_salary_module()
{
    $CI = &get_instance();

    // Check if module already exists
    $CI->db->where('module_name', 'salary');
    $module = $CI->db->get(db_prefix() . 'modules')->row();

    if (!$module) {
        // Insert module into modules table
        $CI->db->insert(db_prefix() . 'modules', [
            'module_name' => 'salary',
            'installed_version' => '1.0.0',
            'active' => 1,
            'date_installed' => date('Y-m-d H:i:s')
        ]);
    } else {
        // Update module to active
        $CI->db->where('module_name', 'salary');
        $CI->db->update(db_prefix() . 'modules', [
            'active' => 1,
            'installed_version' => '1.0.0'
        ]);
    }

    // Clear cache
    $CI->app_modules->clear_cache();

    return true;
}

// Run activation
activate_salary_module();