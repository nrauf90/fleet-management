<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Salary Module Activation
 * This file handles the activation of the salary module
 */

// Check if user is admin
if (!is_admin()) {
    access_denied('Module Activation');
}

$CI = &get_instance();

// Load required models
$CI->load->model('staff_model');
$CI->load->model('currencies_model');

echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">';
echo '<h2 style="color: #333;">Salary Module Activation</h2>';

try {
    // Step 1: Check if module exists in modules table
    echo '<h3>Step 1: Checking module registration...</h3>';

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
        echo '<p style="color: green;">✓ Salary module added to modules table</p>';
    } else {
        // Update module to active
        $CI->db->where('module_name', 'salary');
        $CI->db->update(db_prefix() . 'modules', [
            'active' => 1,
            'installed_version' => '1.0.0'
        ]);
        echo '<p style="color: green;">✓ Salary module updated in modules table</p>';
    }

    // Step 2: Run database installation
    echo '<h3>Step 2: Installing database tables...</h3>';

    if (file_exists(__DIR__ . '/install.php')) {
        require_once(__DIR__ . '/install.php');
        echo '<p style="color: green;">✓ Database tables installed successfully</p>';
    } else {
        echo '<p style="color: orange;">⚠ Install file not found, skipping database installation</p>';
    }

    // Step 3: Register permissions
    echo '<h3>Step 3: Registering permissions...</h3>';

    $capabilities = [
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('salary', ['capabilities' => $capabilities], _l('salary'));
    echo '<p style="color: green;">✓ Permissions registered successfully</p>';

    // Step 4: Clear cache
    echo '<h3>Step 4: Clearing cache...</h3>';

    if (function_exists('clear_application_cache')) {
        clear_application_cache();
        echo '<p style="color: green;">✓ Application cache cleared</p>';
    } else {
        // Try alternative cache clearing methods
        if (is_dir(FCPATH . 'application/cache/')) {
            $cache_files = glob(FCPATH . 'application/cache/*');
            foreach ($cache_files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            echo '<p style="color: green;">✓ Cache files cleared manually</p>';
        } else {
            echo '<p style="color: orange;">⚠ Cache clearing not available</p>';
        }
    }

    // Step 5: Set default settings
    echo '<h3>Step 5: Setting default settings...</h3>';

    $default_settings = [
        'salary_max_advance_percentage' => 50,
        'salary_advance_approval_required' => 1,
        'salary_currency' => 1,
        'salary_decimal_places' => 2
    ];

    foreach ($default_settings as $key => $value) {
        if (get_option($key) === false) {
            add_option($key, $value);
        }
    }
    echo '<p style="color: green;">✓ Default settings configured</p>';

    echo '<h3 style="color: green;">✅ Salary Module Activated Successfully!</h3>';
    echo '<p>The salary module has been activated and is now available in the sidebar menu.</p>';
    echo '<p><strong>Available URLs:</strong></p>';
    echo '<ul>';
    echo '<li><a href="' . admin_url('salary') . '">Dashboard</a></li>';
    echo '<li><a href="' . admin_url('salary/staff') . '">Staff Salary</a></li>';
    echo '<li><a href="' . admin_url('salary/advance') . '">Advance Salary</a></li>';
    echo '<li><a href="' . admin_url('salary/reports') . '">Reports</a></li>';
    echo '<li><a href="' . admin_url('salary/settings') . '">Settings</a></li>';
    echo '</ul>';

    echo '<p><a href="' . admin_url() . '" style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;">Go to Dashboard</a></p>';

} catch (Exception $e) {
    echo '<h3 style="color: red;">❌ Activation Failed</h3>';
    echo '<p style="color: red;">Error: ' . $e->getMessage() . '</p>';
    echo '<p>Please check the error logs and try again.</p>';
}

echo '</div>';