<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('accounts_settings'); ?></h4>
                        <hr class="hr-panel-heading">

                        <div class="alert alert-info">
                            <?php echo _l('accounts_opening_balance_help'); ?>
                        </div>

                        <?php echo form_open(admin_url('accounts/settings')); ?>
                        <div class="row">
                            <div class="col-md-3">
                                <?php
                                $arrAtt = ['data-type' => 'currency'];
                                $value = isset($balances_as_of) ? number_format((float) $balances_as_of['cash'], 2, '.', '') : '';
                                echo render_input('opening_balance_cash', 'accounts_opening_balance_cash', $value, 'text', $arrAtt);
                                ?>
                            </div>
                            <div class="col-md-3">
                                <?php
                                $value = isset($balances_as_of) ? number_format((float) $balances_as_of['bank'], 2, '.', '') : '';
                                echo render_input('opening_balance_bank', 'accounts_opening_balance_bank', $value, 'text', $arrAtt);
                                ?>
                            </div>
                            <div class="col-md-3">
                                <?php
                                echo render_date_input('opening_balance_date', 'accounts_opening_balance_date', _d(date('Y-m-d')));
                                ?>
                            </div>
                            <div class="col-md-3">
                                <?php
                                $selected = $settings ? $settings->currency : '';
                                echo render_select('currency', $currencies, ['id', 'name', 'symbol'], 'currency', $selected);
                                ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('accounts_daily_balances'); ?></h4>
                        <hr class="hr-panel-heading">

                        <div class="row mbot15">
                            <div class="col-md-3">
                                <?php
                                echo render_select('daily_account', [
                                    ['id' => '', 'name' => _l('accounts_all_accounts')],
                                    ['id' => 'cash', 'name' => _l('accounts_cash')],
                                    ['id' => 'bank', 'name' => _l('accounts_bank')],
                                ], ['id', 'name'], 'accounts_account', $daily_account ?? '');
                                ?>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_date_input('daily_from', 'from_date', $daily_from ?? ''); ?>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_date_input('daily_to', 'to_date', $daily_to ?? ''); ?>
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="button" class="btn btn-default" id="daily-apply">
                                        <?php echo _l('accounts_apply_filters'); ?>
                                    </button>
                                    <button type="button" class="btn btn-info" id="daily-download">
                                        <i class="fa fa-file-pdf"></i>
                                        <?php echo _l('accounts_download_statement'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-accounts-daily">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('accounts_date'); ?></th>
                                        <th><?php echo _l('accounts_opening_balance'); ?></th>
                                        <th><?php echo _l('accounts_total_credits'); ?></th>
                                        <th><?php echo _l('accounts_total_debits'); ?></th>
                                        <th><?php echo _l('accounts_closing_balance'); ?></th>
                                        <th><?php echo _l('accounts_statement'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($daily_rows as $day) { ?>
                                    <tr>
                                        <td data-order="<?php echo e($day['date']); ?>"><?php echo _d($day['date']); ?></td>
                                        <td><?php echo app_format_money($day['opening'], $base_currency); ?></td>
                                        <td><?php echo app_format_money($day['credits'], $base_currency); ?></td>
                                        <td><?php echo app_format_money($day['debits'], $base_currency); ?></td>
                                        <td><?php echo app_format_money($day['closing'], $base_currency); ?></td>
                                        <td>
                                            <a href="<?php echo admin_url('accounts/statement?date=' . $day['date'] . ($daily_account ? '&account=' . $daily_account : '')); ?>"
                                               class="btn btn-default btn-icon"
                                               title="<?php echo _l('accounts_download_statement'); ?>">
                                                <i class="fa fa-file-pdf"></i>
                                            </a>
                                        </td>
                                    </tr>
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
<script>
(function ($) {
    "use strict";
    $("input[data-type='currency']").on({
        keyup: function () { format_money($(this)); },
        blur: function () { format_money($(this), true); }
    });
    function format_money(input, blur) {
        if (typeof formatCurrency === 'function') {
            formatCurrency(input, blur ? 'blur' : undefined);
        }
    }

    function daily_filters() {
        var params = {};
        var account = $('select[name="daily_account"]').val();
        var from = $('input[name="daily_from"]').val();
        var to = $('input[name="daily_to"]').val();
        if (account) { params.account = account; }
        if (from) { params.from = from; }
        if (to) { params.to = to; }
        return params;
    }

    $('#daily-apply').on('click', function () {
        window.location.href = admin_url + 'accounts/settings?' + $.param(daily_filters());
    });

    $('#daily-download').on('click', function () {
        window.location.href = admin_url + 'accounts/statement?' + $.param(daily_filters());
    });

    if (typeof appDataTableInline === 'function') {
        appDataTableInline('.table-accounts-daily', {
            order: [[0, 'desc']],
            supportsLoading: true,
            supportsButtons: true
        });
    }
})(jQuery);
</script>
</body>
</html>
