<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <h4 class="no-margin pull-left"><?php echo _l('accounts_transactions'); ?></h4>
                            <?php if ((has_permission('accounts', '', 'create') || is_admin()) && $is_configured) { ?>
                            <a href="#" class="btn btn-info pull-right" onclick="add_account_transaction(); return false;">
                                <?php echo _l('accounts_add_transaction'); ?>
                            </a>
                            <?php } ?>
                            <div class="clearfix"></div>
                        </div>
                        <hr class="hr-panel-heading">

                        <?php if (!$is_configured) { ?>
                        <div class="alert alert-warning">
                            <?php echo _l('accounts_opening_balance_required'); ?>
                            <?php if (has_permission('accounts', '', 'settings') || is_admin()) { ?>
                            <a href="<?php echo admin_url('accounts/settings'); ?>" class="alert-link">
                                <?php echo _l('accounts_go_to_settings'); ?>
                            </a>
                            <?php } ?>
                        </div>
                        <?php } ?>

                        <div class="row mbot15">
                            <div class="col-md-3">
                                <?php echo render_date_input('from_date', 'from_date'); ?>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_date_input('to_date', 'to_date'); ?>
                            </div>
                            <div class="col-md-2">
                                <?php
                                echo render_select('account_filter', [
                                    ['id' => 'cash', 'name' => _l('accounts_cash')],
                                    ['id' => 'bank', 'name' => _l('accounts_bank')],
                                    ['id' => 'alzarooni', 'name' => _l('accounts_alzarooni')],
                                ], ['id', 'name'], 'accounts_account');
                                ?>
                            </div>
                            <div class="col-md-2">
                                <?php
                                echo render_select('transaction_type_filter', [
                                    ['id' => 'credit', 'name' => _l('accounts_credit')],
                                    ['id' => 'debit', 'name' => _l('accounts_debit')],
                                ], ['id', 'name'], 'accounts_type');
                                ?>
                            </div>
                            <div class="col-md-2">
                                <?php
                                echo render_select('source_type_filter', [
                                    ['id' => 'manual', 'name' => _l('accounts_source_manual')],
                                    ['id' => 'payment', 'name' => _l('accounts_source_payment')],
                                    ['id' => 'expense', 'name' => _l('accounts_source_expense')],
                                    ['id' => 'logbook', 'name' => _l('accounts_source_logbook')],
                                ], ['id', 'name'], 'accounts_source');
                                ?>
                            </div>
                        </div>

                        <div class="row mbot15">
                            <div class="col-md-12">
                                <strong><?php echo _l('accounts_current_balance'); ?>:</strong>
                                <?php echo _l('accounts_cash'); ?>:
                                <?php echo app_format_money($summary['cash']['current_balance'], $base_currency); ?>
                                &nbsp;|&nbsp;
                                <?php echo _l('accounts_bank'); ?>:
                                <?php echo app_format_money($summary['bank']['current_balance'], $base_currency); ?>
                            </div>
                        </div>

                        <table class="table table-accounts-transactions scroll-responsive">
                            <thead>
                                <tr>
                                    <th><?php echo _l('accounts_date'); ?></th>
                                    <th><?php echo _l('accounts_type'); ?></th>
                                    <th><?php echo _l('accounts_account'); ?></th>
                                    <th><?php echo _l('accounts_amount'); ?></th>
                                    <th><?php echo _l('accounts_description'); ?></th>
                                    <th><?php echo _l('accounts_source'); ?></th>
                                    <th><?php echo _l('accounts_reference'); ?></th>
                                    <th><?php echo _l('payment_mode'); ?></th>
                                    <th><?php echo _l('staff'); ?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('transaction_modal'); ?>

<?php init_tail(); ?>
<script>
    var accountsTransactionsServerParams = {
        from_date: '[name="from_date"]',
        to_date: '[name="to_date"]',
        transaction_type: '[name="transaction_type_filter"]',
        source_type: '[name="source_type_filter"]',
        account: '[name="account_filter"]'
    };
    initDataTable('.table-accounts-transactions', admin_url + 'accounts/transactions_table', [], [], accountsTransactionsServerParams, [0, 'desc']);

    $('select[name="transaction_type_filter"], select[name="source_type_filter"], select[name="account_filter"]').on('change', function () {
        $('.table-accounts-transactions').DataTable().ajax.reload();
    });
    $('input[name="from_date"], input[name="to_date"]').on('change', function () {
        $('.table-accounts-transactions').DataTable().ajax.reload();
    });
</script>
</body>
</html>
