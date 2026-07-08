<?php

/**
 * Salary Module Activation Script
 * Run this file directly to activate the salary module
 */

// Include CodeIgniter
require_once '../../index.php';

$CI = &get_instance();

echo "Activating Salary Module...\n";

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
    echo "✓ Salary module added to modules table\n";
} else {
    // Update module to active
    $CI->db->where('module_name', 'salary');
    $CI->db->update(db_prefix() . 'modules', [
        'active' => 1,
        'installed_version' => '1.0.0'
    ]);
    echo "✓ Salary module updated in modules table\n";
}

// Clear cache
if (method_exists($CI, 'app_modules')) {
    $CI->app_modules->clear_cache();
    echo "✓ Module cache cleared\n";
}

echo "✓ Salary module activated successfully!\n";
echo "You can now access the salary module from the sidebar menu.\n";