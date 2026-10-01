<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <h4 class="no-margin pull-left">
                                <?php echo e($supplier->name); ?>
                                <?php if ($is_deleted) { ?>
                                <span class="label label-default"><?php echo _l('accounts_supplier_deleted'); ?></span>
                                <?php } ?>
                            </h4>
                            <div class="pull-right">
                                <a href="<?php echo admin_url('accounts/suppliers'); ?>" class="btn btn-default">
                                    <?php echo _l('accounts_suppliers'); ?>
                                </a>
                                <?php if ((has_permission('accounts', '', 'create') || is_admin()) && !$is_deleted) { ?>
                                <a href="#" class="btn btn-info" onclick="add_supplier_transaction(); return false;">
                                    <?php echo _l('accounts_add_transaction'); ?>
                                </a>
                                <?php } ?>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <hr class="hr-panel-heading">

                        <div class="row mbot15">
                            <div class="col-md-12">
                                <strong><?php echo _l('accounts_total_credits'); ?>:</strong>
                                <?php echo app_format_money($totals['credits'], $base_currency); ?>
                                &nbsp;|&nbsp;
                                <strong><?php echo _l('accounts_total_debits'); ?>:</strong>
                                <?php echo app_format_money($totals['debits'], $base_currency); ?>
                                &nbsp;|&nbsp;
                                <strong><?php echo _l('accounts_balance'); ?>:</strong>
                                <span class="<?php echo $totals['balance'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo app_format_money($totals['balance'], $base_currency); ?>
                                </span>
                            </div>
                        </div>

                        <?php echo form_open(admin_url('accounts/supplier_statement/' . $supplier->id), ['method' => 'get']); ?>
                        <div class="row mbot15">
                            <div class="col-md-3">
                                <?php echo render_date_input('from_date', 'from_date'); ?>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_date_input('to_date', 'to_date'); ?>
                            </div>
                            <div class="col-md-3">
                                <label class="control-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-default">
                                        <i class="fa fa-file-pdf-o"></i> <?php echo _l('accounts_download_statement'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php echo form_close(); ?>

                        <table class="table table-supplier-transactions scroll-responsive">
                            <thead>
                                <tr>
                                    <th><?php echo _l('accounts_date'); ?></th>
                                    <th><?php echo _l('accounts_type'); ?></th>
                                    <th><?php echo _l('payment_mode'); ?></th>
                                    <th><?php echo _l('accounts_amount'); ?></th>
                                    <th><?php echo _l('accounts_description'); ?></th>
                                    <th><?php echo _l('accounts_reference'); ?></th>
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

<?php if (!$is_deleted) { ?>
<div class="modal fade" id="supplier-transaction-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('accounts/supplier_transaction/' . $supplier->id), ['id' => 'supplier-transaction-form']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title"><?php echo _l('accounts_edit_transaction'); ?></span>
                    <span class="add-title"><?php echo _l('accounts_add_transaction'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <?php echo form_hidden('id'); ?>
                <div class="row">
                    <div class="col-md-4">
                        <?php
                        echo render_select('transaction_type', [
                            ['id' => 'credit', 'name' => _l('accounts_credit')],
                            ['id' => 'debit', 'name' => _l('accounts_debit')],
                        ], ['id', 'name'], 'accounts_type', 'credit');
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                        echo render_select('account', [
                            ['id' => 'cash', 'name' => _l('accounts_cash')],
                            ['id' => 'bank', 'name' => _l('accounts_bank')],
                        ], ['id', 'name'], 'payment_mode', 'cash');
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $arrAtt = ['data-type' => 'currency'];
                        echo render_input('amount', 'accounts_amount', '', 'text', $arrAtt);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?php echo render_date_input('transaction_date', 'accounts_date', _d(date('Y-m-d'))); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo render_input('reference', 'accounts_reference'); ?>
                    </div>
                </div>
                <?php echo render_textarea('note', 'accounts_description'); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php } ?>

<?php init_tail(); ?>
<script>
    var supplierTransactionsServerParams = {
        from_date: '[name="from_date"]',
        to_date: '[name="to_date"]'
    };
    initDataTable('.table-supplier-transactions', admin_url + 'accounts/supplier_transactions_table/<?php echo (int) $supplier->id; ?>', [], [], supplierTransactionsServerParams, [0, 'desc']);

    $('input[name="from_date"], input[name="to_date"]').on('change', function () {
        $('.table-supplier-transactions').DataTable().ajax.reload();
    });
</script>
</body>
</html>
