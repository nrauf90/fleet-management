var fnServerParams;
(function($) {
	"use strict";

	fnServerParams = {
    };

    appValidateForm($('#fuel-route-form'), {
			name: 'required',
			amount: 'required',
    	},fuel_route_form_handler);

    init_fuel_routes_table();
    
    $('.add-new-fuel-route').on('click', function(){
      $('#fuel-route-modal').find('button[type="submit"]').prop('disabled', false);

      $('#fuel-route-form input[name="name"]').val('');
      $('#fuel-route-form input[name="amount"]').val('');
      $('#fuel-route-form input[name="id"]').val('');

      $('#fuel-route-modal .add-title').removeClass('hide');
      $('#fuel-route-modal .edit-title').addClass('hide');

      $('#fuel-route-modal').modal('show');
    });
})(jQuery);

function init_fuel_routes_table() {
  "use strict";

  if ($.fn.DataTable.isDataTable('.table-fuel-routes')) {
    $('.table-fuel-routes').DataTable().destroy();
  }
  initDataTable('.table-fuel-routes', admin_url + 'fleet/fuel_routes_table', false, false, fnServerParams);
}


function edit_fuel_route(id) {
  "use strict";
    $('#fuel-route-modal').find('button[type="submit"]').prop('disabled', false);

  requestGetJSON(admin_url + 'fleet/fuel_route/'+id).done(function(response) {
      $('#fuel-route-modal .add-title').addClass('hide');
      $('#fuel-route-modal .edit-title').removeClass('hide');

      $('#fuel-route-form input[name="name"]').val(response.name);
      $('#fuel-route-form input[name="amount"]').val(response.amount);
      $('#fuel-route-form input[name="id"]').val(id);

      $('#fuel-route-modal').modal('show');
  });
}

function fuel_route_form_handler(form) {
    "use strict";
    $('#fuel-route-modal').find('button[type="submit"]').prop('disabled', true);

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
    }).done(function(response) {
        response = JSON.parse(response);
        if (response.success === true || response.success == 'true' || $.isNumeric(response.success)) {
          	alert_float('success', response.message);

	 		    init_fuel_routes_table();
        }else{
          alert_float('danger', response.message);
        }
        $('#fuel-route-modal').modal('hide');
    }).fail(function(error) {
        alert_float('danger', JSON.parse(error.mesage));
    });

    return false;
}
