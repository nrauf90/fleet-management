<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin font-bold"><?php echo _l('booking_summary_report'); ?></h4>
                        <hr class="hr-panel-separator" />

                        <!-- Filters -->
                        <div class="row mbot15">
                            <div class="col-md-4">
                                <?php echo render_select('vehicle_id', $vehicles, array('id', 'name'), 'vehicle', '', array('multiple' => true)); ?>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_date_input('from_date', 'from_date'); ?>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_date_input('to_date', 'to_date'); ?>
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="filter-btn" class="btn btn-info pull-right">
                                    <i class="fa fa-filter"></i> <?php echo _l('filter'); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="panel panel-info">
                                    <div class="panel-heading">
                                        <h3 class="panel-title"><?php echo _l('total_bookings'); ?></h3>
                                    </div>
                                    <div class="panel-body">
                                        <h2 class="text-center" id="total-bookings">0</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-success">
                                    <div class="panel-heading">
                                        <h3 class="panel-title"><?php echo _l('completed'); ?></h3>
                                    </div>
                                    <div class="panel-body">
                                        <h2 class="text-center" id="completed">0</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-warning">
                                    <div class="panel-heading">
                                        <h3 class="panel-title"><?php echo _l('pending'); ?></h3>
                                    </div>
                                    <div class="panel-body">
                                        <h2 class="text-center" id="pending">0</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-danger">
                                    <div class="panel-heading">
                                        <h3 class="panel-title"><?php echo _l('cancelled'); ?></h3>
                                    </div>
                                    <div class="panel-body">
                                        <h2 class="text-center" id="cancelled">0</h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Summary Cards -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="panel panel-info">
                                    <div class="panel-heading">
                                        <h3 class="panel-title"><?php echo _l('total_revenue'); ?></h3>
                                    </div>
                                    <div class="panel-body">
                                        <h2 class="text-center" id="total-revenue">0</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-success">
                                    <div class="panel-heading">
                                        <h3 class="panel-title"><?php echo _l('total_expenses'); ?></h3>
                                    </div>
                                    <div class="panel-body">
                                        <h2 class="text-center" id="total-expense">0</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-warning">
                                    <div class="panel-heading">
                                        <h3 class="panel-title"><?php echo _l('total_used_cash'); ?></h3>
                                    </div>
                                    <div class="panel-body">
                                        <h2 class="text-center" id="total-used-cash">0</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-danger">
                                    <div class="panel-heading">
                                        <h3 class="panel-title"><?php echo _l('total_rented_cash'); ?></h3>
                                    </div>
                                    <div class="panel-body">
                                        <h2 class="text-center" id="total-rented-cash">0</h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Profit/Loss Card -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-info">
                                    <div class="panel-heading">
                                        <h3 class="panel-title"><?php echo _l('net_profit_loss'); ?></h3>
                                    </div>
                                    <div class="panel-body">
                                        <h2 class="text-center" id="net-profit-loss">0</h2>
                                    </div>
                                </div>
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
        // Initialize select2 for vehicle filter
        init_selectpicker();

        // Initialize datepickers
        init_datepicker();

        // Load initial data
        loadBookingSummary();

        // Handle filter button click
        $('#filter-btn').on('click', function () {
            loadBookingSummary();
        });
    });

    function loadBookingSummary() {
        var vehicle_id = $('#vehicle_id').val();
        var from_date = $('#from_date').val();
        var to_date = $('#to_date').val();

        // Show loading state
        $('.panel-body h2').html('<i class="fa fa-spinner fa-spin"></i>');

        $.get(admin_url + 'fleet/booking_summary_report', {
            vehicle_id: vehicle_id,
            from_date: from_date,
            to_date: to_date
        }).done(function (response) {
            response = JSON.parse(response);

            // Update statistics
            $('#total-bookings').text(response.total_bookings || 0);
            $('#completed').text(response.completed || 0);
            $('#pending').text(response.pending || 0);
            $('#cancelled').text(response.cancelled || 0);

            // Update financial data
            $('#total-revenue').text(formatMoney(response.total_revenue || 0));
            $('#total-expense').text(formatMoney(response.total_expenses || 0));
            $('#total-used-cash').text(formatMoney(response.total_used_cash || 0));
            $('#total-rented-cash').text(formatMoney(response.total_rented_cash || 0));

            // Calculate and display profit/loss
            var totalRevenue = parseFloat(response.total_revenue || 0);
            var totalUsedCash = parseFloat(response.total_used_cash || 0);
            var totalRentedCash = parseFloat(response.total_rented_cash || 0);
            var totalExpense = parseFloat(response.total_expenses || 0);
            var totalInvoiceItemsCost = parseFloat(response.total_invoice_items_cost || 0);

            var netProfitLoss = totalRevenue - totalUsedCash - totalRentedCash - totalInvoiceItemsCost - totalExpense;

            var profitLossElement = $('#net-profit-loss');
            profitLossElement.text(formatMoney(netProfitLoss));

            // Add color based on profit/loss
            profitLossElement.removeClass('text-success text-danger');
            if (netProfitLoss >= 0) {
                profitLossElement.addClass('text-success');
            } else {
                profitLossElement.addClass('text-danger');
            }
        }).fail(function (error) {
            alert_float('danger', 'Error loading booking summary');
        });
    }

    function formatMoney(amount) {
        var currency = <?php echo json_encode($base_currency ?? null); ?>;
        return accounting.formatMoney(amount, {
            symbol: currency?.symbol || '$',
            decimal: currency?.decimal_separator || '.',
            thousand: currency?.thousand_separator || ',',
            precision: currency?.decimal_places || 2
        });
    }
</script>
</body>

</html>