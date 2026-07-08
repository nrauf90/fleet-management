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
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="no-margin">
                                    <?php echo _l('advance_salary'); ?>
                                </h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <?php if (has_permission('salary', '', 'create')): ?>
                                    <a href="<?php echo admin_url('salary/add_advance'); ?>"
                                        class="btn btn-info pull-right display-block">
                                        <i class="fa fa-plus"></i>
                                        <?php echo _l('add_new', _l('advance_salary_request')); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php
                        $table_data = [
                            _l('staff_name'),
                            _l('amount'),
                            _l('request_date'),
                            _l('status'),
                            _l('reason'),
                            _l('approver'),
                            _l('approved_date'),
                            _l('options')
                        ];

                        render_datatable($table_data, 'advance_salary');
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    $(function () {
        initDataTable('.table-advance_salary', window.location.href, [0, 1, 2, 3, 4, 5, 6], [7]);
    });
</script>
<!-- Salary Module JS -->
<script type="text/javascript" src="<?php echo base_url('modules/salary/assets/js/salary.js'); ?>"></script>
</body>

</html>