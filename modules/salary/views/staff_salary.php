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
                        <?php echo form_open_multipart(admin_url('salary/staff_salary' . ($staff_salary ? '/' . $staff_salary->staffid : '')), ['id' => 'staff-salary-form']); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="staff_id" class="control-label"><?php echo _l('staff_member'); ?> <span
                                            class="text-danger">*</span></label>
                                    <select name="staff_id"
                                        class="selectpicker no-margin<?php if ($staff_salary)
                                            echo ' disabled'; ?>"
                                        data-width="100%" id="staff_id"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" <?php if ($staff_salary)
                                               echo 'disabled'; ?>>
                                        <option value=""></option>
                                        <?php foreach ($staff_members as $staff) { ?>
                                            <option value="<?php echo $staff['staffid']; ?>" <?php if ($staff_salary && $staff_salary->staffid == $staff['staffid'])
                                                   echo 'selected'; ?>>
                                                <?php echo $staff['firstname'] . ' ' . $staff['lastname']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <?php if ($staff_salary): ?>
                                        <input type="hidden" name="staff_id" value="<?php echo $staff_salary->staffid; ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="effective_date"
                                        class="control-label"><?php echo _l('effective_date'); ?> <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group date">
                                        <input type="text" name="effective_date" id="effective_date"
                                            class="form-control datepicker"
                                            value="<?php echo $staff_salary ? _d($staff_salary->effective_date) : _d(date('Y-m-d')); ?>"
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
                                    <label for="initial_salary"
                                        class="control-label"><?php echo _l('initial_salary'); ?> <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><?php echo $base_currency->symbol; ?></span>
                                        <input type="number" name="initial_salary" id="initial_salary"
                                            class="form-control" step="0.01" min="0"
                                            value="<?php echo $staff_salary ? $staff_salary->initial_salary : ''; ?>"
                                            required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="advance_salary"
                                        class="control-label"><?php echo _l('advance_salary'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><?php echo $base_currency->symbol; ?></span>
                                        <input type="number" name="advance_salary" id="advance_salary"
                                            class="form-control" step="0.01" min="0"
                                            value="<?php echo $staff_salary ? $staff_salary->advance_salary : '0.00'; ?>">
                                    </div>
                                    <small class="text-muted"><?php echo _l('advance_salary_help_text'); ?></small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit"
                                    class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                            </div>
                        </div>

                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>

            <?php if ($staff_salary && isset($salary_history)): ?>
                <div class="col-md-4">
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="no-margin"><?php echo _l('salary_history'); ?></h4>
                            <hr class="hr-panel-separator" />
                            <?php if (!empty($salary_history)): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('effective_date'); ?></th>
                                                <th><?php echo _l('initial_salary'); ?></th>
                                                <th><?php echo _l('advance_salary'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($salary_history as $history): ?>
                                                <tr>
                                                    <td><?php echo _d($history['effective_date']); ?></td>
                                                    <td><?php echo app_format_money($history['initial_salary'], $base_currency); ?>
                                                    </td>
                                                    <td><?php echo app_format_money($history['advance_salary'], $base_currency); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted"><?php echo _l('no_salary_history'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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

        // Form validation
        $('#staff-salary-form').on('submit', function (e) {
            var staff_id = $('#staff_id').val();
            var initial_salary = $('#initial_salary').val();
            var effective_date = $('#effective_date').val();

            if (!staff_id) {
                alert_float('danger', '<?php echo _l('please_select_staff_member'); ?>');
                e.preventDefault();
                return false;
            }

            if (!initial_salary || initial_salary <= 0) {
                alert_float('danger', '<?php echo _l('please_enter_valid_initial_salary'); ?>');
                e.preventDefault();
                return false;
            }

            if (!effective_date) {
                alert_float('danger', '<?php echo _l('please_select_effective_date'); ?>');
                e.preventDefault();
                return false;
            }
        });

        // Auto-calculate advance salary based on percentage
        $('#initial_salary').on('input', function () {
            var initial_salary = parseFloat($(this).val()) || 0;
            var max_advance_percentage = <?php echo $this->salary_model->get_salary_settings('max_advance_percentage') ?: 50; ?>;
            var max_advance = (initial_salary * max_advance_percentage) / 100;

            $('#advance_salary').attr('max', max_advance);
            $('#advance_salary').attr('placeholder', 'Max: ' + max_advance.toFixed(2));
        });
    });
</script>
</body>

</html>