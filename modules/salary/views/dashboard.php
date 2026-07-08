<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!-- Salary Module CSS -->
<link rel="stylesheet" type="text/css" href="<?php echo base_url('modules/salary/assets/css/salary.css'); ?>">
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo _l('salary_dashboard'); ?>
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="widget-card bg-primary">
                    <div class="widget-card-body">
                        <div class="widget-card-icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <div class="widget-card-content">
                            <h3><?php echo $stats['total_staff']; ?></h3>
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
                            <h3><?php echo app_format_money($stats['total_salary'], $base_currency ?? get_base_currency_fallback()); ?>
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
                            <i class="fa fa-clock-o"></i>
                        </div>
                        <div class="widget-card-content">
                            <h3><?php echo $stats['pending_advance']; ?></h3>
                            <p><?php echo _l('pending_advance_requests'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="widget-card bg-info">
                    <div class="widget-card-body">
                        <div class="widget-card-icon">
                            <i class="fa fa-credit-card"></i>
                        </div>
                        <div class="widget-card-content">
                            <h3><?php echo app_format_money($stats['monthly_advance'], $base_currency ?? get_base_currency_fallback()); ?>
                            </h3>
                            <p><?php echo _l('monthly_advance'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Salary Updates -->
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo _l('recent_salary_updates'); ?>
                        </h4>
                        <hr class="hr-panel-separator" />
                        <?php if (!empty($recent_salaries)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?php echo _l('staff_name'); ?></th>
                                            <th><?php echo _l('initial_salary'); ?></th>
                                            <th><?php echo _l('effective_date'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_salaries as $salary): ?>
                                            <tr>
                                                <td><?php echo $salary['staff_name']; ?></td>
                                                <td><?php echo app_format_money($salary['initial_salary'], $base_currency); ?>
                                                </td>
                                                <td><?php echo _d($salary['effective_date']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted"><?php echo _l('no_recent_salary_updates'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Advance Requests -->
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo _l('recent_advance_requests'); ?>
                        </h4>
                        <hr class="hr-panel-separator" />
                        <?php if (!empty($recent_advances)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?php echo _l('staff_name'); ?></th>
                                            <th><?php echo _l('amount'); ?></th>
                                            <th><?php echo _l('status'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($recent_advances, 0, 5) as $advance): ?>
                                            <tr>
                                                <td><?php echo $advance['staff_name']; ?></td>
                                                <td><?php echo app_format_money($advance['amount'], $base_currency); ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = '';
                                                    switch ($advance['status']) {
                                                        case 'pending':
                                                            $status_class = 'label-warning';
                                                            break;
                                                        case 'approved':
                                                            $status_class = 'label-success';
                                                            break;
                                                        case 'rejected':
                                                            $status_class = 'label-danger';
                                                            break;
                                                        case 'paid':
                                                            $status_class = 'label-info';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="label <?php echo $status_class; ?>">
                                                        <?php echo _l('advance_status_' . $advance['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted"><?php echo _l('no_recent_advance_requests'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo _l('quick_actions'); ?>
                        </h4>
                        <hr class="hr-panel-separator" />
                        <div class="row">
                            <div class="col-md-3">
                                <a href="<?php echo admin_url('salary/staff'); ?>" class="btn btn-info btn-block">
                                    <i class="fa fa-users"></i> <?php echo _l('manage_staff_salary'); ?>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="<?php echo admin_url('salary/advance'); ?>" class="btn btn-warning btn-block">
                                    <i class="fa fa-credit-card"></i> <?php echo _l('manage_advance_salary'); ?>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="<?php echo admin_url('salary/reports'); ?>" class="btn btn-success btn-block">
                                    <i class="fa fa-chart-bar"></i> <?php echo _l('view_reports'); ?>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="<?php echo admin_url('salary/settings'); ?>" class="btn btn-default btn-block">
                                    <i class="fa fa-cog"></i> <?php echo _l('settings'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .widget-card {
        border-radius: 8px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .widget-card-body {
        padding: 20px;
        display: flex;
        align-items: center;
    }

    .widget-card-icon {
        font-size: 2.5em;
        margin-right: 15px;
        opacity: 0.8;
    }

    .widget-card-content h3 {
        margin: 0;
        font-size: 1.8em;
        font-weight: bold;
    }

    .widget-card-content p {
        margin: 5px 0 0 0;
        opacity: 0.9;
    }

    .bg-primary {
        background-color: #007bff;
    }

    .bg-success {
        background-color: #28a745;
    }

    .bg-warning {
        background-color: #ffc107;
        color: #212529;
    }

    .bg-info {
        background-color: #17a2b8;
    }
</style>

<?php init_tail(); ?>
<!-- Salary Module JS -->
<script type="text/javascript" src="<?php echo base_url('modules/salary/assets/js/salary.js'); ?>"></script>