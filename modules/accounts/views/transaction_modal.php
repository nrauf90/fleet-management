<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="account-transaction-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('accounts/transaction'), ['id' => 'account-transaction-form']); ?>
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
                            ['id' => 'alzarooni', 'name' => _l('accounts_alzarooni')],
                        ], ['id', 'name'], 'accounts_account', 'cash');
                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $arrAtt = ['data-type' => 'currency'];
                        echo render_input('amount', 'accounts_amount', '', 'text', $arrAtt);
                        ?>
                    </div>
                </div>
                <div class="row alzarooni-payment-row hide">
                    <div class="col-md-4">
                        <?php
                        echo render_select('alzarooni_account', [
                            ['id' => 'cash', 'name' => _l('accounts_cash')],
                            ['id' => 'bank', 'name' => _l('accounts_bank')],
                        ], ['id', 'name'], 'payment_mode', 'cash');
                        ?>
                    </div>
                    <div class="col-md-8">
                        <p class="text-muted mtop25"><?php echo _l('accounts_alzarooni_help'); ?></p>
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
                <?php echo render_textarea('description', 'accounts_description'); ?>
                <p class="text-muted"><?php echo _l('accounts_manual_help'); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
