(function ($) {
  "use strict";

  function toggle_alzarooni_row($modal) {
    var isAlzarooni = $modal.find('select[name="account"]').val() === "alzarooni";
    $modal.find(".alzarooni-payment-row").toggleClass("hide", !isAlzarooni);
  }

  window.add_account_transaction = function () {
    var $modal = $("#account-transaction-modal");
    $modal.removeClass("edit-mode");
    $modal.find('input[name="id"]').val("");
    $modal.find('select[name="transaction_type"]').selectpicker("val", "credit");
    $modal.find('select[name="account"]').selectpicker("val", "cash");
    $modal.find('select[name="alzarooni_account"]').selectpicker("val", "cash");
    toggle_alzarooni_row($modal);
    $modal.find('input[name="amount"]').val("");
    $modal.find('input[name="reference"]').val("");
    $modal.find('textarea[name="description"]').val("");
    $modal.modal("show");
  };

  window.edit_account_transaction = function (id) {
    $.get(admin_url + "accounts/get_transaction/" + id).done(function (response) {
      try {
        response = typeof response === "string" ? JSON.parse(response) : response;
      } catch (e) {
        return;
      }
      if (!response.success || response.source_type !== "manual") {
        alert_float("danger", "Only manual transactions can be edited.");
        return;
      }

      var $modal = $("#account-transaction-modal");
      $modal.addClass("edit-mode");
      $modal.find('input[name="id"]').val(response.id);
      $modal.find('select[name="transaction_type"]').selectpicker("val", response.transaction_type);
      $modal.find('select[name="account"]').selectpicker("val", response.is_alzarooni ? "alzarooni" : (response.account || "cash"));
      $modal.find('select[name="alzarooni_account"]').selectpicker("val", response.is_alzarooni ? (response.account || "cash") : "cash");
      toggle_alzarooni_row($modal);
      $modal.find('input[name="amount"]').val(response.amount);
      $modal.find('input[name="transaction_date"]').val(response.transaction_date);
      $modal.find('input[name="reference"]').val(response.reference || "");
      $modal.find('textarea[name="description"]').val(response.description || "");
      $modal.modal("show");
    });
  };

  window.add_supplier = function () {
    var $modal = $("#supplier-modal");
    $modal.removeClass("edit-mode");
    $modal.find('input[name="id"]').val("");
    $modal.find('input[name="name"]').val("");
    $modal.modal("show");
  };

  window.edit_supplier = function (id, name) {
    var $modal = $("#supplier-modal");
    $modal.addClass("edit-mode");
    $modal.find('input[name="id"]').val(id);
    $modal.find('input[name="name"]').val(name);
    $modal.modal("show");
  };

  window.add_supplier_transaction = function () {
    var $modal = $("#supplier-transaction-modal");
    $modal.removeClass("edit-mode");
    $modal.find('input[name="id"]').val("");
    $modal.find('select[name="transaction_type"]').selectpicker("val", "credit");
    $modal.find('select[name="account"]').selectpicker("val", "cash");
    $modal.find('input[name="amount"]').val("");
    $modal.find('input[name="reference"]').val("");
    $modal.find('textarea[name="note"]').val("");
    $modal.modal("show");
  };

  window.edit_supplier_transaction = function (id) {
    $.get(admin_url + "accounts/get_supplier_transaction/" + id).done(function (response) {
      try {
        response = typeof response === "string" ? JSON.parse(response) : response;
      } catch (e) {
        return;
      }
      if (!response.success) {
        return;
      }

      var $modal = $("#supplier-transaction-modal");
      $modal.addClass("edit-mode");
      $modal.find('input[name="id"]').val(response.id);
      $modal.find('select[name="transaction_type"]').selectpicker("val", response.transaction_type);
      $modal.find('select[name="account"]').selectpicker("val", response.account || "cash");
      $modal.find('input[name="amount"]').val(response.amount);
      $modal.find('input[name="transaction_date"]').val(response.transaction_date);
      $modal.find('input[name="reference"]').val(response.reference || "");
      $modal.find('textarea[name="note"]').val(response.note || "");
      $modal.modal("show");
    });
  };

  $(function () {
    appValidateForm($("#account-transaction-form"), {
      transaction_type: "required",
      account: "required",
      amount: "required",
      transaction_date: "required"
    });

    appValidateForm($("#supplier-form"), {
      name: "required"
    });

    appValidateForm($("#supplier-transaction-form"), {
      transaction_type: "required",
      account: "required",
      amount: "required",
      transaction_date: "required"
    });

    $(document).on("change", '#account-transaction-modal select[name="account"]', function () {
      toggle_alzarooni_row($("#account-transaction-modal"));
    });

    $("#account-transaction-modal, #supplier-transaction-modal").on("shown.bs.modal", function () {
      $("input[data-type='currency']").off("keyup.blur").on({
        keyup: function () {
          if (typeof formatCurrency === "function") {
            formatCurrency($(this));
          }
        },
        blur: function () {
          if (typeof formatCurrency === "function") {
            formatCurrency($(this), "blur");
          }
        }
      });
    });
  });
})(jQuery);
