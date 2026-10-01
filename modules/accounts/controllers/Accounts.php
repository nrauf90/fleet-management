<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Accounts extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('accounts/accounts_model');
        $this->load->model('currencies_model');
    }

    /**
     * Dashboard
     */
    public function index()
    {
        if (!has_permission('accounts', '', 'view') && !is_admin()) {
            access_denied('accounts');
        }

        $summary = $this->accounts_model->get_balance_summary();
        $data['title'] = _l('accounts_dashboard');
        $data['summary'] = $summary;
        $data['recent'] = $this->accounts_model->get_recent_transactions(10);
        $data['base_currency'] = $this->resolve_currency($summary['currency']);
        $data['is_configured'] = $summary['is_configured'];

        $this->load->view('dashboard', $data);
    }

    /**
     * Transactions list
     */
    public function transactions()
    {
        if (!has_permission('accounts', '', 'view') && !is_admin()) {
            access_denied('accounts');
        }

        $summary = $this->accounts_model->get_balance_summary();
        $data['title'] = _l('accounts_transactions');
        $data['summary'] = $summary;
        $data['base_currency'] = $this->resolve_currency($summary['currency']);
        $data['is_configured'] = $summary['is_configured'];

        $this->load->view('transactions/manage', $data);
    }

    /**
     * DataTables AJAX
     */
    public function transactions_table()
    {
        if (!has_permission('accounts', '', 'view') && !is_admin()) {
            ajax_access_denied();
        }

        $summary = $this->accounts_model->get_balance_summary();
        $currency = $this->resolve_currency($summary['currency']);

        $aColumns = [
            'transaction_date',
            'transaction_type',
            'account',
            'is_alzarooni',
            'amount',
            'description',
            'source_type',
            'reference',
            'paymentmode',
            'addedfrom',
        ];
        $sIndexColumn = 'id';
        $sTable = db_prefix() . 'account_transactions';

        $where = [];
        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        $type = $this->input->post('transaction_type');
        $source = $this->input->post('source_type');
        $account = $this->input->post('account');

        if ($from_date) {
            $where[] = 'AND transaction_date >= "' . $this->db->escape_str(to_sql_date($from_date)) . '"';
        }
        if ($to_date) {
            $where[] = 'AND transaction_date <= "' . $this->db->escape_str(to_sql_date($to_date)) . '"';
        }
        if ($type && in_array($type, ['credit', 'debit'], true)) {
            $where[] = 'AND transaction_type = "' . $this->db->escape_str($type) . '"';
        }
        if ($source && in_array($source, ['manual', 'payment', 'expense', 'logbook', 'supplier'], true)) {
            $where[] = 'AND source_type = "' . $this->db->escape_str($source) . '"';
        }
        if ($account === 'alzarooni') {
            $where[] = 'AND is_alzarooni = 1';
        } elseif ($account && in_array($account, ['cash', 'bank'], true)) {
            $where[] = 'AND account = "' . $this->db->escape_str($account) . '"';
        }

        $result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, [
            'id',
            'source_id',
            'transaction_id',
        ]);

        $output = $result['output'];
        $rResult = $result['rResult'];

        $payment_mode_names = [];
        foreach ($this->db->get(db_prefix() . 'payment_modes')->result() as $mode) {
            $payment_mode_names[(string) $mode->id] = $mode->name;
        }

        foreach ($rResult as $aRow) {
            $row = [];
            $row[] = _d($aRow['transaction_date']);

            $badge = $aRow['transaction_type'] === 'credit'
                ? '<span class="label label-success">' . _l('accounts_credit') . '</span>'
                : '<span class="label label-danger">' . _l('accounts_debit') . '</span>';
            $row[] = $badge;

            if (!empty($aRow['is_alzarooni'])) {
                $settled = $aRow['account'] === 'bank' ? _l('accounts_bank') : _l('accounts_cash');
                $row[] = '<span class="label label-default">' . e(_l('accounts_alzarooni')) . '</span>'
                    . '<div class="text-muted small">' . e($settled) . '</div>';
            } else {
                $account_label = $aRow['account'] === 'bank' ? _l('accounts_bank') : _l('accounts_cash');
                $row[] = '<span class="label label-default">' . e($account_label) . '</span>';
            }

            $row[] = app_format_money($aRow['amount'], $currency);

            $desc = e($aRow['description']);
            if ($aRow['source_type'] === 'payment' && $aRow['source_id']) {
                $this->load->model('payments_model');
                $payment = $this->payments_model->get($aRow['source_id']);
                if ($payment) {
                    $desc .= '<div class="row-options"><a href="' . admin_url('payments/payment/' . $aRow['source_id']) . '" target="_blank">' . _l('view') . '</a></div>';
                }
            } elseif ($aRow['source_type'] === 'expense' && $aRow['source_id']) {
                $desc .= '<div class="row-options"><a href="' . admin_url('expenses/list_expenses/' . $aRow['source_id']) . '" target="_blank">' . _l('view') . '</a></div>';
            } elseif ($aRow['source_type'] === 'logbook' && $aRow['source_id']) {
                $desc .= '<div class="row-options">';
                $desc .= '<a href="' . admin_url('fleet/logbook_detail/' . $aRow['source_id']) . '" target="_blank">' . _l('view') . '</a>';
                $this->db->select('booking_id');
                $this->db->where('id', $aRow['source_id']);
                $logbook_row = $this->db->get(db_prefix() . 'fleet_logbooks')->row();
                if ($logbook_row && !empty($logbook_row->booking_id)) {
                    $desc .= ' | <a href="' . admin_url('fleet/booking_detail/' . $logbook_row->booking_id) . '" target="_blank">' . _l('booking') . '</a>';
                }
                $desc .= '</div>';
            } elseif ($aRow['source_type'] === 'manual') {
                $opts = '<div class="row-options">';
                if (has_permission('accounts', '', 'edit') || is_admin()) {
                    $opts .= '<a href="#" onclick="edit_account_transaction(' . (int) $aRow['id'] . '); return false;">' . _l('edit') . '</a>';
                }
                if (has_permission('accounts', '', 'delete') || is_admin()) {
                    $opts .= ' | <a href="' . admin_url('accounts/delete_transaction/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
                }
                $opts .= '</div>';
                $desc .= $opts;
            }
            $row[] = $desc;

            $row[] = e(accounts_source_label($aRow['source_type']));
            $row[] = e($aRow['reference']);

            $mode_id = (string) $aRow['paymentmode'];
            if ($mode_id === '') {
                $row[] = '—';
            } else {
                $mode_cell = e($payment_mode_names[$mode_id] ?? $mode_id);
                if (!empty($aRow['transaction_id'])) {
                    $mode_cell .= '<div class="text-muted small">' . e($aRow['transaction_id']) . '</div>';
                }
                $row[] = $mode_cell;
            }

            $row[] = $aRow['addedfrom'] ? e(get_staff_full_name($aRow['addedfrom'])) : '—';

            $output['aaData'][] = $row;
        }

        echo json_encode($output);
        die();
    }

    /**
     * Get transaction JSON for edit modal
     */
    public function get_transaction($id)
    {
        if (!has_permission('accounts', '', 'view') && !is_admin()) {
            ajax_access_denied();
        }

        $tx = $this->accounts_model->get_transaction($id);
        if (!$tx) {
            echo json_encode(['success' => false]);
            die();
        }

        echo json_encode([
            'success'          => true,
            'id'               => (int) $tx->id,
            'transaction_type' => $tx->transaction_type,
            'account'          => $tx->account,
            'is_alzarooni'     => !empty($tx->is_alzarooni) ? 1 : 0,
            'amount'           => (float) $tx->amount,
            'transaction_date' => _d($tx->transaction_date),
            'description'      => $tx->description,
            'reference'        => $tx->reference,
            'source_type'      => $tx->source_type,
        ]);
        die();
    }

    /**
     * Add / update manual transaction
     */
    public function transaction()
    {
        if (!$this->input->post()) {
            redirect(admin_url('accounts/transactions'));
        }

        $id = $this->input->post('id');
        $data = $this->input->post();

        if ($id) {
            if (!has_permission('accounts', '', 'edit') && !is_admin()) {
                access_denied('accounts');
            }
            $success = $this->accounts_model->update_transaction($id, $data);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('accounts_transaction')));
            } else {
                set_alert('danger', _l('accounts_transaction_update_failed'));
            }
        } else {
            if (!has_permission('accounts', '', 'create') && !is_admin()) {
                access_denied('accounts');
            }
            $success = $this->accounts_model->add_transaction($data);
            if ($success) {
                set_alert('success', _l('added_successfully', _l('accounts_transaction')));
            } else {
                set_alert('danger', _l('accounts_transaction_add_failed'));
            }
        }

        redirect(admin_url('accounts/transactions'));
    }

    /**
     * Delete manual transaction
     */
    public function delete_transaction($id)
    {
        if (!has_permission('accounts', '', 'delete') && !is_admin()) {
            access_denied('accounts');
        }

        $success = $this->accounts_model->delete_transaction($id);
        if ($success) {
            set_alert('success', _l('deleted', _l('accounts_transaction')));
        } else {
            set_alert('danger', _l('accounts_transaction_delete_failed'));
        }

        redirect(admin_url('accounts/transactions'));
    }

    /**
     * Opening balance settings
     */
    public function settings()
    {
        if (!has_permission('accounts', '', 'settings') && !is_admin()) {
            access_denied('accounts');
        }

        if ($this->input->post()) {
            $success = $this->accounts_model->save_settings($this->input->post());
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('accounts_settings')));
            } else {
                set_alert('danger', _l('something_went_wrong'));
            }
            redirect(admin_url('accounts/settings'));
        }

        $settings = $this->accounts_model->get_settings();
        $data['title'] = _l('accounts_settings');
        $data['settings'] = $settings;
        $data['currencies'] = $this->currencies_model->get();
        $data['balances_as_of'] = $this->accounts_model->get_balances_as_of(date('Y-m-d'));
        $data['base_currency'] = $this->resolve_currency($settings ? (int) $settings->currency : 0);

        // Daily balances filter (GET params in user date format)
        $account_filter = $this->input->get('account');
        $account_filter = in_array($account_filter, ['cash', 'bank'], true) ? $account_filter : null;
        $from_filter = $this->input->get('from');
        $to_filter = $this->input->get('to');

        try {
            $from_sql = $from_filter ? to_sql_date($from_filter) : null;
            $to_sql = $to_filter ? to_sql_date($to_filter) : null;
        } catch (Throwable $e) {
            $from_sql = $to_sql = null;
        }
        if ($from_sql && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from_sql)) {
            $from_sql = null;
        }
        if ($to_sql && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to_sql)) {
            $to_sql = null;
        }

        $data['daily_rows'] = $this->accounts_model->get_daily_balances($account_filter, $from_sql, $to_sql);
        $data['daily_account'] = $account_filter;
        $data['daily_from'] = $from_filter;
        $data['daily_to'] = $to_filter;

        $this->load->view('settings', $data);
    }

    /**
     * Download a PDF statement for a single day (?date=Y-m-d) or a custom
     * range (?from=..&to=.. in user date format), optionally per account.
     */
    public function statement()
    {
        if (!has_permission('accounts', '', 'view') && !is_admin()) {
            access_denied('accounts');
        }

        $account = $this->input->get('account');
        $account = in_array($account, ['cash', 'bank', 'alzarooni'], true) ? $account : null;

        $day = $this->input->get('date');
        if ($day && preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
            $from = $day;
            $to = $day;
        } else {
            try {
                $from = $this->input->get('from') ? to_sql_date($this->input->get('from')) : null;
                $to = $this->input->get('to') ? to_sql_date($this->input->get('to')) : null;
            } catch (Throwable $e) {
                $from = $to = null;
            }
        }

        if (($from && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from))
            || ($to && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))) {
            $from = null;
        }

        if (!$from || !$to || $from > $to) {
            set_alert('danger', _l('accounts_statement_invalid_range'));
            redirect(admin_url('accounts/settings'));
        }

        $statement = $this->accounts_model->get_statement_data($from, $to, $account);
        $settings = $this->accounts_model->get_settings();
        $statement['currency'] = $this->resolve_currency($settings ? (int) $settings->currency : 0);

        try {
            $pdf = app_pdf(
                'account_statement',
                module_dir_path(ACCOUNTS_MODULE_NAME) . 'libraries/Account_statement_pdf',
                $statement
            );
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = $this->input->get('print') ? 'I' : 'D';

        $filename = 'account-statement-' . $from . '-to-' . $to . ($account ? '-' . $account : '') . '.pdf';
        $pdf->Output($filename, $type);
        die();
    }

    /**
     * Suppliers list
     */
    public function suppliers()
    {
        if (!has_permission('accounts', '', 'view') && !is_admin()) {
            access_denied('accounts');
        }

        $summary = $this->accounts_model->get_balance_summary();
        $data['title'] = _l('accounts_suppliers');
        $data['suppliers'] = $this->accounts_model->get_suppliers_with_totals();
        $data['base_currency'] = $this->resolve_currency($summary['currency']);

        $this->load->view('suppliers/manage', $data);
    }

    /**
     * Add / rename supplier
     */
    public function save_supplier()
    {
        if (!$this->input->post()) {
            redirect(admin_url('accounts/suppliers'));
        }

        $id = $this->input->post('id');
        $name = $this->input->post('name');

        if ($id) {
            if (!has_permission('accounts', '', 'edit') && !is_admin()) {
                access_denied('accounts');
            }
            $success = $this->accounts_model->update_supplier($id, $name);
            set_alert($success ? 'success' : 'danger', $success ? _l('updated_successfully', _l('accounts_supplier')) : _l('accounts_supplier_update_failed'));
        } else {
            if (!has_permission('accounts', '', 'create') && !is_admin()) {
                access_denied('accounts');
            }
            $success = $this->accounts_model->add_supplier($name);
            set_alert($success ? 'success' : 'danger', $success ? _l('added_successfully', _l('accounts_supplier')) : _l('accounts_supplier_add_failed'));
        }

        redirect(admin_url('accounts/suppliers'));
    }

    /**
     * Soft delete supplier
     */
    public function delete_supplier($id)
    {
        if (!has_permission('accounts', '', 'delete') && !is_admin()) {
            access_denied('accounts');
        }

        $success = $this->accounts_model->delete_supplier($id);
        set_alert($success ? 'success' : 'danger', $success ? _l('deleted', _l('accounts_supplier')) : _l('accounts_supplier_delete_failed'));

        redirect(admin_url('accounts/suppliers'));
    }

    /**
     * Supplier detail: transaction history + statement
     */
    public function supplier($id)
    {
        if (!has_permission('accounts', '', 'view') && !is_admin()) {
            access_denied('accounts');
        }

        $supplier = $this->accounts_model->get_supplier($id);
        if (!$supplier) {
            show_404();
        }

        $summary = $this->accounts_model->get_balance_summary();
        $data['supplier'] = $supplier;
        $data['is_deleted'] = !empty($supplier->deleted_at);
        $data['totals'] = $this->accounts_model->get_supplier_totals($id);
        $data['title'] = $supplier->name;
        $data['base_currency'] = $this->resolve_currency($summary['currency']);

        $this->load->view('suppliers/detail', $data);
    }

    /**
     * Supplier transactions DataTables AJAX
     */
    public function supplier_transactions_table($supplier_id)
    {
        if (!has_permission('accounts', '', 'view') && !is_admin()) {
            ajax_access_denied();
        }

        $supplier = $this->accounts_model->get_supplier($supplier_id);
        if (!$supplier) {
            ajax_access_denied();
        }

        $summary = $this->accounts_model->get_balance_summary();
        $currency = $this->resolve_currency($summary['currency']);

        $aColumns = [
            'transaction_date',
            'transaction_type',
            'account',
            'amount',
            'reference',
            'note',
            'addedfrom',
        ];
        $sIndexColumn = 'id';
        $sTable = db_prefix() . 'accounts_supplier_transactions';

        $where = ['AND supplier_id = ' . (int) $supplier_id];
        $from_date = $this->input->post('from_date');
        $to_date = $this->input->post('to_date');
        if ($from_date) {
            $where[] = 'AND transaction_date >= "' . $this->db->escape_str(to_sql_date($from_date)) . '"';
        }
        if ($to_date) {
            $where[] = 'AND transaction_date <= "' . $this->db->escape_str(to_sql_date($to_date)) . '"';
        }

        $result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, ['id']);
        $output = $result['output'];
        $rResult = $result['rResult'];

        $can_edit = (has_permission('accounts', '', 'edit') || is_admin()) && empty($supplier->deleted_at);
        $can_delete = has_permission('accounts', '', 'delete') || is_admin();

        foreach ($rResult as $aRow) {
            $row = [];
            $row[] = _d($aRow['transaction_date']);
            $row[] = $aRow['transaction_type'] === 'credit'
                ? '<span class="label label-success">' . _l('accounts_credit') . '</span>'
                : '<span class="label label-danger">' . _l('accounts_debit') . '</span>';
            $row[] = '<span class="label label-default">' . e($aRow['account'] === 'bank' ? _l('accounts_bank') : _l('accounts_cash')) . '</span>';
            $row[] = app_format_money($aRow['amount'], $currency);

            $note = e($aRow['note']);
            if ($can_edit || $can_delete) {
                $opts = '<div class="row-options">';
                if ($can_edit) {
                    $opts .= '<a href="#" onclick="edit_supplier_transaction(' . (int) $aRow['id'] . '); return false;">' . _l('edit') . '</a>';
                }
                if ($can_delete) {
                    $opts .= ($can_edit ? ' | ' : '') . '<a href="' . admin_url('accounts/delete_supplier_transaction/' . $aRow['id']) . '?supplier_id=' . (int) $supplier_id . '" class="text-danger _delete">' . _l('delete') . '</a>';
                }
                $opts .= '</div>';
                $note .= $opts;
            }
            $row[] = $note;

            $row[] = e($aRow['reference']);
            $row[] = $aRow['addedfrom'] ? e(get_staff_full_name($aRow['addedfrom'])) : '—';

            $output['aaData'][] = $row;
        }

        echo json_encode($output);
        die();
    }

    /**
     * Get supplier transaction JSON for edit modal
     */
    public function get_supplier_transaction($id)
    {
        if (!has_permission('accounts', '', 'view') && !is_admin()) {
            ajax_access_denied();
        }

        $tx = $this->accounts_model->get_supplier_transaction($id);
        if (!$tx) {
            echo json_encode(['success' => false]);
            die();
        }

        echo json_encode([
            'success'          => true,
            'id'               => (int) $tx->id,
            'supplier_id'      => (int) $tx->supplier_id,
            'transaction_type' => $tx->transaction_type,
            'account'          => $tx->account,
            'amount'           => (float) $tx->amount,
            'transaction_date' => _d($tx->transaction_date),
            'reference'        => $tx->reference,
            'note'             => $tx->note,
        ]);
        die();
    }

    /**
     * Add / update supplier transaction
     */
    public function supplier_transaction($supplier_id)
    {
        if (!$this->input->post()) {
            redirect(admin_url('accounts/supplier/' . (int) $supplier_id));
        }

        $id = $this->input->post('id');
        $data = $this->input->post();

        if ($id) {
            if (!has_permission('accounts', '', 'edit') && !is_admin()) {
                access_denied('accounts');
            }
            $success = $this->accounts_model->update_supplier_transaction($id, $data);
            set_alert($success ? 'success' : 'danger', $success ? _l('updated_successfully', _l('accounts_transaction')) : _l('accounts_transaction_update_failed'));
        } else {
            if (!has_permission('accounts', '', 'create') && !is_admin()) {
                access_denied('accounts');
            }
            $success = $this->accounts_model->add_supplier_transaction($supplier_id, $data);
            set_alert($success ? 'success' : 'danger', $success ? _l('added_successfully', _l('accounts_transaction')) : _l('accounts_transaction_add_failed'));
        }

        redirect(admin_url('accounts/supplier/' . (int) $supplier_id));
    }

    /**
     * Delete supplier transaction (also removes the synced ledger row)
     */
    public function delete_supplier_transaction($id)
    {
        if (!has_permission('accounts', '', 'delete') && !is_admin()) {
            access_denied('accounts');
        }

        $tx = $this->accounts_model->get_supplier_transaction($id);
        $supplier_id = $tx ? (int) $tx->supplier_id : (int) $this->input->get('supplier_id');

        $success = $this->accounts_model->delete_supplier_transaction($id);
        set_alert($success ? 'success' : 'danger', $success ? _l('deleted', _l('accounts_transaction')) : _l('accounts_transaction_delete_failed'));

        redirect(admin_url('accounts/supplier/' . $supplier_id));
    }

    /**
     * Download a PDF statement for a supplier. ?from=&to= in user date format;
     * omitting both exports the supplier's full history.
     */
    public function supplier_statement($id)
    {
        if (!has_permission('accounts', '', 'view') && !is_admin()) {
            access_denied('accounts');
        }

        $supplier = $this->accounts_model->get_supplier($id);
        if (!$supplier) {
            show_404();
        }

        try {
            $from = $this->input->get('from_date') ? to_sql_date($this->input->get('from_date')) : null;
            $to = $this->input->get('to_date') ? to_sql_date($this->input->get('to_date')) : null;
        } catch (Throwable $e) {
            $from = $to = null;
        }

        if (($from && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from))
            || ($to && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))
            || ($from && $to && $from > $to)) {
            set_alert('danger', _l('accounts_statement_invalid_range'));
            redirect(admin_url('accounts/supplier/' . (int) $id));
        }

        // Full history when no range is given
        $to = $to ?: date('Y-m-d');
        $from = $from ?: ($this->accounts_model->get_supplier_first_txn_date($id) ?: $to);

        $statement = $this->accounts_model->get_supplier_statement_data($id, $from, $to);
        $settings = $this->accounts_model->get_settings();
        $statement['currency'] = $this->resolve_currency($settings ? (int) $settings->currency : 0);

        try {
            $pdf = app_pdf(
                'account_statement',
                module_dir_path(ACCOUNTS_MODULE_NAME) . 'libraries/Account_statement_pdf',
                $statement
            );
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = $this->input->get('print') ? 'I' : 'D';
        $pdf->Output('supplier-statement-' . $supplier->id . '-' . $from . '-to-' . $to . '.pdf', $type);
        die();
    }

    /**
     * Resolve currency object for formatting
     * @param  int $currency_id
     * @return object
     */
    private function resolve_currency($currency_id)
    {
        if ($currency_id) {
            $currency = $this->currencies_model->get($currency_id);
            if ($currency) {
                return $currency;
            }
        }

        return $this->currencies_model->get_base_currency();
    }

    /**
     * Add a custom expense payee name from the expense form modal.
     * @return json
     */
    public function add_expense_payee()
    {
        if (!has_permission('expenses', '', 'create') && !is_admin()) {
            ajax_access_denied();
        }

        $name = $this->input->post('name');
        $payee = $this->accounts_model->add_expense_payee($name);

        if (!$payee) {
            echo json_encode([
                'success' => false,
                'message' => _l('something_went_wrong'),
            ]);
            die();
        }

        echo json_encode([
            'success' => true,
            'id'      => $payee['id'],
            'name'    => $payee['name'],
            'message' => _l('added_successfully'),
        ]);
        die();
    }
}
