<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <h4 class="no-margin pull-left"><?php echo _l('accounts_suppliers'); ?></h4>
                            <?php if (has_permission('accounts', '', 'create') || is_admin()) { ?>
                            <a href="#" class="btn btn-info pull-right" onclick="add_supplier(); return false;">
                                <?php echo _l('accounts_add_supplier'); ?>
                            </a>
                            <?php } ?>
                            <div class="clearfix"></div>
                        </div>
                        <hr class="hr-panel-heading">

                        <table class="table dt-table scroll-responsive">
                            <thead>
                                <tr>
                                    <th><?php echo _l('accounts_supplier_name'); ?></th>
                                    <th><?php echo _l('accounts_total_credits'); ?></th>
                                    <th><?php echo _l('accounts_total_debits'); ?></th>
                                    <th><?php echo _l('accounts_balance'); ?></th>
                                    <th><?php echo _l('options'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($suppliers as $supplier) { ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo admin_url('accounts/supplier/' . $supplier['id']); ?>">
                                            <?php echo e($supplier['name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo app_format_money($supplier['credits'], $base_currency); ?></td>
                                    <td><?php echo app_format_money($supplier['debits'], $base_currency); ?></td>
                                    <td>
                                        <?php $balance = $supplier['credits'] - $supplier['debits']; ?>
                                        <span class="<?php echo $balance >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo app_format_money($balance, $base_currency); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (has_permission('accounts', '', 'edit') || is_admin()) { ?>
                                        <a href="#" onclick='edit_supplier(<?php echo (int) $supplier['id']; ?>, <?php echo json_encode($supplier['name']); ?>); return false;'>
                                            <?php echo _l('edit'); ?>
                                        </a>
                                        <?php } ?>
                                        <?php if (has_permission('accounts', '', 'delete') || is_admin()) { ?>
                                        | <a href="<?php echo admin_url('accounts/delete_supplier/' . $supplier['id']); ?>" class="text-danger _delete">
                                            <?php echo _l('delete'); ?>
                                        </a>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="supplier-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('accounts/save_supplier'), ['id' => 'supplier-form']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title"><?php echo _l('accounts_edit_supplier'); ?></span>
                    <span class="add-title"><?php echo _l('accounts_add_supplier'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <?php echo form_hidden('id'); ?>
                <?php echo render_input('name', 'accounts_supplier_name'); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php init_tail(); ?>
</body>
</html>
