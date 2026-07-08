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
                        <?php echo form_open_multipart(admin_url('salary/edit_advance/' . $advance->id), ['id' => 'edit-advance-salary-form']); ?>

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
                                                data-salary="<?php echo $staff['current_salary']; ?>" <?php echo $advance->staff_id == $staff['staffid'] ? 'selected' : ''; ?>>
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
                                            class="form-control datepicker"
                                            value="<?php echo _d($advance->request_date); ?>" required>
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
                                            min="0" value="<?php echo $advance->amount; ?>" required>
                                    </div>
                                    <small class="text-muted" id="max_advance_text"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="control-label"><?php echo _l('status'); ?></label>
                                    <select name="status" id="status" class="selectpicker no-margin" data-width="100%">
                                        <option value="pending" <?php echo $advance->status == 'pending' ? 'selected' : ''; ?>><?php echo _l('advance_status_pending'); ?></option>
                                        <option value="approved" <?php echo $advance->status == 'approved' ? 'selected' : ''; ?>><?php echo _l('advance_status_approved'); ?></option>
                                        <option value="rejected" <?php echo $advance->status == 'rejected' ? 'selected' : ''; ?>><?php echo _l('advance_status_rejected'); ?></option>
                                        <option value="paid" <?php echo $advance->status == 'paid' ? 'selected' : ''; ?>>
                                            <?php echo _l('advance_status_paid'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="reason" class="control-label"><?php echo _l('reason'); ?> <span
                                            class="text-danger">*</span></label>
                                    <textarea name="reason" id="reason" class="form-control" rows="4" required
                                        placeholder="<?php echo _l('reason_placeholder'); ?>"><?php echo $advance->reason; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit"
                                    class="btn btn-info pull-right"><?php echo _l('update'); ?></button>
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
                        <h4 class="no-margin"><?php echo _l('request_details'); ?></h4>
                        <hr class="hr-panel-separator" />
                        <table class="table table-striped">
                            <tr>
                                <td><strong><?php echo _l('request_id'); ?></strong></td>
                                <td>#<?php echo $advance->id; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php echo _l('created_by'); ?></strong></td>
                                <td><?php echo get_staff_full_name($advance->created_by); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php echo _l('created_at'); ?></strong></td>
                                <td><?php echo _dt($advance->created_at); ?></td>
                            </tr>
                            <?php if ($advance->approved_by): ?>
                                <tr>
                                    <td><strong><?php echo _l('approved_by'); ?></strong></td>
                                    <td><?php echo $advance->approver_name; ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($advance->approved_date): ?>
                                <tr>
                                    <td><strong><?php echo _l('approved_date'); ?></strong></td>
                                    <td><?php echo _d($advance->approved_date); ?></td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('quick_actions'); ?></h4>
                        <hr class="hr-panel-separator" />
                        <div class="row">
                            <div class="col-md-12">
                                <?php if ($advance->status == 'pending'): ?>
                                    <a href="<?php echo admin_url('salary/approve_advance/' . $advance->id . '/approved'); ?>"
                                        class="btn btn-success btn-block"
                                        onclick="return confirm('<?php echo _l('confirm_approve_advance'); ?>')">
                                        <i class="fa fa-check"></i> <?php echo _l('approve'); ?>
                                    </a>
                                    <a href="<?php echo admin_url('salary/approve_advance/' . $advance->id . '/rejected'); ?>"
                                        class="btn btn-danger btn-block"
                                        onclick="return confirm('<?php echo _l('confirm_reject_advance'); ?>')">
                                        <i class="fa fa-times"></i> <?php echo _l('reject'); ?>
                                    </a>
                                <?php endif; ?>
                                <?php if (has_permission('salary', '', 'delete')): ?>
                                    <a href="<?php echo admin_url('salary/delete_advance/' . $advance->id); ?>"
                                        class="btn btn-danger btn-block"
                                        onclick="return confirm('<?php echo _l('confirm_delete_advance'); ?>')">
                                        <i class="fa fa-trash"></i> <?php echo _l('delete'); ?>
                                    </a>
                                <?php endif; ?>
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

            $('#amount').attr('max', maxAdvance);
            $('#max_advance_text').text('<?php echo _l('max_advance_amount'); ?>: ' + app.format_money(maxAdvance, <?php echo json_encode($base_currency); ?>));
        });

        // Trigger change event on load
        $('#staff_id').trigger('change');

        // Form validation
        $('#edit-advance-salary-form').on('submit', function (e) {
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