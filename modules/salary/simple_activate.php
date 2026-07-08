<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Simple Salary Module Activation
 * This script avoids problematic cache clearing methods
 */

// Check if user is admin
if (!is_admin()) {
    access_denied('Module Activation');
}

$CI = &get_instance();

echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">';
echo '<h2 style="color: #333;">Salary Module Activation</h2>';

try {
    // Step 1: Register module in database
    echo '<h3>Step 1: Registering module...</h3>';

    $CI->db->where('module_name', 'salary');
    $module = $CI->db->get(db_prefix() . 'modules')->row();

    if (!$module) {
        $CI->db->insert(db_prefix() . 'modules', [
            'module_name' => 'salary',
            'installed_version' => '1.0.0',
            'active' => 1,
            'date_installed' => date('Y-m-d H:i:s')
        ]);
        echo '<p style="color: green;">✓ Salary module registered</p>';
    } else {
        $CI->db->where('module_name', 'salary');
        $CI->db->update(db_prefix() . 'modules', [
            'active' => 1,
            'installed_version' => '1.0.0'
        ]);
        echo '<p style="color: green;">✓ Salary module updated</p>';
    }

    // Step 2: Install database tables
    echo '<h3>Step 2: Installing database tables...</h3>';

    if (file_exists(__DIR__ . '/install.php')) {
        require_once(__DIR__ . '/install.php');
        echo '<p style="color: green;">✓ Database tables installed</p>';
    } else {
        echo '<p style="color: red;">❌ Install file not found</p>';
    }

    // Step 3: Register permissions
    echo '<h3>Step 3: Setting up permissions...</h3>';

    $capabilities = [
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('salary', ['capabilities' => $capabilities], _l('salary'));
    echo '<p style="color: green;">✓ Permissions registered</p>';

    // Step 4: Set default options
    echo '<h3>Step 4: Setting default options...</h3>';

    $default_options = [
        'salary_max_advance_percentage' => 50,
        'salary_advance_approval_required' => 1,
        'salary_currency' => 1,
        'salary_decimal_places' => 2
    ];

    foreach ($default_options as $option => $value) {
        if (get_option($option) === false) {
            add_option($option, $value);
        }
    }
    echo '<p style="color: green;">✓ Default options set</p>';

    // Step 5: Simple cache clearing
    echo '<h3>Step 5: Clearing cache...</h3>';

    // Clear cache files manually
    $cache_path = FCPATH . 'application/cache/';
    if (is_dir($cache_path)) {
        $files = glob($cache_path . '*');
        foreach ($files as $file) {
            if (is_file($file) && basename($file) !== 'index.html') {
                @unlink($file);
            }
        }
        echo '<p style="color: green;">✓ Cache cleared</p>';
    } else {
        echo '<p style="color: orange;">⚠ Cache directory not found</p>';
    }

    echo '<h3 style="color: green;">✅ Salary Module Activated Successfully!</h3>';
    echo '<p>The salary module has been activated. Please refresh your browser to see the changes.</p>';

    echo '<p><strong>Module URLs:</strong></p>';
    echo '<ul>';
    echo '<li><a href="' . admin_url('salary') . '">Dashboard</a></li>';
    echo '<li><a href="' . admin_url('salary/staff') . '">Staff Salary</a></li>';
    echo '<li><a href="' . admin_url('salary/advance') . '">Advance Salary</a></li>';
    echo '<li><a href="' . admin_url('salary/reports') . '">Reports</a></li>';
    echo '<li><a href="' . admin_url('salary/settings') . '">Settings</a></li>';
    echo '</ul>';

    echo '<p><strong>Next Steps:</strong></p>';
    echo '<ol>';
    echo '<li>Refresh your browser</li>';
    echo '<li>Check the sidebar menu for "Salary"</li>';
    echo '<li>Assign salary permissions to staff roles</li>';
    echo '<li>Start using the salary module</li>';
    echo '</ol>';

    echo '<p><a href="' . admin_url() . '" style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;">Go to Dashboard</a></p>';

} catch (Exception $e) {
    echo '<h3 style="color: red;">❌ Activation Failed</h3>';
    echo '<p style="color: red;">Error: ' . $e->getMessage() . '</p>';
    echo '<p>Please check the error logs and try again.</p>';
}

echo '</div>';