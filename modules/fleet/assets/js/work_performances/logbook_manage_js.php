<script type="text/javascript">
  // Payment mode id => name, used to decide whether a transaction id is relevant.
  var fleetPaymentModeNames = <?php
    $fleet_pm_map = [];
    if (isset($payment_modes) && is_array($payment_modes)) {
        foreach ($payment_modes as $fleet_pm) {
            $fleet_pm_map[(string) $fleet_pm['id']] = $fleet_pm['name'];
        }
    }
    echo json_encode($fleet_pm_map);
  ?>;

  // Cash-style modes settle on the spot, so they carry no transaction reference.
  function fleetModeNeedsTransactionId(modeId) {
    "use strict";
    if (!modeId) {
      return false;
    }
    var name = fleetPaymentModeNames[String(modeId)] || '';
    return !/cash/i.test(name);
  }

  function fleetToggleTransactionField() {
    "use strict";
    var $field = $('#logbook-modal .logbook-transaction-field');
    if (fleetModeNeedsTransactionId($('#logbook-modal select[name="paymentmode"]').val())) {
      $field.show();
    } else {
      $field.hide().find('input[name="transaction_id"]').val('');
    }
  }

  var fnServerParams;
  (function ($) {
    "use strict";

    $(document).on('change', '#logbook-modal select[name="paymentmode"]', fleetToggleTransactionField);

    appValidateForm($('#logbook-form'), {
      name: 'required',
      booking_id: 'required',
      date: 'required',
      driver_id: 'required',
      vehicle_id: 'required',
    }, fuel_form_handler);

    fnServerParams = {
      "booking_id": '[name="_booking_id"]',
      "status": '[name="status"]',
      "from_date": '[name="from_date"]',
      "to_date": '[name="to_date"]',
    };

    $('.add-new-logbook').on('click', function () {
      $('#logbook-modal').find('button[type="submit"]').prop('disabled', false);
      $('#logbook-modal').modal('show');
      $('#logbook-modal input[name="id"]').val('');
      $('#logbook-modal select[name="vehicle_id"]').val('').change();
      $('#logbook-modal select[name="booking_id"]').val('').change();
      $('#logbook-modal select[name="driver_id"]').val('').change();
      $('#logbook-modal input[name="name"]').val('');
      $('#logbook-modal input[name="date"]').val('');
      $('#logbook-modal input[name="odometer"]').val('');
      $('#logbook-modal textarea[name="description"]').val('');
      $('#logbook-modal input[name="hand_cash"]').val('0.00');
      $('#logbook-modal input[name="used_cash"]').val('0.00');
      $('#logbook-modal select[name="paymentmode"]').val('').change();
      $('#logbook-modal input[name="transaction_id"]').val('');
      fleetToggleTransactionField();
    });

    $('select[name="status"]').on('change', function () {
      init_fuel_table();
    });

    $('input[name="from_date"]').on('change', function () {
      init_fuel_table();
    });

    $('input[name="to_date"]').on('change', function () {
      init_fuel_table();
    });

    init_fuel_table();

    $("input[data-type='currency']").on({
      keyup: function () {
        formatCurrency($(this));
      },
      blur: function () {
        formatCurrency($(this), "blur");
      }
    });
  })(jQuery);

  function init_fuel_table() {
    "use strict";

    if ($.fn.DataTable.isDataTable('.table-logbook')) {
      $('.table-logbook').DataTable().destroy();
    }
    initDataTable('.table-logbook', admin_url + 'fleet/logbook_table', [0], [0], fnServerParams, [1, 'desc']);
    $('.dataTables_filter').addClass('hide');
  }

  function fuel_form_handler(form) {
    "use strict";
    $('#logbook-modal').find('button[type="submit"]').prop('disabled', true);

    var formURL = form.action;
    var formData = new FormData($(form)[0]);

    $.ajax({
      type: $(form).attr('method'),
      data: formData,
      mimeType: $(form).attr('enctype'),
      contentType: false,
      cache: false,
      processData: false,
      url: formURL
    }).done(function (response) {
      response = JSON.parse(response);
      if (response.success === true || response.success == 'true' || $.isNumeric(response.success)) {
        alert_float('success', response.message);
        init_fuel_table();
      } else {
        alert_float('danger', response.message);
      }
      $('#logbook-modal').modal('hide');
    }).fail(function (error) {
      alert_float('danger', JSON.parse(error.mesage));
    });

    return false;
  }

  function formatNumber(n) {
    "use strict";
    // format number 1000000 to 1,234,567
    return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
  }
  function formatCurrency(input, blur) {
    "use strict";
    // appends $ to value, validates decimal side
    // and puts cursor back in right position.

    // get input value
    var input_val = input.val();

    // don't validate empty input
    if (input_val === "") { return; }

    // original length
    var original_len = input_val.length;

    // initial caret position
    var caret_pos = input.prop("selectionStart");

    // check for decimal
    if (input_val.indexOf(".") >= 0) {

      // get position of first decimal
      // this prevents multiple decimals from
      // being entered
      var decimal_pos = input_val.indexOf(".");

      // split number by decimal point
      var left_side = input_val.substring(0, decimal_pos);
      var right_side = input_val.substring(decimal_pos);

      // add commas to left side of number
      left_side = formatNumber(left_side);

      // validate right side
      right_side = formatNumber(right_side);

      // Limit decimal to only 2 digits
      right_side = right_side.substring(0, 2);

      // join number by .
      input_val = left_side + "." + right_side;

    } else {
      // no decimal entered
      // add commas to number
      // remove all non-digits
      input_val = formatNumber(input_val);
      input_val = input_val;

    }

    // send updated string to input
    input.val(input_val);

    // put caret back in the right position
    var updated_len = input_val.length;
    caret_pos = updated_len - original_len + caret_pos;
    input[0].setSelectionRange(caret_pos, caret_pos);
  }

  function edit_logbook(id) {
    "use strict";
    $('#logbook-modal').find('button[type="submit"]').prop('disabled', false);

    requestGetJSON(admin_url + 'fleet/get_data_logbook/' + id).done(function (response) {
      $('#logbook-modal').modal('show');

      $('input[name="id"]').val(id);
      $('select[name="vehicle_id"]').val(response.vehicle_id).change();
      $('select[name="booking_id"]').val(response.booking_id).change();
      $('select[name="driver_id"]').val(response.driver_id).change();
      $('input[name="name"]').val(response.name);
      $('input[name="date"]').val(response.date);
      $('input[name="odometer"]').val(response.odometer);
      $('textarea[name="description"]').val(response.description);
      $('input[name="hand_cash"]').val(response.hand_cash);
      $('input[name="used_cash"]').val(response.used_cash);
      $('#logbook-modal select[name="paymentmode"]').val(response.paymentmode || '').change();
      $('#logbook-modal input[name="transaction_id"]').val(response.transaction_id || '');
      fleetToggleTransactionField();

    });
  }

  function adjust_cash(booking_id) {
    "use strict";

    if (confirm('<?php echo _l("confirm_adjust_cash"); ?>')) {
      $('#btn-adjust-cash').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l("processing"); ?>...');

      $.ajax({
        type: 'POST',
        url: admin_url + 'fleet/adjust_cash',
        data: {
          booking_id: booking_id
        },
        dataType: 'json'
      }).done(function (response) {
        if (response.success === true) {
          alert_float('success', response.message);
          // Reload the page to show updated data
          setTimeout(function () {
            window.location.reload();
          }, 1500);
        } else {
          alert_float('danger', response.message);
        }
      }).fail(function (error) {
        alert_float('danger', '<?php echo _l("something_went_wrong"); ?>');
      }).always(function () {
        $('#btn-adjust-cash').prop('disabled', false).html('<i class="fa fa-money"></i> <?php echo _l("adjust_cash"); ?>');
      });
    }
  }

</script>