(function ($) {
  "use strict";

  function fillImporterExporter(clientId) {
    var $importer = $('textarea[name="importer"]');
    var $exporter = $('textarea[name="exporter"]');

    if (!$importer.length && !$exporter.length) {
      return;
    }

    if (!clientId) {
      $importer.val("");
      $exporter.val("");
      return;
    }

    $.get(admin_url + "fleet/get_client_booking_rate/" + clientId).done(function (response) {
      try {
        response = typeof response === "string" ? JSON.parse(response) : response;
      } catch (e) {
        return;
      }
      $importer.val(response.importer || "");
      $exporter.val(response.exporter || "");
    });
  }

  function movePartiesFieldsBeforeBilling() {
    var $parties = $("#fleet-invoice-parties-fields");
    if (!$parties.length) {
      return;
    }

    var $billingRow = $(".invoice.accounting-template .col-md-6 > .row")
      .filter(function () {
        return $(this).find(".billing_street").length;
      })
      .first();

    if ($billingRow.length) {
      $parties.insertBefore($billingRow);
    }
  }

  function hideDuplicateInvoiceViewBlocks() {
    if (!$(".fleet-invoice-left-panel").length) {
      return;
    }

    var $preview = $("#invoice-preview");
    if ($preview.length) {
      $preview.addClass("fleet-invoice-custom-layout");
    }

    var $right = $(
      "#invoice-preview .col-sm-6.text-right, .transaction-html-info-col-right"
    );
    $right
      .find(
        ".invoice-html-bill-to, .invoice-html-customer-billing-info, .invoice-html-ship-to, .invoice-html-customer-shipping-info"
      )
      .addClass("fleet-invoice-hide-on-right");
    $right.children("span.bold").slice(0, 2).addClass("fleet-invoice-hide-on-right");
    $right.children("address").slice(0, 2).addClass("fleet-invoice-hide-on-right");
  }

  $(function () {
    if ($('textarea[name="importer"]').length) {
      movePartiesFieldsBeforeBilling();

      $("body").on("change", ".f_client_id #clientid", function () {
        fillImporterExporter($(this).val());
      });

      var isEdit = $('input[name="isedit"]').length > 0;
      if (!isEdit && $("#clientid").val()) {
        fillImporterExporter($("#clientid").val());
      }
    }

    hideDuplicateInvoiceViewBlocks();
  });

  $(document).on("app.invoice_loaded", function () {
    hideDuplicateInvoiceViewBlocks();
  });
})(jQuery);
