<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="expense-payee-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('accounts/add_expense_payee'), ['id' => 'expense-payee-form']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('accounts_new_expense_payee'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo render_input('name', 'accounts_expense_payee_name'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<script>
  window.addEventListener('load', function () {
    appValidateForm($('#expense-payee-form'), { name: 'required' }, manage_expense_payee);

    $('#expense-payee-modal').on('hidden.bs.modal', function () {
      $('#expense-payee-modal input[name="name"]').val('');
    });

    // The customer field has no hook of its own, so the picker is rendered higher up
    // and moved into place here.
    var $field = $('#expense-payee-field');
    var $customer = $('#clientid').closest('.form-group');
    if ($field.length && $customer.length) {
      $field.insertBefore($customer);
    }
  });

  function new_expense_payee() {
    $('#expense-payee-modal').modal('show');
  }

  function manage_expense_payee(form) {
    $.post(form.action, $(form).serialize()).done(function (response) {
      response = JSON.parse(response);

      if (response.success === true) {
        var payee = $('#payee');
        // Reuse the row when the name already existed, otherwise add it.
        if (payee.find('option[value="' + response.id + '"]').length === 0) {
          payee.append('<option value="' + response.id + '">' + response.name + '</option>');
        }
        payee.selectpicker('val', response.id);
        payee.selectpicker('refresh');
        alert_float('success', response.message);
      } else {
        alert_float('danger', response.message);
      }

      $('#expense-payee-modal').modal('hide');
    });

    return false;
  }
</script>
