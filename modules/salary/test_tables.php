<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Test file to check database tables
 */

$CI = &get_instance();

echo '<h2>Database Tables Test</h2>';

// Check if staff table has salary columns
echo '<h3>1. Checking staff table...</h3>';
$CI->db->select('COLUMN_NAME');
$CI->db->from('INFORMATION_SCHEMA.COLUMNS');
$CI->db->where('TABLE_SCHEMA', $CI->db->database);
$CI->db->where('TABLE_NAME', db_prefix() . 'staff');
$CI->db->where_in('COLUMN_NAME', ['initial_salary', 'current_salary', 'salary_effective_date']);

$columns = $CI->db->get()->result_array();
$salary_columns = array_column($columns, 'COLUMN_NAME');

echo '<p>Salary columns found in staff table: ' . implode(', ', $salary_columns) . '</p>';

if (count($salary_columns) < 3) {
    echo '<p style="color: red;">❌ Missing salary columns in staff table!</p>';
    echo '<p>Please run the salary module installation script.</p>';
} else {
    echo '<p style="color: green;">✅ All salary columns found in staff table</p>';
}

// Check if staff_salary table exists
echo '<h3>2. Checking staff_salary table...</h3>';
$CI->db->select('TABLE_NAME');
$CI->db->from('INFORMATION_SCHEMA.TABLES');
$CI->db->where('TABLE_SCHEMA', $CI->db->database);
$CI->db->where('TABLE_NAME', db_prefix() . 'staff_salary');

$table = $CI->db->get()->row();

if ($table) {
    echo '<p style="color: green;">✅ staff_salary table exists</p>';

    // Check table structure
    $CI->db->select('COLUMN_NAME, DATA_TYPE');
    $CI->db->from('INFORMATION_SCHEMA.COLUMNS');
    $CI->db->where('TABLE_SCHEMA', $CI->db->database);
    $CI->db->where('TABLE_NAME', db_prefix() . 'staff_salary');

    $columns = $CI->db->get()->result_array();
    echo '<p>Columns: ';
    foreach ($columns as $col) {
        echo $col['COLUMN_NAME'] . ' (' . $col['DATA_TYPE'] . '), ';
    }
    echo '</p>';
} else {
    echo '<p style="color: red;">❌ staff_salary table does not exist!</p>';
}

// Check if staff_advance_salary table exists
echo '<h3>3. Checking staff_advance_salary table...</h3>';
$CI->db->select('TABLE_NAME');
$CI->db->from('INFORMATION_SCHEMA.TABLES');
$CI->db->where('TABLE_SCHEMA', $CI->db->database);
$CI->db->where('TABLE_NAME', db_prefix() . 'staff_advance_salary');

$table = $CI->db->get()->row();

if ($table) {
    echo '<p style="color: green;">✅ staff_advance_salary table exists</p>';

    // Check table structure
    $CI->db->select('COLUMN_NAME, DATA_TYPE');
    $CI->db->from('INFORMATION_SCHEMA.COLUMNS');
    $CI->db->where('TABLE_SCHEMA', $CI->db->database);
    $CI->db->where('TABLE_NAME', db_prefix() . 'staff_advance_salary');

    $columns = $CI->db->get()->result_array();
    echo '<p>Columns: ';
    foreach ($columns as $col) {
        echo $col['COLUMN_NAME'] . ' (' . $col['DATA_TYPE'] . '), ';
    }
    echo '</p>';
} else {
    echo '<p style="color: red;">❌ staff_advance_salary table does not exist!</p>';
}

// Test a simple query
echo '<h3>4. Testing simple query...</h3>';
try {
    $CI->db->select('staffid, firstname, lastname, initial_salary, current_salary');
    $CI->db->from(db_prefix() . 'staff');
    $CI->db->where('active', 1);
    $CI->db->limit(5);

    $result = $CI->db->get()->result_array();
    echo '<p style="color: green;">✅ Query successful. Found ' . count($result) . ' staff members</p>';

    if (count($result) > 0) {
        echo '<p>Sample data:</p>';
        echo '<ul>';
        foreach ($result as $staff) {
            echo '<li>' . $staff['firstname'] . ' ' . $staff['lastname'] . ' - Initial: ' . ($staff['initial_salary'] ?: 'Not set') . '</li>';
        }
        echo '</ul>';
    }
} catch (Exception $e) {
    echo '<p style="color: red;">❌ Query failed: ' . $e->getMessage() . '</p>';
}

echo '<h3>5. Recommendations:</h3>';
echo '<ul>';
echo '<li>If tables are missing, run: <a href="' . base_url('modules/salary/simple_activate.php') . '">Activation Script</a></li>';
echo '<li>If columns are missing, check the install.php file</li>';
echo '<li>Make sure the database user has proper permissions</li>';
echo '</ul>';