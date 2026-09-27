<?php

defined('BASEPATH') or exit('No direct script access allowed');

$lang['accounts'] = 'Accounts';
$lang['accounts_dashboard'] = 'Dashboard';
$lang['accounts_transactions'] = 'Transactions';
$lang['accounts_settings'] = 'Accounts Settings';
$lang['accounts_permission_settings'] = 'Settings (Opening Balance)';

$lang['accounts_current_balance'] = 'Current Balance';
$lang['accounts_opening_balance'] = 'Opening Balance';
$lang['accounts_opening_balance_cash'] = 'Opening Balance — Cash';
$lang['accounts_opening_balance_bank'] = 'Opening Balance — Bank';
$lang['accounts_opening_balance_date'] = 'Opening Balance Date';
$lang['accounts_cash'] = 'Cash';
$lang['accounts_bank'] = 'Bank';
$lang['accounts_account'] = 'Account';
$lang['accounts_total_credits'] = 'Total Credits';
$lang['accounts_total_debits'] = 'Total Debits';
$lang['accounts_recent_transactions'] = 'Recent Transactions';
$lang['accounts_view_all'] = 'View All';
$lang['accounts_no_transactions'] = 'No transactions yet.';

$lang['accounts_add_transaction'] = 'Add Transaction';
$lang['accounts_edit_transaction'] = 'Edit Transaction';
$lang['accounts_transaction'] = 'Transaction';
$lang['accounts_date'] = 'Date';
$lang['accounts_type'] = 'Type';
$lang['accounts_amount'] = 'Amount';
$lang['accounts_description'] = 'Description';
$lang['accounts_source'] = 'Source';
$lang['accounts_reference'] = 'Reference';
$lang['accounts_credit'] = 'Credit';
$lang['accounts_debit'] = 'Debit';
$lang['accounts_manual_help'] = 'Use credit for money received (cash in hand / deposit). Use debit for money paid out.';

$lang['accounts_source_manual'] = 'Manual';
$lang['accounts_source_payment'] = 'Invoice Payment';
$lang['accounts_source_expense'] = 'Expense';
$lang['accounts_source_logbook'] = 'Booking Hand Cash';
$lang['accounts_logbook_hand_cash'] = 'In-hand cash to driver';
$lang['accounts_logbook_rented_cash'] = 'Rented vehicle cash';
$lang['accounts_source_opening'] = 'Opening Balance';

$lang['accounts_opening_balance_required'] = 'Opening balance is not set yet. Configure it before recording or syncing transactions.';
$lang['accounts_go_to_settings'] = 'Go to Settings';
$lang['accounts_opening_balance_help'] = 'Enter the cash and bank balances you actually hold on the opening balance date — it defaults to today. Payments (credit), expenses (debit), and manual transactions on or after that date update the running balances; earlier transactions are ignored.';

$lang['accounts_transaction_add_failed'] = 'Could not add transaction. Check amount and type.';
$lang['accounts_transaction_update_failed'] = 'Could not update transaction. Only manual transactions can be edited.';
$lang['accounts_transaction_delete_failed'] = 'Could not delete transaction. Only manual transactions can be deleted.';

$lang['accounts_expense_payee'] = 'Staff / Person';
$lang['accounts_expense_payee_name'] = 'Name';
$lang['accounts_new_expense_payee'] = 'New Name';

$lang['accounts_daily_balances'] = 'Daily Balances';
$lang['accounts_closing_balance'] = 'Closing Balance';
$lang['accounts_beginning_balance'] = 'Beginning Balance';
$lang['accounts_balance'] = 'Balance';
$lang['accounts_all_accounts'] = 'All Accounts';
$lang['accounts_apply_filters'] = 'Apply Filters';
$lang['accounts_statement'] = 'Statement';
$lang['accounts_download_statement'] = 'Download Statement';
$lang['accounts_statement_period'] = 'Statement period: %s to %s';
$lang['accounts_statement_invalid_range'] = 'Please choose a valid from and to date for the statement.';
