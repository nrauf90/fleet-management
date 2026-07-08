/**
 * Salary Module JavaScript
 */

$(document).ready(function () {
  // Initialize salary module
  initSalaryModule();

  // Initialize tooltips
  if (typeof $.fn.tooltip !== 'undefined') {
    $('[data-toggle="tooltip"]').tooltip();
  }

  // Initialize popovers
  if (typeof $.fn.popover !== 'undefined') {
    $('[data-toggle="popover"]').popover();
  }
});

/**
 * Initialize salary module functionality
 */
function initSalaryModule() {
  // Format currency inputs
  $('.currency-input').on('input', function () {
    formatCurrencyInput(this);
  });

  // Handle advance amount validation
  $('.advance-amount').on('input', function () {
    validateAdvanceAmount(this);
  });

  // Handle form submissions
  $('.salary-form').on('submit', function (e) {
    if (!validateSalaryForm(this)) {
      e.preventDefault();
    }
  });

  // Handle status changes
  $('.status-change').on('change', function () {
    handleStatusChange(this);
  });

  // Initialize data tables if available
  if (typeof $.fn.DataTable !== 'undefined') {
    initSalaryDataTables();
  }
}

/**
 * Format currency input
 */
function formatCurrencyInput(input) {
  let value = $(input).val();

  // Remove non-numeric characters except decimal point
  value = value.replace(/[^\d.]/g, '');

  // Ensure only one decimal point
  const parts = value.split('.');
  if (parts.length > 2) {
    value = parts[0] + '.' + parts.slice(1).join('');
  }

  // Limit decimal places to 2
  if (parts.length === 2 && parts[1].length > 2) {
    value = parts[0] + '.' + parts[1].substring(0, 2);
  }

  $(input).val(value);
}

/**
 * Validate advance amount
 */
function validateAdvanceAmount(input) {
  const amount = parseFloat($(input).val()) || 0;
  const currentSalary = parseFloat($('#current_salary').val()) || 0;
  const maxPercentage = parseFloat($('#max_advance_percentage').val()) || 50;

  const maxAmount = (currentSalary * maxPercentage) / 100;

  if (amount > maxAmount) {
    $(input).addClass('is-invalid');
    showAlert(
      'Amount exceeds maximum allowed advance of ' + formatCurrency(maxAmount),
      'warning',
    );
  } else {
    $(input).removeClass('is-invalid');
  }
}

/**
 * Validate salary form
 */
function validateSalaryForm(form) {
  let isValid = true;

  // Check required fields
  $(form)
    .find('[required]')
    .each(function () {
      if (!$(this).val()) {
        $(this).addClass('is-invalid');
        isValid = false;
      } else {
        $(this).removeClass('is-invalid');
      }
    });

  // Check currency fields
  $(form)
    .find('.currency-input')
    .each(function () {
      const value = parseFloat($(this).val()) || 0;
      if (value < 0) {
        $(this).addClass('is-invalid');
        isValid = false;
      }
    });

  if (!isValid) {
    showAlert('Please fill in all required fields correctly.', 'error');
  }

  return isValid;
}

/**
 * Handle status change
 */
function handleStatusChange(select) {
  const status = $(select).val();
  const row = $(select).closest('tr');

  // Update status label
  const statusLabel = row.find('.status-label');
  statusLabel.removeClass().addClass('status-label status-' + status);
  statusLabel.text(status.charAt(0).toUpperCase() + status.slice(1));

  // Show/hide action buttons based on status
  if (status === 'pending') {
    row.find('.approve-btn, .reject-btn').show();
    row.find('.pay-btn').hide();
  } else if (status === 'approved') {
    row.find('.approve-btn, .reject-btn').hide();
    row.find('.pay-btn').show();
  } else {
    row.find('.approve-btn, .reject-btn, .pay-btn').hide();
  }
}

/**
 * Initialize salary data tables
 */
function initSalaryDataTables() {
  $('.salary-datatable').each(function () {
    $(this).DataTable({
      responsive: true,
      pageLength: 25,
      order: [[0, 'asc']],
      language: {
        search: 'Search:',
        lengthMenu: 'Show _MENU_ entries per page',
        info: 'Showing _START_ to _END_ of _TOTAL_ entries',
        infoEmpty: 'Showing 0 to 0 of 0 entries',
        infoFiltered: '(filtered from _MAX_ total entries)',
        paginate: {
          first: 'First',
          last: 'Last',
          next: 'Next',
          previous: 'Previous',
        },
      },
    });
  });
}

/**
 * Show alert message
 */
function showAlert(message, type) {
  const alertClass =
    type === 'error'
      ? 'alert-danger'
      : type === 'warning'
      ? 'alert-warning'
      : type === 'success'
      ? 'alert-success'
      : 'alert-info';

  const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;

  // Remove existing alerts
  $('.alert').remove();

  // Add new alert
  $('.content').prepend(alertHtml);

  // Auto-dismiss after 5 seconds
  setTimeout(function () {
    $('.alert').fadeOut();
  }, 5000);
}

/**
 * Format currency
 */
function formatCurrency(amount) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount);
}

/**
 * Confirm action
 */
function confirmAction(message, callback) {
  if (confirm(message)) {
    callback();
  }
}

/**
 * Load salary statistics
 */
function loadSalaryStats() {
  $.ajax({
    url: admin_url + 'salary/get_stats',
    type: 'GET',
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        updateDashboardStats(response.data);
      }
    },
    error: function () {
      showAlert('Failed to load statistics.', 'error');
    },
  });
}

/**
 * Update dashboard statistics
 */
function updateDashboardStats(stats) {
  $('#total_staff').text(stats.total_staff);
  $('#total_salary').text(formatCurrency(stats.total_salary));
  $('#pending_requests').text(stats.pending_advance_requests);
  $('#monthly_advance').text(formatCurrency(stats.monthly_advance));
}

// Export functions for global use
window.SalaryModule = {
  init: initSalaryModule,
  formatCurrency: formatCurrency,
  showAlert: showAlert,
  confirmAction: confirmAction,
  loadStats: loadSalaryStats,
};
