<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// Create staff_salary table
if (!$CI->db->table_exists(db_prefix() . 'staff_salary')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "staff_salary` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `staff_id` int(11) NOT NULL,
      `initial_salary` decimal(15,2) NOT NULL DEFAULT '0.00',
      `advance_salary` decimal(15,2) NOT NULL DEFAULT '0.00',
      `effective_date` date NOT NULL,
      `status` enum('active','inactive') NOT NULL DEFAULT 'active',
      `created_by` int(11) NOT NULL,
      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `staff_id` (`staff_id`),
      KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Create staff_advance_salary table
if (!$CI->db->table_exists(db_prefix() . 'staff_advance_salary')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "staff_advance_salary` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `staff_id` int(11) NOT NULL,
      `amount` decimal(15,2) NOT NULL,
      `request_date` date NOT NULL,
      `approved_date` date NULL,
      `status` enum('pending','approved','rejected','paid') NOT NULL DEFAULT 'pending',
      `reason` text NULL,
      `approved_by` int(11) NULL,
      `created_by` int(11) NOT NULL,
      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `staff_id` (`staff_id`),
      KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Create salary_settings table
if (!$CI->db->table_exists(db_prefix() . 'salary_settings')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "salary_settings` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `setting_name` varchar(100) NOT NULL,
      `setting_value` text NULL,
      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `setting_name` (`setting_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Insert default settings
$default_settings = [
    'max_advance_percentage' => '50',
    'advance_approval_required' => '1',
    'salary_currency' => 'USD',
    'salary_decimal_places' => '2'
];

foreach ($default_settings as $setting_name => $setting_value) {
    $CI->db->where('setting_name', $setting_name);
    $exists = $CI->db->get(db_prefix() . 'salary_settings')->row();

    if (!$exists) {
        $CI->db->insert(db_prefix() . 'salary_settings', [
            'setting_name' => $setting_name,
            'setting_value' => $setting_value
        ]);
    }
}

// Add salary fields to staff table if they don't exist
if (!$CI->db->field_exists('initial_salary', db_prefix() . 'staff')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'staff` ADD COLUMN `initial_salary` decimal(15,2) NOT NULL DEFAULT "0.00" AFTER `hourly_rate`');
}

if (!$CI->db->field_exists('current_salary', db_prefix() . 'staff')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'staff` ADD COLUMN `current_salary` decimal(15,2) NOT NULL DEFAULT "0.00" AFTER `initial_salary`');
}

if (!$CI->db->field_exists('salary_effective_date', db_prefix() . 'staff')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'staff` ADD COLUMN `salary_effective_date` date NULL AFTER `current_salary`');
}