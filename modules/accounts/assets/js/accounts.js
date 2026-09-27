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

  $(function () {
    appValidateForm($("#account-transaction-form"), {
      transaction_type: "required",
      account: "required",
      amount: "required",
      transaction_date: "required"
    });

    $(document).on("change", '#account-transaction-modal select[name="account"]', function () {
      toggle_alzarooni_row($("#account-transaction-modal"));
    });

    $("#account-transaction-modal").on("shown.bs.modal", function () {
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
