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
                        <?php echo form_open_multipart(admin_url('salary/add_advance'), ['id' => 'advance-salary-form']); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="staff_id" class="control-label"><?php echo _l('staff_member'); ?> <span
                                            class="text-danger">*</span></label>
                                    <select name="staff_id" class="selectpicker no-margin" data-width="100%"
                                        id="staff_id"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                                        required>
                                        <option value=""></option>
                                        <?php foreach ($staff_members as $staff) { ?>
                                            <option value="<?php echo $staff['staffid']; ?>"
                                                data-salary="<?php echo $staff['current_salary']; ?>">
                                                <?php echo $staff['firstname'] . ' ' . $staff['lastname']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="request_date" class="control-label"><?php echo _l('request_date'); ?>
                                        <span class="text-danger">*</span></label>
                                    <div class="input-group date">
                                        <input type="text" name="request_date" id="request_date"
                                            class="form-control datepicker" value="<?php echo _d(date('Y-m-d')); ?>"
                                            required>
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar calendar-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount" class="control-label"><?php echo _l('amount'); ?> <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><?php echo $base_currency->symbol; ?></span>
                                        <input type="number" name="amount" id="amount" class="form-control" step="0.01"
                                            min="0" required>
                                    </div>
                                    <small class="text-muted" id="max_advance_text"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="current_salary"
                                        class="control-label"><?php echo _l('current_salary'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><?php echo $base_currency->symbol; ?></span>
                                        <input type="text" id="current_salary" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="reason" class="control-label"><?php echo _l('reason'); ?> <span
                                            class="text-danger">*</span></label>
                                    <textarea name="reason" id="reason" class="form-control" rows="4" required
                                        placeholder="<?php echo _l('reason_placeholder'); ?>"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit"
                                    class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                                <a href="<?php echo admin_url('salary/advance'); ?>"
                                    class="btn btn-default pull-right mright5"><?php echo _l('cancel'); ?></a>
                            </div>
                        </div>

                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('advance_salary_info'); ?></h4>
                        <hr class="hr-panel-separator" />
                        <div class="alert alert-info">
                            <h5><?php echo _l('advance_salary_rules'); ?></h5>
                            <ul class="list-unstyled">
                                <li><i class="fa fa-check text-success"></i>
                                    <?php echo _l('max_advance_percentage_rule', $this->salary_model->get_salary_settings('max_advance_percentage') ?: 50); ?>
                                </li>
                                <li><i class="fa fa-check text-success"></i>
                                    <?php echo _l('advance_approval_required'); ?></li>
                                <li><i class="fa fa-check text-success"></i> <?php echo _l('advance_payment_terms'); ?>
                                </li>
                            </ul>
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
        // Initialize datepicker
        $('.datepicker').datetimepicker({
            format: app.options.date_format,
            locale: app.locale
        });

        // Initialize select picker
        $('.selectpicker').selectpicker();

        // Update current salary when staff is selected
        $('#staff_id').on('change', function () {
            var selectedOption = $(this).find('option:selected');
            var currentSalary = selectedOption.data('salary') || 0;
            var maxAdvancePercentage = <?php echo $this->salary_model->get_salary_settings('max_advance_percentage') ?: 50; ?>;
            var maxAdvance = (currentSalary * maxAdvancePercentage) / 100;

            $('#current_salary').val(app.format_money(currentSalary, <?php echo json_encode($base_currency); ?>));
            $('#amount').attr('max', maxAdvance);
            $('#max_advance_text').text('<?php echo _l('max_advance_amount'); ?>: ' + app.format_money(maxAdvance, <?php echo json_encode($base_currency); ?>));
        });

        // Form validation
        $('#advance-salary-form').on('submit', function (e) {
            var staff_id = $('#staff_id').val();
            var amount = parseFloat($('#amount').val()) || 0;
            var request_date = $('#request_date').val();
            var reason = $('#reason').val().trim();

            if (!staff_id) {
                alert_float('danger', '<?php echo _l('please_select_staff_member'); ?>');
                e.preventDefault();
                return false;
            }

            if (!amount || amount <= 0) {
                alert_float('danger', '<?php echo _l('please_enter_valid_amount'); ?>');
                e.preventDefault();
                return false;
            }

            if (!request_date) {
                alert_float('danger', '<?php echo _l('please_select_request_date'); ?>');
                e.preventDefault();
                return false;
            }

            if (!reason) {
                alert_float('danger', '<?php echo _l('please_enter_reason'); ?>');
                e.preventDefault();
                return false;
            }

            // Check if amount exceeds maximum allowed
            var maxAmount = parseFloat($('#amount').attr('max')) || 0;
            if (amount > maxAmount) {
                alert_float('danger', '<?php echo _l('amount_exceeds_maximum_allowed'); ?>');
                e.preventDefault();
                return false;
            }
        });
    });
</script>
</body>

</html>