<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'accounts_settings')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "accounts_settings` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `opening_balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
      `opening_balance_date` DATE NULL,
      `currency` INT(11) NOT NULL DEFAULT 0,
      `is_configured` TINYINT(1) NOT NULL DEFAULT 0,
      `dateupdated` DATETIME NULL,
      `updated_by` INT(11) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'account_transactions')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "account_transactions` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `transaction_type` ENUM('credit','debit') NOT NULL,
      `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
      `transaction_date` DATE NOT NULL,
      `description` TEXT NULL,
      `source_type` VARCHAR(20) NOT NULL DEFAULT 'manual',
      `source_id` INT(11) NULL,
      `reference` VARCHAR(191) NULL,
      `addedfrom` INT(11) NULL,
      `datecreated` DATETIME NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `source_unique` (`source_type`, `source_id`),
      KEY `transaction_date` (`transaction_date`),
      KEY `transaction_type` (`transaction_type`),
      KEY `source_type` (`source_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Ensure settings row id=1 exists (not configured until admin saves)
$CI->db->where('id', 1);
$settings = $CI->db->get(db_prefix() . 'accounts_settings')->row();
if (!$settings) {
    $currency_id = 0;
    $CI->db->select('id');
    $CI->db->where('isdefault', 1);
    $base = $CI->db->get(db_prefix() . 'currencies')->row();
    if ($base) {
        $currency_id = (int) $base->id;
    }

    $CI->db->insert(db_prefix() . 'accounts_settings', [
        'id'                   => 1,
        'opening_balance'      => 0,
        'opening_balance_date' => null,
        'currency'             => $currency_id,
        'is_configured'        => 0,
        'dateupdated'          => date('Y-m-d H:i:s'),
        'updated_by'           => null,
    ]);
}

if (!$CI->db->field_exists('paymentmode', db_prefix() . 'account_transactions')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . "account_transactions`
        ADD COLUMN `paymentmode` VARCHAR(50) NULL DEFAULT NULL,
        ADD COLUMN `transaction_id` VARCHAR(100) NULL DEFAULT NULL;
    ");
}

// Split the single opening balance into Cash + Bank accounts
if (!$CI->db->field_exists('opening_balance_cash', db_prefix() . 'accounts_settings')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . "accounts_settings`
        ADD COLUMN `opening_balance_cash` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        ADD COLUMN `opening_balance_bank` DECIMAL(15,2) NOT NULL DEFAULT 0.00;
    ");

    // Carry the legacy single opening balance into the cash account
    $CI->db->query('UPDATE `' . db_prefix() . 'accounts_settings` SET `opening_balance_cash` = `opening_balance`');
}

// Cash/bank account is derived from the payment mode name — the stored mapping is obsolete
if ($CI->db->field_exists('cash_payment_modes', db_prefix() . 'accounts_settings')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'accounts_settings` DROP COLUMN `cash_payment_modes`');
}

if (!$CI->db->field_exists('account', db_prefix() . 'account_transactions')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . "account_transactions`
        ADD COLUMN `account` VARCHAR(10) NOT NULL DEFAULT 'cash' AFTER `transaction_type`,
        ADD KEY `account` (`account`);
    ");

    // Reclassify synced rows: an explicit non-cash payment mode posts to bank
    $__cash_modes = [];
    foreach ($CI->db->get(db_prefix() . 'payment_modes')->result() as $__mode) {
        if (stripos($__mode->name, 'cash') !== false) {
            $__cash_modes[] = (int) $__mode->id;
        }
    }
    if (!empty($__cash_modes)) {
        $CI->db->query('UPDATE `' . db_prefix() . "account_transactions`
            SET `account` = 'bank'
            WHERE `source_type` <> 'manual'
              AND `paymentmode` IS NOT NULL AND `paymentmode` <> ''
              AND `paymentmode` NOT IN ('" . implode("','", array_map('intval', $__cash_modes)) . "')");
    }
}

/**
 * Expense payee: who an expense was recorded for (a staff member or a free-form name).
 */
if (!$CI->db->table_exists(db_prefix() . 'expense_payees')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "expense_payees` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(191) NOT NULL,
        `dateadded` DATETIME NULL DEFAULT NULL,
        `addedfrom` INT(11) NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

if (!$CI->db->field_exists('payee_type', db_prefix() . 'expenses')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . "expenses`
        ADD COLUMN `payee_type` VARCHAR(10) NULL DEFAULT NULL,
        ADD COLUMN `payee_id` INT(11) NULL DEFAULT NULL;
    ");
}
