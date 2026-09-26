(function () {
  'use strict';
  $("input[data-type='currency']").on({
    keyup: function () {
      formatCurrency($(this));
    },
    blur: function () {
      formatCurrency($(this), 'blur');
    },
  });
  $(document).on('change','#vehicle_id',function() {
    // Get selected option's data-subtext attribute
    var subtext = $(this).find('option:selected').data("subtext");

    // Check the value and toggle fields accordingly
    if (subtext === "rented") {
        $('.rented-vehicle-fields').show();
        $('.own-vehicle-fields').hide();
    } else {
        $('.rented-vehicle-fields').hide();
        $('.own-vehicle-fields').show();
    }
});
})(jQuery);
function change_status(data) {
  'use strict';
  $.post(admin_url + 'fleet/admin_change_status', data).done(function (
    response,
  ) {
    response = JSON.parse(response);
    if (response.success == true) {
      alert_float('success', 'Status changed');
      setTimeout(function () {
        location.reload();
      }, 1500);
    }
  });
}

function create_invoice(id) {
  'use strict';
  if (confirm('Are you sure?')) {
    $.post(admin_url + 'fleet/create_invoice_by_booking/' + id).done(function (
      response,
    ) {
      response = JSON.parse(response);
      if (response.message != '') {
        alert_float('success', response.message);
        $('#invoice-number').text(response.invoice_number);
      } else {
        alert_float('danger');
      }
      $('#btn-create-invoice').addClass('hide');
    });
  }
}

function booking_status_mark_as(status, booking_id) {
  'use strict';

  var url = 'fleet/booking_status_mark_as/' + status + '/' + booking_id;
  $('body').append('<div class="dt-loader"></div>');

  requestGetJSON(url).done(function (response) {
    $('body').find('.dt-loader').remove();
    if (response.success === true || response.success == 'true') {
      alert_float('success', 'Status changed');
      setTimeout(function () {
        location.reload();
      }, 1500);
    }
  });
}

function update_info(id) {
  'use strict';
  $('#info-modal').modal('show');
}

function formatNumber(n) {
  'use strict';
  // format number 1000000 to 1,234,567
  return n.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
function formatCurrency(input, blur) {
  'use strict';
  // appends $ to value, validates decimal side
  // and puts cursor back in right position.

  // get input value
  var input_val = input.val();

  // don't validate empty input
  if (input_val === '') {
    return;
  }

  // original length
  var original_len = input_val.length;

  // initial caret position
  var caret_pos = input.prop('selectionStart');

  // check for decimal
  if (input_val.indexOf('.') >= 0) {
    // get position of first decimal
    // this prevents multiple decimals from
    // being entered
    var decimal_pos = input_val.indexOf('.');

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
    input_val = left_side + '.' + right_side;
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

// Initialize Dropzone for booking attachments
if (typeof Dropzone !== 'undefined') {
  Dropzone.autoDiscover = false;
  var attachmentsDropzone = new Dropzone('#booking-attachments-upload', {
    url: admin_url + 'fleet/upload_booking_attachment',
    maxFiles: 10,
    addRemoveLinks: true,
    acceptedFiles: '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif',
    success: function (file, response) {
      if (
        this.getUploadingFiles().length === 0 &&
        this.getQueuedFiles().length === 0
      ) {
        window.location.reload();
      }
    },
    error: function (file, response) {
      alert_float('danger', response);
    },
  });

  attachmentsDropzone.on('sending', function (file, xhr, formData) {
    formData.append('booking_id', $('input[name="_booking_id"]').val());
    if (typeof csrfData !== 'undefined') {
      formData.append(csrfData.token_name, csrfData.hash);
    }
  });
}

function update_delivery_note_totals() {
  var total = 0;
  $('#delivery-note-items tbody tr.dn-item-row').each(function (index) {
    $(this).find('.dn-s-no').text(index + 1);
    total += parseInt($(this).find('.dn-packages').val(), 10) || 0;
  });
  $('#delivery-note-total').text(total);
}

function add_delivery_note_row() {
  var row =
    '<tr class="dn-item-row">' +
    '<td class="dn-s-no"></td>' +
    '<td><select class="selectpicker dn-driver" data-width="100%"><option value=""></option>' +
    (typeof fleet_dn_driver_options_html !== 'undefined' ? fleet_dn_driver_options_html : '') +
    '</select></td>' +
    '<td><input type="text" class="form-control dn-truck-no"></td>' +
    '<td><input type="number" min="0" class="form-control dn-packages" value="0"></td>' +
    '<td class="text-center"><button type="button" class="btn btn-danger btn-icon" onclick="remove_delivery_note_row(this); return false;"><i class="fa fa-remove"></i></button></td>' +
    '</tr>';
  $('#delivery-note-items tbody').append(row);
  init_selectpicker();
  update_delivery_note_totals();
}

function remove_delivery_note_row(btn) {
  $(btn).closest('tr').remove();
  update_delivery_note_totals();
}

function save_delivery_note_items(booking_id) {
  var rows = [];
  $('#delivery-note-items tbody tr.dn-item-row').each(function () {
    rows.push({
      driver_id: $(this).find('select.dn-driver').val(),
      truck_no: $(this).find('.dn-truck-no').val(),
      packages: $(this).find('.dn-packages').val(),
    });
  });

  $.post(admin_url + 'fleet/save_delivery_note_items', {
    booking_id: booking_id,
    rows: rows,
    supplier: $('#dn_supplier').val(),
    contact_person: $('#dn_contact_person').val(),
    invoice_no: $('#dn_invoice_no').val(),
    destination: $('#dn_destination').val(),
    dispatch_date: $('#dn_dispatch_date').val(),
  }).done(function (response) {
    response = typeof response === 'string' ? JSON.parse(response) : response;
    if (response.success) {
      alert_float('success', response.message);
    } else {
      alert_float('danger', response.message || 'Failed to save delivery note');
    }
  }).fail(function () {
    alert_float('danger', 'Failed to save delivery note');
  });
}

$(function () {
  update_delivery_note_totals();

  $(document).on('change', '#delivery-note-items select.dn-driver', function () {
    var row = $(this).closest('tr');
    var truck = row.find('.dn-truck-no');
    if (!truck.val() && typeof fleet_driver_vehicle_map !== 'undefined') {
      var plate = fleet_driver_vehicle_map[$(this).val()];
      if (plate) {
        truck.val(plate);
      }
    }
  });

  $(document).on('input', '#delivery-note-items .dn-packages', function () {
    update_delivery_note_totals();
  });
});
