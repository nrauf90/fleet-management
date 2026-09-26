<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('accounts_dashboard'); ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!$is_configured) { ?>
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning">
                    <?php echo _l('accounts_opening_balance_required'); ?>
                    <?php if (has_permission('accounts', '', 'settings') || is_admin()) { ?>
                    <a href="<?php echo admin_url('accounts/settings'); ?>" class="alert-link">
                        <?php echo _l('accounts_go_to_settings'); ?>
                    </a>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php } ?>

        <?php foreach (['cash' => _l('accounts_cash'), 'bank' => _l('accounts_bank')] as $account_key => $account_label) { ?>
        <h4 class="tw-font-semibold"><?php echo $account_label; ?></h4>
        <div class="row">
            <div class="col-md-3">
                <div class="accounts-stat-card accounts-stat-balance">
                    <div class="accounts-stat-label"><?php echo _l('accounts_current_balance'); ?></div>
                    <div class="accounts-stat-value"><?php echo app_format_money($summary[$account_key]['current_balance'], $base_currency); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="accounts-stat-card">
                    <div class="accounts-stat-label"><?php echo _l('accounts_opening_balance'); ?></div>
                    <div class="accounts-stat-value"><?php echo app_format_money($summary[$account_key]['opening_balance'], $base_currency); ?></div>
                    <?php if (!empty($summary['opening_balance_date'])) { ?>
                    <div class="text-muted mtop5"><?php echo _d($summary['opening_balance_date']); ?></div>
                    <?php } ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="accounts-stat-card accounts-stat-credit">
                    <div class="accounts-stat-label"><?php echo _l('accounts_total_credits'); ?></div>
                    <div class="accounts-stat-value"><?php echo app_format_money($summary[$account_key]['total_credits'], $base_currency); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="accounts-stat-card accounts-stat-debit">
                    <div class="accounts-stat-label"><?php echo _l('accounts_total_debits'); ?></div>
                    <div class="accounts-stat-value"><?php echo app_format_money($summary[$account_key]['total_debits'], $base_currency); ?></div>
                </div>
            </div>
        </div>
        <?php } ?>

        <div class="row mtop20">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tw-flex tw-justify-between tw-items-center">
                            <h4 class="no-margin"><?php echo _l('accounts_recent_transactions'); ?></h4>
                            <a href="<?php echo admin_url('accounts/transactions'); ?>" class="btn btn-default btn-sm">
                                <?php echo _l('accounts_view_all'); ?>
                            </a>
                        </div>
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-striped no-mtop">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('accounts_date'); ?></th>
                                        <th><?php echo _l('accounts_type'); ?></th>
                                        <th><?php echo _l('accounts_amount'); ?></th>
                                        <th><?php echo _l('accounts_description'); ?></th>
                                        <th><?php echo _l('accounts_source'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent)) { ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted"><?php echo _l('accounts_no_transactions'); ?></td>
                                    </tr>
                                    <?php } else { ?>
                                    <?php foreach ($recent as $tx) { ?>
                                    <tr>
                                        <td><?php echo _d($tx['transaction_date']); ?></td>
                                        <td>
                                            <?php if ($tx['transaction_type'] === 'credit') { ?>
                                            <span class="label label-success"><?php echo _l('accounts_credit'); ?></span>
                                            <?php } else { ?>
                                            <span class="label label-danger"><?php echo _l('accounts_debit'); ?></span>
                                            <?php } ?>
                                        </td>
                                        <td><?php echo app_format_money($tx['amount'], $base_currency); ?></td>
                                        <td><?php echo e($tx['description']); ?></td>
                                        <td><?php echo e(accounts_source_label($tx['source_type'])); ?></td>
                                    </tr>
                                    <?php } ?>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
