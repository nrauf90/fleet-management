<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Whether opening balance has been configured
 * @return bool
 */
function accounts_is_configured()
{
    $CI = &get_instance();
    $CI->load->model('accounts/accounts_model');
    $settings = $CI->accounts_model->get_settings();

    return $settings && (int) $settings->is_configured === 1;
}

/**
 * Resolve which account (cash|bank) a transaction belongs to from its payment mode.
 * A mode whose name contains "cash" posts to cash; any other explicit mode posts
 * to bank; no payment mode is treated as cash.
 * @param  string|int|null $paymentmode
 * @return string
 */
function accounts_resolve_account($paymentmode)
{
    $mode = trim((string) $paymentmode);
    if ($mode === '') {
        return 'cash';
    }

    $CI = &get_instance();
    $CI->db->where('id', $mode);
    $payment_mode = $CI->db->get(db_prefix() . 'payment_modes')->row();

    return ($payment_mode && stripos($payment_mode->name, 'cash') !== false) ? 'cash' : 'bank';
}

/**
 * Sync (create or update) a credit transaction from an invoice payment
 * @param  int $payment_id
 * @return bool
 */
function accounts_sync_from_payment($payment_id)
{
    if (!$payment_id || !accounts_is_configured()) {
        return false;
    }

    $CI = &get_instance();
    $CI->load->model('payments_model');
    $CI->load->model('invoices_model');
    $CI->load->model('accounts/accounts_model');

    $payment = $CI->payments_model->get($payment_id);
    if (!$payment) {
        return false;
    }

    $invoice = $CI->invoices_model->get($payment->invoiceid);
    $invoice_number = $invoice ? format_invoice_number($invoice->id) : '#' . $payment->invoiceid;
    $client_name = $invoice ? get_company_name($invoice->clientid) : '';
    $mode_name = '';
    if (!empty($payment->paymentmode)) {
        $mode_name = isset($payment->name) ? $payment->name : '';
        if ($mode_name === '' && is_numeric($payment->paymentmode)) {
            $CI->load->model('payment_modes_model');
            $mode = $CI->payment_modes_model->get($payment->paymentmode);
            $mode_name = $mode ? $mode->name : '';
        }
    }

    $parts = array_filter([
        'Invoice payment ' . $invoice_number,
        $client_name,
        $mode_name,
    ]);

    $data = [
        'transaction_type' => 'credit',
        'account'          => accounts_resolve_account($payment->paymentmode ?? null),
        'amount'           => (float) $payment->amount,
        'transaction_date' => $payment->date,
        'description'      => implode(' — ', $parts),
        'source_type'      => 'payment',
        'source_id'        => (int) $payment_id,
        'reference'        => !empty($payment->transactionid) ? (string) $payment->transactionid : '',
        'paymentmode'      => !empty($payment->paymentmode) ? $payment->paymentmode : null,
        'addedfrom'        => get_staff_user_id() ?: 0,
    ];

    return $CI->accounts_model->upsert_synced_transaction($data);
}

/**
 * Sync (create or update) a debit transaction from an expense
 * @param  int $expense_id
 * @return bool
 */
function accounts_sync_from_expense($expense_id)
{
    if (!$expense_id || !accounts_is_configured()) {
        return false;
    }

    $CI = &get_instance();
    $CI->load->model('expenses_model');
    $CI->load->model('accounts/accounts_model');

    $expense = $CI->expenses_model->get($expense_id);
    if (!$expense) {
        return false;
    }

    $label = !empty($expense->expense_name) ? $expense->expense_name : '';
    if ($label === '' && !empty($expense->category_name)) {
        $label = $expense->category_name;
    }
    if ($label === '') {
        $label = 'Expense #' . $expense_id;
    }

    $parts = array_filter([
        'Expense: ' . $label,
        !empty($expense->reference_no) ? $expense->reference_no : '',
    ]);

    // Include tax in total cash out
    $amount = (float) $expense->amount;
    if (!empty($expense->taxrate)) {
        $amount += ((float) $expense->amount * (float) $expense->taxrate) / 100;
    }
    if (!empty($expense->taxrate2)) {
        $amount += ((float) $expense->amount * (float) $expense->taxrate2) / 100;
    }

    $data = [
        'transaction_type' => 'debit',
        'account'          => accounts_resolve_account($expense->paymentmode ?? null),
        'amount'           => $amount,
        'transaction_date' => $expense->date,
        'description'      => implode(' — ', $parts),
        'source_type'      => 'expense',
        'source_id'        => (int) $expense_id,
        'reference'        => !empty($expense->reference_no) ? (string) $expense->reference_no : '',
        'paymentmode'      => !empty($expense->paymentmode) ? $expense->paymentmode : null,
        'addedfrom'        => get_staff_user_id() ?: (!empty($expense->addedfrom) ? (int) $expense->addedfrom : 0),
    ];

    return $CI->accounts_model->upsert_synced_transaction($data);
}

/**
 * Resolve cash-out amount from a fleet logbook row.
 * @param  object $logbook
 * @return array|null{type: string, amount: float}
 */
function accounts_resolve_logbook_cash($logbook)
{
    if (!$logbook) {
        return null;
    }

    $hand_cash = (float) ($logbook->hand_cash ?? 0);
    $total_cash = (float) ($logbook->total_cash ?? 0);
    $paid_cash = (float) ($logbook->paid_cash ?? 0);

    if ($hand_cash > 0) {
        return ['type' => 'hand_cash', 'amount' => $hand_cash];
    }

    if ($total_cash > 0) {
        return ['type' => 'rented_cash', 'amount' => $total_cash];
    }

    if ($paid_cash > 0) {
        return ['type' => 'paid_cash', 'amount' => $paid_cash];
    }

    return null;
}

/**
 * Sync debit transaction from fleet booking logbook (in-hand / rental cash).
 * @param  int $logbook_id
 * @return bool
 */
function accounts_sync_from_logbook($logbook_id)
{
    if (!$logbook_id || !accounts_is_configured()) {
        return false;
    }

    $CI = &get_instance();
    $CI->load->model('accounts/accounts_model');

    $CI->db->where('id', $logbook_id);
    $logbook = $CI->db->get(db_prefix() . 'fleet_logbooks')->row();
    if (!$logbook) {
        accounts_delete_synced_transaction('logbook', $logbook_id);

        return false;
    }

    $cash = accounts_resolve_logbook_cash($logbook);
    if (!$cash) {
        return accounts_delete_synced_transaction('logbook', $logbook_id);
    }

    $booking_label = '';
    if (!empty($logbook->booking_id)) {
        $CI->db->where('id', $logbook->booking_id);
        $booking = $CI->db->get(db_prefix() . 'fleet_bookings')->row();
        if ($booking && !empty($booking->number)) {
            $booking_label = $booking->number;
        }
    }

    $driver_name = !empty($logbook->driver_id) ? get_staff_full_name($logbook->driver_id) : '';
    $cash_label = $cash['type'] === 'hand_cash'
        ? _l('accounts_logbook_hand_cash')
        : _l('accounts_logbook_rented_cash');

    $parts = array_filter([
        $cash_label,
        $booking_label ? 'Booking ' . $booking_label : '',
        $driver_name,
        !empty($logbook->name) ? $logbook->name : '',
    ]);

    $data = [
        'transaction_type' => 'debit',
        'account'          => accounts_resolve_account($logbook->paymentmode ?? null),
        'amount'           => $cash['amount'],
        'transaction_date' => !empty($logbook->date) ? $logbook->date : date('Y-m-d'),
        'description'      => implode(' — ', $parts),
        'source_type'      => 'logbook',
        'source_id'        => (int) $logbook_id,
        'reference'        => $booking_label,
        'paymentmode'      => !empty($logbook->paymentmode) ? $logbook->paymentmode : null,
        'transaction_id'   => !empty($logbook->transaction_id) ? $logbook->transaction_id : null,
        'addedfrom'        => get_staff_user_id() ?: 0,
    ];

    return $CI->accounts_model->upsert_synced_transaction($data);
}

/**
 * Delete synced transaction by source
 * @param  string $source_type
 * @param  int    $source_id
 * @return bool
 */
function accounts_delete_synced_transaction($source_type, $source_id)
{
    if (!$source_type || !$source_id) {
        return false;
    }

    $CI = &get_instance();
    $CI->load->model('accounts/accounts_model');

    return $CI->accounts_model->delete_by_source($source_type, $source_id);
}

/**
 * When an invoice is deleted, remove any payment-sourced ledger rows for its payments.
 * Perfex bulk-deletes payments without firing after_payment_deleted per row.
 * @param  int $invoice_id
 * @return void
 */
function accounts_delete_transactions_for_invoice($invoice_id)
{
    if (!$invoice_id) {
        return;
    }

    $CI = &get_instance();
    $CI->load->model('accounts/accounts_model');
    $CI->accounts_model->delete_payment_transactions_for_invoice($invoice_id);
}

/**
 * Human-readable source label
 * @param  string $source_type
 * @return string
 */
function accounts_source_label($source_type)
{
    $map = [
        'manual'  => _l('accounts_source_manual'),
        'payment' => _l('accounts_source_payment'),
        'expense' => _l('accounts_source_expense'),
        'logbook' => _l('accounts_source_logbook'),
        'opening' => _l('accounts_source_opening'),
    ];

    return isset($map[$source_type]) ? $map[$source_type] : $source_type;
}

/**
 * Render the payee picker on the expense form.
 * Hooked to before_expense_form_name.
 * @param  object|null $expense
 * @return void
 */
function accounts_expense_payee_field($expense = null)
{
    $CI = &get_instance();
    $CI->load->model('accounts/accounts_model');

    $options  = $CI->accounts_model->get_expense_payee_options();
    $selected = '';
    if ($expense && !empty($expense->payee_type) && !empty($expense->payee_id)) {
        $selected = $expense->payee_type . '_' . $expense->payee_id;
    }

    // Wrapped so the footer script can move it directly above the customer field,
    // which has no hook of its own.
    echo '<div id="expense-payee-field">';
    echo render_select_with_input_group(
        'payee',
        $options,
        ['id', 'name'],
        'accounts_expense_payee',
        $selected,
        '<div class="input-group-btn"><a href="#" class="btn btn-default" onclick="new_expense_payee();return false;"><i class="fa fa-plus"></i></a></div>'
    );
    echo '</div>';
}

/**
 * Render the 'add payee' modal and its script once, at the end of the expense form.
 * Hooked to before_expense_form_template_close.
 * @return void
 */
function accounts_expense_payee_modal()
{
    // Rendered from app_admin_footer so its <form> is never nested inside the
    // expense form, which would break expense form validation.
    $viewuri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($viewuri, '/admin/expenses/expense') === false) {
        return;
    }

    $CI = &get_instance();
    $CI->load->view('accounts/expense_payee_modal');
}

/**
 * Split the namespaced payee value into its stored columns before the expense is written.
 * The expenses model inserts the post array directly, so 'payee' must not survive.
 * @param  array $data
 * @return array
 */
function accounts_prepare_expense_payee($data)
{
    if (!array_key_exists('payee', $data)) {
        return $data;
    }

    $value = trim((string) $data['payee']);
    unset($data['payee']);

    $data['payee_type'] = null;
    $data['payee_id']   = null;

    if ($value !== '' && strpos($value, '_') !== false) {
        list($type, $id) = explode('_', $value, 2);
        if (in_array($type, ['staff', 'custom'], true) && ctype_digit($id)) {
            $data['payee_type'] = $type;
            $data['payee_id']   = (int) $id;
        }
    }

    return $data;
}

/**
 * before_expense_updated passes the row id as a second argument.
 * @param  array $data
 * @param  int   $id
 * @return array
 */
function accounts_prepare_expense_payee_update($data, $id = null)
{
    return accounts_prepare_expense_payee($data);
}
