<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo $title; ?>
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php echo form_open_multipart(admin_url('salary/settings'), ['id' => 'salary-settings-form']); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_advance_percentage"
                                        class="control-label"><?php echo _l('max_advance_percentage'); ?> <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="max_advance_percentage" id="max_advance_percentage"
                                            class="form-control" min="1" max="100"
                                            value="<?php echo $settings['max_advance_percentage'] ?? 50; ?>" required>
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <small class="text-muted"><?php echo _l('max_advance_percentage_help'); ?></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- <div class="form-group">
                                    <label for="salary_currency"
                                        class="control-label"><?php echo _l('salary_currency'); ?></label>
                                    <select name="salary_currency" id="salary_currency" class="selectpicker no-margin"
                                        data-width="100%">
                                        <option value="USD" <?php echo ($settings['salary_currency'] ?? 'USD') == 'USD' ? 'selected' : ''; ?>>USD - US Dollar</option>
                                        <option value="EUR" <?php echo ($settings['salary_currency'] ?? 'USD') == 'EUR' ? 'selected' : ''; ?>>EUR - Euro</option>
                                        <option value="GBP" <?php echo ($settings['salary_currency'] ?? 'USD') == 'GBP' ? 'selected' : ''; ?>>GBP - British Pound</option>
                                        <option value="JPY" <?php echo ($settings['salary_currency'] ?? 'USD') == 'JPY' ? 'selected' : ''; ?>>JPY - Japanese Yen</option>
                                        <option value="CAD" <?php echo ($settings['salary_currency'] ?? 'USD') == 'CAD' ? 'selected' : ''; ?>>CAD - Canadian Dollar</option>
                                        <option value="AUD" <?php echo ($settings['salary_currency'] ?? 'USD') == 'AUD' ? 'selected' : ''; ?>>AUD - Australian Dollar</option>
                                    </select>
                                </div> -->
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="salary_decimal_places"
                                        class="control-label"><?php echo _l('salary_decimal_places'); ?></label>
                                    <select name="salary_decimal_places" id="salary_decimal_places"
                                        class="selectpicker no-margin" data-width="100%">
                                        <option value="0" <?php echo ($settings['salary_decimal_places'] ?? '2') == '0' ? 'selected' : ''; ?>>0 (Whole numbers)</option>
                                        <option value="2" <?php echo ($settings['salary_decimal_places'] ?? '2') == '2' ? 'selected' : ''; ?>>2 (Standard)</option>
                                        <option value="3" <?php echo ($settings['salary_decimal_places'] ?? '2') == '3' ? 'selected' : ''; ?>>3 (High precision)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="advance_approval_required"
                                        class="control-label"><?php echo _l('advance_approval_required'); ?></label>
                                    <div class="checkbox checkbox-primary">
                                        <input type="checkbox" name="advance_approval_required"
                                            id="advance_approval_required" value="1" <?php echo ($settings['advance_approval_required'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                        <label for="advance_approval_required">
                                            <?php echo _l('require_approval_for_advance'); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit"
                                    class="btn btn-info pull-right"><?php echo _l('save_settings'); ?></button>
                            </div>
                        </div>

                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('salary_settings_info'); ?></h4>
                        <hr class="hr-panel-separator" />
                        <div class="alert alert-info">
                            <h5><?php echo _l('settings_explanation'); ?></h5>
                            <ul class="list-unstyled">
                                <li><i class="fa fa-info-circle text-info"></i>
                                    <?php echo _l('max_advance_percentage_explanation'); ?></li>
                                <li><i class="fa fa-info-circle text-info"></i>
                                    <?php echo _l('currency_explanation'); ?></li>
                                <li><i class="fa fa-info-circle text-info"></i>
                                    <?php echo _l('decimal_places_explanation'); ?></li>
                                <li><i class="fa fa-info-circle text-info"></i>
                                    <?php echo _l('approval_required_explanation'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('quick_actions'); ?></h4>
                        <hr class="hr-panel-separator" />
                        <div class="row">
                            <div class="col-md-12">
                                <a href="<?php echo admin_url('salary'); ?>" class="btn btn-default btn-block">
                                    <i class="fa fa-dashboard"></i> <?php echo _l('back_to_dashboard'); ?>
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <a href="<?php echo admin_url('salary/staff'); ?>" class="btn btn-info btn-block">
                                    <i class="fa fa-users"></i> <?php echo _l('manage_staff_salary'); ?>
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <a href="<?php echo admin_url('salary/advance'); ?>" class="btn btn-warning btn-block">
                                    <i class="fa fa-credit-card"></i> <?php echo _l('manage_advance_salary'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    $(function () {
        // Initialize select picker
        $('.selectpicker').selectpicker();

        // Form validation
        $('#salary-settings-form').on('submit', function (e) {
            var maxAdvancePercentage = parseInt($('#max_advance_percentage').val()) || 0;

            if (maxAdvancePercentage <= 0 || maxAdvancePercentage > 100) {
                alert_float('danger', '<?php echo _l('please_enter_valid_percentage'); ?>');
                e.preventDefault();
                return false;
            }
        });

        // Auto-save settings on change
        $('#max_advance_percentage, #salary_currency, #salary_decimal_places').on('change', function () {
            // Optionally auto-save settings
            // $('#salary-settings-form').submit();
        });
    });
</script>
</body>

</html>