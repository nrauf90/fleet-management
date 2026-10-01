<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Accounts_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get ledger settings (single row)
     * @return object|null
     */
    public function get_settings()
    {
        $this->db->where('id', 1);

        return $this->db->get(db_prefix() . 'accounts_settings')->row();
    }

    /**
     * Save opening balance / settings
     * @param  array $data
     * @return bool
     */
    public function save_settings($data)
    {
        $payload = [
            'opening_balance_cash' => (float) str_replace(',', '', (string) ($data['opening_balance_cash'] ?? 0)),
            'opening_balance_bank' => (float) str_replace(',', '', (string) ($data['opening_balance_bank'] ?? 0)),
            'opening_balance_date' => !empty($data['opening_balance_date']) ? to_sql_date($data['opening_balance_date']) : date('Y-m-d'),
            'currency'             => (int) ($data['currency'] ?? 0),
            'is_configured'        => 1,
            'dateupdated'          => date('Y-m-d H:i:s'),
            'updated_by'           => get_staff_user_id(),
        ];

        $existing = $this->get_settings();
        if ($existing) {
            $this->db->where('id', 1);
            $this->db->update(db_prefix() . 'accounts_settings', $payload);
            $success = $this->db->affected_rows() >= 0;
        } else {
            $payload['id'] = 1;
            $this->db->insert(db_prefix() . 'accounts_settings', $payload);
            $success = $this->db->insert_id() > 0;
        }

        if ($success) {
            $this->reclassify_synced_accounts();
        }

        return $success;
    }

    /**
     * Re-derive cash/bank account on synced transactions from the configured cash payment modes.
     * Manual transactions keep the account chosen by the user.
     * @return void
     */
    private function reclassify_synced_accounts()
    {
        $cash_modes = [];
        foreach ($this->db->get(db_prefix() . 'payment_modes')->result() as $mode) {
            if (stripos($mode->name, 'cash') !== false) {
                $cash_modes[] = (int) $mode->id;
            }
        }
        $in = !empty($cash_modes) ? "'" . implode("','", $cash_modes) . "'" : "''";

        $this->db->query('UPDATE `' . db_prefix() . "account_transactions`
            SET `account` = IF(`paymentmode` IS NULL OR `paymentmode` = '' OR `paymentmode` IN (" . $in . "), 'cash', 'bank')
            WHERE `source_type` NOT IN ('manual', 'supplier')");
    }

    /**
     * Balance summary, split per account (cash / bank)
     * @return array{cash: array, bank: array, is_configured: bool, currency: int, opening_balance_date: string|null}
     */
    public function get_balance_summary()
    {
        $settings = $this->get_settings();
        $configured = $settings && (int) $settings->is_configured === 1;
        $opening_date = ($settings && !empty($settings->opening_balance_date)) ? $settings->opening_balance_date : null;

        $this->db->select("
            COALESCE(SUM(CASE WHEN account = 'cash' AND transaction_type = 'credit' THEN amount ELSE 0 END), 0) as cash_credits,
            COALESCE(SUM(CASE WHEN account = 'cash' AND transaction_type = 'debit' THEN amount ELSE 0 END), 0) as cash_debits,
            COALESCE(SUM(CASE WHEN account = 'bank' AND transaction_type = 'credit' THEN amount ELSE 0 END), 0) as bank_credits,
            COALESCE(SUM(CASE WHEN account = 'bank' AND transaction_type = 'debit' THEN amount ELSE 0 END), 0) as bank_debits
        ", false);
        if ($opening_date) {
            $this->db->where('transaction_date >=', $opening_date);
        }
        $totals = $this->db->get(db_prefix() . 'account_transactions')->row();

        $cash_opening  = $settings ? (float) ($settings->opening_balance_cash ?? $settings->opening_balance) : 0;
        $bank_opening  = $settings ? (float) $settings->opening_balance_bank : 0;
        $cash_credits  = $totals ? (float) $totals->cash_credits : 0;
        $cash_debits   = $totals ? (float) $totals->cash_debits : 0;
        $bank_credits  = $totals ? (float) $totals->bank_credits : 0;
        $bank_debits   = $totals ? (float) $totals->bank_debits : 0;

        return [
            'opening_balance_date' => $settings ? $settings->opening_balance_date : null,
            'is_configured'        => $configured,
            'currency'             => $settings ? (int) $settings->currency : 0,
            'cash'                 => [
                'opening_balance' => $cash_opening,
                'total_credits'   => $cash_credits,
                'total_debits'    => $cash_debits,
                'current_balance' => $cash_opening + $cash_credits - $cash_debits,
            ],
            'bank'                 => [
                'opening_balance' => $bank_opening,
                'total_credits'   => $bank_credits,
                'total_debits'    => $bank_debits,
                'current_balance' => $bank_opening + $bank_credits - $bank_debits,
            ],
        ];
    }

    /**
     * Per-day opening / credits / debits / closing balances for one account or both combined.
     * Every day from the opening balance date (or the filter's from date) through today
     * (or the filter's to date) is returned, newest first; days with no activity carry
     * the balance forward. Transactions dated before the opening balance date are
     * excluded — the opening balances represent the actual balances on that date.
     * @param  string|null $account cash|bank|null (null = both)
     * @param  string|null $from    sql date, lower bound for listed days
     * @param  string|null $to      sql date, upper bound for listed days
     * @return array rows: date, opening, credits, debits, closing
     */
    public function get_daily_balances($account = null, $from = null, $to = null)
    {
        $settings = $this->get_settings();
        $base = $this->opening_base_for_account($settings, $account);

        $start = ($settings && !empty($settings->opening_balance_date)) ? $settings->opening_balance_date : null;
        if (!$start) {
            $this->db->select_min('transaction_date');
            if ($account) {
                $this->db->where('account', $account);
            }
            $min = $this->db->get(db_prefix() . 'account_transactions')->row();
            $start = ($min && $min->transaction_date) ? $min->transaction_date : date('Y-m-d');
        }

        $end = date('Y-m-d');
        if ($to && $to < $end) {
            $end = $to;
        }
        if ($from && $from > $start) {
            $start = $from;
        }
        if ($start > $end) {
            return [];
        }

        $this->db->select("
            transaction_date,
            COALESCE(SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END), 0) AS credits,
            COALESCE(SUM(CASE WHEN transaction_type = 'debit' THEN amount ELSE 0 END), 0) AS debits
        ", false);
        if ($account) {
            $this->db->where('account', $account);
        }
        $this->db->group_by('transaction_date');

        $per_day = [];
        foreach ($this->db->get(db_prefix() . 'account_transactions')->result() as $row) {
            $per_day[$row->transaction_date] = [
                'credits' => (float) $row->credits,
                'debits'  => (float) $row->debits,
            ];
        }

        $opening_date = ($settings && !empty($settings->opening_balance_date)) ? $settings->opening_balance_date : null;
        $balance = $base + $this->net_before($start, $account, $opening_date);

        $days = [];
        $current = strtotime($start);
        $last = strtotime($end);
        while ($current <= $last) {
            $date = date('Y-m-d', $current);
            $credits = isset($per_day[$date]) ? $per_day[$date]['credits'] : 0.0;
            $debits = isset($per_day[$date]) ? $per_day[$date]['debits'] : 0.0;
            $closing = $balance + $credits - $debits;

            $days[] = [
                'date'    => $date,
                'opening' => $balance,
                'credits' => $credits,
                'debits'  => $debits,
                'closing' => $closing,
            ];

            $balance = $closing;
            $current = strtotime('+1 day', $current);
        }

        return array_reverse($days);
    }

    /**
     * Statement data for a date range: beginning balance, transactions, totals.
     * @param  string      $from    sql date
     * @param  string      $to      sql date
     * @param  string|null $account cash|bank|alzarooni|null (null = both)
     * @return array
     */
    public function get_statement_data($from, $to, $account = null)
    {
        $alzarooni_only = $account === 'alzarooni';
        $account_filter = $alzarooni_only ? null : $account;

        $settings = $this->get_settings();
        $opening_date = ($settings && !empty($settings->opening_balance_date)) ? $settings->opening_balance_date : null;
        if ($alzarooni_only) {
            $beginning = $this->net_before($from, null, $opening_date, true);
        } else {
            $beginning = $this->opening_base_for_account($settings, $account_filter) + $this->net_before($from, $account_filter, $opening_date);
        }

        $this->db->where('transaction_date >=', $from);
        $this->db->where('transaction_date <=', $to);
        if ($alzarooni_only) {
            $this->db->where('is_alzarooni', 1);
        } elseif ($account_filter) {
            $this->db->where('account', $account_filter);
        }
        $this->db->order_by('transaction_date', 'asc');
        $this->db->order_by('id', 'asc');
        $rows = $this->db->get(db_prefix() . 'account_transactions')->result_array();

        $credits = 0.0;
        $debits = 0.0;
        foreach ($rows as $row) {
            if ($row['transaction_type'] === 'credit') {
                $credits += (float) $row['amount'];
            } else {
                $debits += (float) $row['amount'];
            }
        }

        return [
            'from'              => $from,
            'to'                => $to,
            'account'           => $account,
            'beginning_balance' => $beginning,
            'total_credits'     => $credits,
            'total_debits'      => $debits,
            'closing_balance'   => $beginning + $credits - $debits,
            'transactions'      => $rows,
        ];
    }

    /**
     * Configured opening balance for one account or both combined.
     * @param  object|null $settings
     * @param  string|null $account  cash|bank|null
     * @return float
     */
    private function opening_base_for_account($settings, $account = null)
    {
        if (!$settings) {
            return 0.0;
        }

        $cash = (float) ($settings->opening_balance_cash ?? $settings->opening_balance ?? 0);
        $bank = (float) ($settings->opening_balance_bank ?? 0);

        if ($account === 'cash') {
            return $cash;
        }
        if ($account === 'bank') {
            return $bank;
        }

        return $cash + $bank;
    }

    /**
     * Net movement (credits minus debits) strictly before a date,
     * optionally bounded below by $since (inclusive).
     * @param  string      $date          sql date
     * @param  string|null $account       cash|bank|null
     * @param  string|null $since         sql date lower bound (inclusive)
     * @param  bool        $alzarooni_only restrict to Al Zarooni transactions
     * @return float
     */
    private function net_before($date, $account = null, $since = null, $alzarooni_only = false)
    {
        $this->db->select("COALESCE(SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE -amount END), 0) AS net", false);
        $this->db->where('transaction_date <', $date);
        if ($since) {
            $this->db->where('transaction_date >=', $since);
        }
        if ($account) {
            $this->db->where('account', $account);
        }
        if ($alzarooni_only) {
            $this->db->where('is_alzarooni', 1);
        }
        $row = $this->db->get(db_prefix() . 'account_transactions')->row();

        return $row ? (float) $row->net : 0.0;
    }

    /**
     * Running cash/bank balances at the start of a date (that day's transactions excluded).
     * Pre-fills the settings form so submitting re-baselines to the actual current balances.
     * @param  string $date sql date
     * @return array{cash: float, bank: float}
     */
    public function get_balances_as_of($date)
    {
        $settings = $this->get_settings();
        $since = ($settings && !empty($settings->opening_balance_date)) ? $settings->opening_balance_date : null;

        return [
            'cash' => $this->opening_base_for_account($settings, 'cash') + $this->net_before($date, 'cash', $since),
            'bank' => $this->opening_base_for_account($settings, 'bank') + $this->net_before($date, 'bank', $since),
        ];
    }

    /**
     * Get recent transactions
     * @param  int $limit
     * @return array
     */
    public function get_recent_transactions($limit = 10)
    {
        $this->db->order_by('transaction_date', 'DESC');
        $this->db->order_by('id', 'DESC');
        $this->db->limit((int) $limit);

        return $this->db->get(db_prefix() . 'account_transactions')->result_array();
    }

    /**
     * Get single transaction
     * @param  int $id
     * @return object|null
     */
    public function get_transaction($id)
    {
        $this->db->where('id', $id);

        return $this->db->get(db_prefix() . 'account_transactions')->row();
    }

    /**
     * Add manual transaction
     * @param  array $data
     * @return int|false
     */
    public function add_transaction($data)
    {
        $row = $this->prepare_manual_payload($data);
        if ($row === false) {
            return false;
        }

        $row['source_type'] = 'manual';
        $row['source_id']   = null;
        $row['addedfrom']   = get_staff_user_id();
        $row['datecreated'] = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix() . 'account_transactions', $row);
        $insert_id = $this->db->insert_id();

        return $insert_id ?: false;
    }

    /**
     * Update manual transaction only
     * @param  int   $id
     * @param  array $data
     * @return bool
     */
    public function update_transaction($id, $data)
    {
        $existing = $this->get_transaction($id);
        if (!$existing || $existing->source_type !== 'manual') {
            return false;
        }

        $row = $this->prepare_manual_payload($data);
        if ($row === false) {
            return false;
        }

        $this->db->where('id', $id);
        $this->db->where('source_type', 'manual');
        $this->db->update(db_prefix() . 'account_transactions', $row);

        return $this->db->affected_rows() >= 0;
    }

    /**
     * Delete manual transaction only
     * @param  int $id
     * @return bool
     */
    public function delete_transaction($id)
    {
        $existing = $this->get_transaction($id);
        if (!$existing || $existing->source_type !== 'manual') {
            return false;
        }

        $this->db->where('id', $id);
        $this->db->where('source_type', 'manual');
        $this->db->delete(db_prefix() . 'account_transactions');

        return $this->db->affected_rows() > 0;
    }

    /**
     * Upsert synced payment/expense row
     * @param  array $data
     * @return bool
     */
    public function upsert_synced_transaction($data)
    {
        if (empty($data['source_type']) || empty($data['source_id'])) {
            return false;
        }

        $payload = [
            'transaction_type' => $data['transaction_type'] === 'debit' ? 'debit' : 'credit',
            'account'          => in_array(($data['account'] ?? ''), ['cash', 'bank'], true) ? $data['account'] : 'cash',
            'amount'           => abs((float) $data['amount']),
            'transaction_date' => $data['transaction_date'],
            'description'      => $data['description'] ?? '',
            'source_type'      => $data['source_type'],
            'source_id'        => (int) $data['source_id'],
            'reference'        => $data['reference'] ?? '',
            'paymentmode'      => $data['paymentmode'] ?? null,
            'transaction_id'   => $data['transaction_id'] ?? null,
            'addedfrom'        => isset($data['addedfrom']) ? (int) $data['addedfrom'] : 0,
        ];

        $this->db->where('source_type', $payload['source_type']);
        $this->db->where('source_id', $payload['source_id']);
        $existing = $this->db->get(db_prefix() . 'account_transactions')->row();

        if ($existing) {
            $this->db->where('id', $existing->id);
            $this->db->update(db_prefix() . 'account_transactions', $payload);

            return true;
        }

        $payload['datecreated'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'account_transactions', $payload);

        return $this->db->insert_id() > 0;
    }

    /**
     * Delete by source type + id
     * @param  string $source_type
     * @param  int    $source_id
     * @return bool
     */
    public function delete_by_source($source_type, $source_id)
    {
        $this->db->where('source_type', $source_type);
        $this->db->where('source_id', (int) $source_id);
        $this->db->delete(db_prefix() . 'account_transactions');

        return $this->db->affected_rows() > 0;
    }

    /**
     * Remove payment-sourced transactions belonging to a deleted invoice.
     * Looks up payment IDs that may still exist briefly, or matches description invoice number as fallback.
     * Prefer deleting by payment IDs still in DB before invoice cascade finishes — hook runs after delete.
     * So we store invoiceid on payment sync via reference pattern: look up remaining orphans by scanning
     * source_type=payment where payment no longer exists.
     * @param  int $invoice_id
     * @return void
     */
    public function delete_payment_transactions_for_invoice($invoice_id)
    {
        // After invoice delete, payment rows are gone. Clean orphans: payment source_id not in invoicepaymentrecords.
        $this->db->select('id, source_id');
        $this->db->where('source_type', 'payment');
        $rows = $this->db->get(db_prefix() . 'account_transactions')->result_array();

        if (empty($rows)) {
            return;
        }

        foreach ($rows as $row) {
            $this->db->where('id', $row['source_id']);
            $payment = $this->db->get(db_prefix() . 'invoicepaymentrecords')->row();
            if (!$payment) {
                $this->db->where('id', $row['id']);
                $this->db->delete(db_prefix() . 'account_transactions');
            }
        }
    }

    /**
     * Normalize manual transaction POST data
     * @param  array $data
     * @return array|false
     */
    private function prepare_manual_payload($data)
    {
        $type = isset($data['transaction_type']) ? $data['transaction_type'] : '';
        if (!in_array($type, ['credit', 'debit'], true)) {
            return false;
        }

        $amount = (float) str_replace(',', '', (string) ($data['amount'] ?? 0));
        if ($amount <= 0) {
            return false;
        }

        $date = !empty($data['transaction_date']) ? to_sql_date($data['transaction_date']) : date('Y-m-d');

        $account = $data['account'] ?? '';
        $is_alzarooni = $account === 'alzarooni';
        if ($is_alzarooni) {
            $account = in_array(($data['alzarooni_account'] ?? ''), ['cash', 'bank'], true)
                ? $data['alzarooni_account']
                : 'cash';
        }
        if (!in_array($account, ['cash', 'bank'], true)) {
            $account = 'cash';
        }

        return [
            'transaction_type' => $type,
            'account'          => $account,
            'is_alzarooni'     => $is_alzarooni ? 1 : 0,
            'amount'           => $amount,
            'transaction_date' => $date,
            'description'      => isset($data['description']) ? $data['description'] : '',
            'reference'        => isset($data['reference']) ? $data['reference'] : '',
        ];
    }

    /**
     * All selectable expense payees: active staff plus custom names.
     * Values are namespaced so a staff id and a custom id never collide.
     * @return array
     */
    public function get_expense_payee_options()
    {
        $options = [];

        $this->db->where('active', 1);
        $this->db->order_by('firstname', 'asc');
        foreach ($this->db->get(db_prefix() . 'staff')->result() as $staff) {
            $options[] = [
                'id'   => 'staff_' . $staff->staffid,
                'name' => trim($staff->firstname . ' ' . $staff->lastname),
            ];
        }

        $this->db->order_by('name', 'asc');
        foreach ($this->db->get(db_prefix() . 'expense_payees')->result() as $payee) {
            $options[] = [
                'id'   => 'custom_' . $payee->id,
                'name' => $payee->name,
            ];
        }

        return $options;
    }

    /**
     * Add a custom payee name, reusing the row when the name already exists.
     * @param  string $name
     * @return array|false  ['id' => 'custom_3', 'name' => 'Ali']
     */
    public function add_expense_payee($name)
    {
        $name = trim($name);
        if ($name === '') {
            return false;
        }

        $existing = $this->db->get_where(db_prefix() . 'expense_payees', ['name' => $name])->row();
        if ($existing) {
            return ['id' => 'custom_' . $existing->id, 'name' => $existing->name];
        }

        $this->db->insert(db_prefix() . 'expense_payees', [
            'name'      => $name,
            'dateadded' => date('Y-m-d H:i:s'),
            'addedfrom' => get_staff_user_id() ?: null,
        ]);

        $insert_id = $this->db->insert_id();

        return $insert_id ? ['id' => 'custom_' . $insert_id, 'name' => $name] : false;
    }

    /**
     * Display name for an expense's stored payee.
     * @param  string|null $type staff|custom
     * @param  int|null    $id
     * @return string
     */
    public function get_expense_payee_name($type, $id)
    {
        if (!$type || !$id) {
            return '';
        }

        if ($type === 'staff') {
            $row = $this->db->get_where(db_prefix() . 'staff', ['staffid' => (int) $id])->row();

            return $row ? trim($row->firstname . ' ' . $row->lastname) : '';
        }

        $row = $this->db->get_where(db_prefix() . 'expense_payees', ['id' => (int) $id])->row();

        return $row ? $row->name : '';
    }

    /**
     * Active suppliers with aggregated credit/debit totals.
     * @return array
     */
    public function get_suppliers_with_totals()
    {
        $this->db->select('s.id, s.name, s.dateadded,
            COALESCE(SUM(CASE WHEN t.transaction_type = "credit" THEN t.amount ELSE 0 END), 0) AS credits,
            COALESCE(SUM(CASE WHEN t.transaction_type = "debit" THEN t.amount ELSE 0 END), 0) AS debits', false);
        $this->db->from(db_prefix() . 'accounts_suppliers s');
        $this->db->join(db_prefix() . 'accounts_supplier_transactions t', 't.supplier_id = s.id', 'left');
        $this->db->where('s.deleted_at IS NULL', null, false);
        $this->db->group_by('s.id');
        $this->db->order_by('s.name', 'asc');

        return $this->db->get()->result_array();
    }

    /**
     * Get single supplier (includes soft-deleted rows for audit access)
     * @param  int $id
     * @return object|null
     */
    public function get_supplier($id)
    {
        $this->db->where('id', (int) $id);

        return $this->db->get(db_prefix() . 'accounts_suppliers')->row();
    }

    /**
     * Add supplier by name
     * @param  string $name
     * @return int|false
     */
    public function add_supplier($name)
    {
        $name = trim((string) $name);
        if ($name === '') {
            return false;
        }

        $this->db->insert(db_prefix() . 'accounts_suppliers', [
            'name'      => $name,
            'dateadded' => date('Y-m-d H:i:s'),
            'addedfrom' => get_staff_user_id() ?: null,
        ]);

        return $this->db->insert_id() ?: false;
    }

    /**
     * Rename supplier
     * @param  int    $id
     * @param  string $name
     * @return bool
     */
    public function update_supplier($id, $name)
    {
        $name = trim((string) $name);
        if ($name === '') {
            return false;
        }

        $this->db->where('id', (int) $id);
        $this->db->update(db_prefix() . 'accounts_suppliers', ['name' => $name]);

        return $this->db->affected_rows() >= 0;
    }

    /**
     * Soft delete: hides the supplier and blocks new transactions while
     * keeping its history and synced ledger rows for audit.
     * @param  int $id
     * @return bool
     */
    public function delete_supplier($id)
    {
        $this->db->where('id', (int) $id);
        $this->db->where('deleted_at IS NULL', null, false);
        $this->db->update(db_prefix() . 'accounts_suppliers', ['deleted_at' => date('Y-m-d H:i:s')]);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Credit / debit totals for one supplier.
     * @param  int $supplier_id
     * @return array{credits: float, debits: float, balance: float}
     */
    public function get_supplier_totals($supplier_id)
    {
        $this->db->select("
            COALESCE(SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE 0 END), 0) AS credits,
            COALESCE(SUM(CASE WHEN transaction_type = 'debit' THEN amount ELSE 0 END), 0) AS debits
        ", false);
        $this->db->where('supplier_id', (int) $supplier_id);
        $row = $this->db->get(db_prefix() . 'accounts_supplier_transactions')->row();

        $credits = $row ? (float) $row->credits : 0.0;
        $debits = $row ? (float) $row->debits : 0.0;

        return [
            'credits' => $credits,
            'debits'  => $debits,
            'balance' => $credits - $debits,
        ];
    }

    /**
     * Get single supplier transaction
     * @param  int $id
     * @return object|null
     */
    public function get_supplier_transaction($id)
    {
        $this->db->where('id', (int) $id);

        return $this->db->get(db_prefix() . 'accounts_supplier_transactions')->row();
    }

    /**
     * Add supplier transaction and mirror it into the account ledger
     * @param  int   $supplier_id
     * @param  array $data
     * @return int|false
     */
    public function add_supplier_transaction($supplier_id, $data)
    {
        $supplier = $this->get_supplier($supplier_id);
        if (!$supplier || !empty($supplier->deleted_at)) {
            return false;
        }

        $row = $this->prepare_supplier_txn_payload($data);
        if ($row === false) {
            return false;
        }

        $row['supplier_id'] = (int) $supplier_id;
        $row['addedfrom']   = get_staff_user_id();
        $row['datecreated'] = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix() . 'accounts_supplier_transactions', $row);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            $this->sync_supplier_to_ledger($insert_id);
        }

        return $insert_id ?: false;
    }

    /**
     * Update supplier transaction and its synced ledger row
     * @param  int   $id
     * @param  array $data
     * @return bool
     */
    public function update_supplier_transaction($id, $data)
    {
        $existing = $this->get_supplier_transaction($id);
        if (!$existing) {
            return false;
        }

        $row = $this->prepare_supplier_txn_payload($data);
        if ($row === false) {
            return false;
        }

        $this->db->where('id', (int) $id);
        $this->db->update(db_prefix() . 'accounts_supplier_transactions', $row);

        $this->sync_supplier_to_ledger((int) $id);

        return true;
    }

    /**
     * Delete supplier transaction and its synced ledger row
     * @param  int $id
     * @return bool
     */
    public function delete_supplier_transaction($id)
    {
        $this->db->where('id', (int) $id);
        $this->db->delete(db_prefix() . 'accounts_supplier_transactions');
        if ($this->db->affected_rows() < 1) {
            return false;
        }

        return $this->delete_by_source('supplier', $id);
    }

    /**
     * Statement data for one supplier over a date range.
     * Rows carry the same keys the account statement view consumes.
     * @param  int    $supplier_id
     * @param  string $from sql date
     * @param  string $to   sql date
     * @return array
     */
    public function get_supplier_statement_data($supplier_id, $from, $to)
    {
        $supplier = $this->get_supplier($supplier_id);

        $this->db->select("COALESCE(SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE -amount END), 0) AS net", false);
        $this->db->where('supplier_id', (int) $supplier_id);
        $this->db->where('transaction_date <', $from);
        $beginning_row = $this->db->get(db_prefix() . 'accounts_supplier_transactions')->row();
        $beginning = $beginning_row ? (float) $beginning_row->net : 0.0;

        $this->db->where('supplier_id', (int) $supplier_id);
        $this->db->where('transaction_date >=', $from);
        $this->db->where('transaction_date <=', $to);
        $this->db->order_by('transaction_date', 'asc');
        $this->db->order_by('id', 'asc');
        $rows = $this->db->get(db_prefix() . 'accounts_supplier_transactions')->result_array();

        $credits = 0.0;
        $debits = 0.0;
        foreach ($rows as &$row) {
            if ($row['transaction_type'] === 'credit') {
                $credits += (float) $row['amount'];
            } else {
                $debits += (float) $row['amount'];
            }
            $row['description']  = $row['note'];
            $row['source_type']  = 'supplier';
            $row['is_alzarooni'] = 0;
        }
        unset($row);

        return [
            'from'              => $from,
            'to'                => $to,
            'account'           => null,
            'supplier_name'     => $supplier ? $supplier->name : '',
            'beginning_balance' => $beginning,
            'total_credits'     => $credits,
            'total_debits'      => $debits,
            'closing_balance'   => $beginning + $credits - $debits,
            'transactions'      => $rows,
        ];
    }

    /**
     * First supplier transaction date (for full-history statements)
     * @param  int $supplier_id
     * @return string|null sql date
     */
    public function get_supplier_first_txn_date($supplier_id)
    {
        $this->db->select_min('transaction_date');
        $this->db->where('supplier_id', (int) $supplier_id);
        $row = $this->db->get(db_prefix() . 'accounts_supplier_transactions')->row();

        return ($row && $row->transaction_date) ? $row->transaction_date : null;
    }

    /**
     * Mirror a supplier transaction into account_transactions.
     * Supplier name leads the description per the ledger requirement;
     * the stored account is the chosen payment method (cash|bank).
     * @param  int $txn_id
     * @return bool
     */
    private function sync_supplier_to_ledger($txn_id)
    {
        $txn = $this->get_supplier_transaction($txn_id);
        if (!$txn) {
            return $this->delete_by_source('supplier', $txn_id);
        }

        $supplier = $this->get_supplier($txn->supplier_id);

        $parts = array_filter([
            'Supplier: ' . ($supplier ? $supplier->name : '#' . $txn->supplier_id),
            $txn->note,
        ]);

        return $this->upsert_synced_transaction([
            'transaction_type' => $txn->transaction_type,
            'account'          => $txn->account,
            'amount'           => (float) $txn->amount,
            'transaction_date' => $txn->transaction_date,
            'description'      => implode(' — ', $parts),
            'source_type'      => 'supplier',
            'source_id'        => (int) $txn->id,
            'reference'        => (string) $txn->reference,
            'paymentmode'      => $txn->account,
            'addedfrom'        => (int) $txn->addedfrom,
        ]);
    }

    /**
     * Normalize supplier transaction POST data
     * @param  array $data
     * @return array|false
     */
    private function prepare_supplier_txn_payload($data)
    {
        $type = isset($data['transaction_type']) ? $data['transaction_type'] : '';
        if (!in_array($type, ['credit', 'debit'], true)) {
            return false;
        }

        $amount = (float) str_replace(',', '', (string) ($data['amount'] ?? 0));
        if ($amount <= 0) {
            return false;
        }

        $date = !empty($data['transaction_date']) ? to_sql_date($data['transaction_date']) : date('Y-m-d');

        $account = in_array(($data['account'] ?? ''), ['cash', 'bank'], true) ? $data['account'] : 'cash';

        return [
            'transaction_type' => $type,
            'account'          => $account,
            'amount'           => $amount,
            'transaction_date' => $date,
            'reference'        => isset($data['reference']) ? trim((string) $data['reference']) : '',
            'note'             => isset($data['note']) ? trim((string) $data['note']) : '',
        ];
    }
}
