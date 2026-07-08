<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="no-margin">
                                    <?php echo $title; ?>
                                </h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button" class="btn btn-default" onclick="printSalaryReport()">
                                    <i class="fa fa-print"></i> <?php echo _l('print'); ?>
                                </button>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default dropdown-toggle"
                                        data-toggle="dropdown">
                                        <i class="fa fa-download"></i> <?php echo _l('export'); ?> <span
                                            class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a href="#"
                                                onclick="exportSalaryData('pdf')"><?php echo _l('export_pdf'); ?></a>
                                        </li>
                                        <li><a href="#"
                                                onclick="exportSalaryData('excel')"><?php echo _l('export_excel'); ?></a>
                                        </li>
                                        <li><a href="#"
                                                onclick="exportSalaryData('csv')"><?php echo _l('export_csv'); ?></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <form method="get" action="<?php echo admin_url('salary/reports'); ?>">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="month"><?php echo _l('month'); ?></label>
                                        <input type="month" name="month" id="month" class="form-control"
                                            value="<?php echo $month; ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="staff_id"><?php echo _l('staff_member'); ?></label>
                                        <select name="staff_id" id="staff_id" class="selectpicker no-margin"
                                            data-width="100%">
                                            <option value=""><?php echo _l('all_staff'); ?></option>
                                            <?php foreach ($staff_members as $staff) { ?>
                                                <option value="<?php echo $staff['staffid']; ?>" <?php echo $staff_id == $staff['staffid'] ? 'selected' : ''; ?>>
                                                    <?php echo $staff['firstname'] . ' ' . $staff['lastname']; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="submit"
                                                class="btn btn-info"><?php echo _l('filter'); ?></button>
                                            <a href="<?php echo admin_url('salary/reports'); ?>"
                                                class="btn btn-default"><?php echo _l('clear'); ?></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="row">
            <div class="col-md-3">
                <div class="widget-card bg-primary">
                    <div class="widget-card-body">
                        <div class="widget-card-icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <div class="widget-card-content">
                            <h3><?php echo count($salary_data); ?></h3>
                            <p><?php echo _l('total_staff'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="widget-card bg-success">
                    <div class="widget-card-body">
                        <div class="widget-card-icon">
                            <i class="fa fa-money"></i>
                        </div>
                        <div class="widget-card-content">
                            <h3><?php echo app_format_money(array_sum(array_column($salary_data, 'current_salary')), $base_currency); ?>
                            </h3>
                            <p><?php echo _l('total_salary'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="widget-card bg-warning">
                    <div class="widget-card-body">
                        <div class="widget-card-icon">
                            <i class="fa fa-credit-card"></i>
                        </div>
                        <div class="widget-card-content">
                            <h3><?php echo app_format_money(array_sum(array_column($advance_data, 'amount')), $base_currency); ?>
                            </h3>
                            <p><?php echo _l('total_advance'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="widget-card bg-info">
                    <div class="widget-card-body">
                        <div class="widget-card-icon">
                            <i class="fa fa-chart-line"></i>
                        </div>
                        <div class="widget-card-content">
                            <h3><?php echo app_format_money(array_sum(array_column($salary_data, 'current_salary')) - array_sum(array_column($advance_data, 'amount')), $base_currency); ?>
                            </h3>
                            <p><?php echo _l('net_salary'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Salary Report -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('salary_report'); ?> -
                            <?php echo date('F Y', strtotime($month . '-01')); ?></h4>
                        <hr class="hr-panel-separator" />
                        <?php if (!empty($salary_data)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-salary">
                                    <thead>
                                        <tr>
                                            <th><?php echo _l('staff_name'); ?></th>
                                            <th><?php echo _l('initial_salary'); ?></th>
                                            <th><?php echo _l('current_salary'); ?></th>
                                            <th><?php echo _l('advance_salary'); ?></th>
                                            <th><?php echo _l('effective_date'); ?></th>
                                            <th><?php echo _l('net_salary'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($salary_data as $salary): ?>
                                            <tr>
                                                <td><?php echo $salary['full_name']; ?></td>
                                                <td><?php echo app_format_money($salary['initial_salary'], $base_currency); ?>
                                                </td>
                                                <td><?php echo app_format_money($salary['current_salary'], $base_currency); ?>
                                                </td>
                                                <td><?php echo app_format_money($salary['advance_salary'] ?? 0, $base_currency); ?>
                                                </td>
                                                <td><?php echo _d($salary['salary_effective_date']); ?></td>
                                                <td><?php echo app_format_money($salary['current_salary'] - ($salary['advance_salary'] ?? 0), $base_currency); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-primary text-white">
                                            <td><strong><?php echo _l('total'); ?></strong></td>
                                            <td><strong><?php echo app_format_money(array_sum(array_column($salary_data, 'initial_salary')), $base_currency); ?></strong>
                                            </td>
                                            <td><strong><?php echo app_format_money(array_sum(array_column($salary_data, 'current_salary')), $base_currency); ?></strong>
                                            </td>
                                            <td><strong><?php echo app_format_money(array_sum(array_column($salary_data, 'advance_salary')), $base_currency); ?></strong>
                                            </td>
                                            <td></td>
                                            <td><strong><?php echo app_format_money(array_sum(array_column($salary_data, 'current_salary')) - array_sum(array_column($salary_data, 'advance_salary')), $base_currency); ?></strong>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted"><?php echo _l('no_salary_data_found'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advance Salary Report -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('advance_salary_report'); ?> -
                            <?php echo date('F Y', strtotime($month . '-01')); ?></h4>
                        <hr class="hr-panel-separator" />
                        <?php if (!empty($advance_data)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-salary">
                                    <thead>
                                        <tr>
                                            <th><?php echo _l('staff_name'); ?></th>
                                            <th><?php echo _l('amount'); ?></th>
                                            <th><?php echo _l('request_date'); ?></th>
                                            <th><?php echo _l('status'); ?></th>
                                            <th><?php echo _l('reason'); ?></th>
                                            <th><?php echo _l('approver'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($advance_data as $advance): ?>
                                            <tr>
                                                <td><?php echo $advance['staff_name']; ?></td>
                                                <td><?php echo app_format_money($advance['amount'], $base_currency); ?></td>
                                                <td><?php echo _d($advance['request_date']); ?></td>
                                                <td><?php echo get_advance_status_badge($advance['status']); ?></td>
                                                <td><?php echo $advance['reason']; ?></td>
                                                <td><?php echo $advance['approver_name'] ?: '-'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-warning">
                                            <td><strong><?php echo _l('total'); ?></strong></td>
                                            <td><strong><?php echo app_format_money(array_sum(array_column($advance_data, 'amount')), $base_currency); ?></strong>
                                            </td>
                                            <td colspan="4"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted"><?php echo _l('no_advance_data_found'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    $(function () {
        // Initialize select picker
        $('.selectpicker').selectpicker();

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
</body>

</html>