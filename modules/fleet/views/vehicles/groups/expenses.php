<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (isset($vehicle)) { ?>
<h4 class="customer-profile-group-heading"><?php echo _l('vehicle_expenses'); ?></h4>
<?php if (isset($vehicle_expenses) && count($vehicle_expenses) > 0) { ?>
<table class="table table-expenses scroll-responsive">
   <thead>
      <tr>
         <th><?php echo _l('expense_dt_table_heading_date'); ?></th>
         <th><?php echo _l('expense_dt_table_heading_category'); ?></th>
         <th><?php echo _l('expense_name'); ?></th>
         <th><?php echo _l('reference_no'); ?></th>
         <th><?php echo _l('expense_dt_table_heading_amount'); ?></th>
      </tr>
   </thead>
   <tbody>
      <?php
      $total = 0;
      foreach ($vehicle_expenses as $expense) {
          $total += $expense['amount'];
          ?>
      <tr>
         <td><?php echo _d($expense['date']); ?></td>
         <td><?php echo e($expense['category_name']); ?></td>
         <td>
            <a href="<?php echo admin_url('expenses/list_expenses/' . $expense['id']); ?>">
               <?php echo e($expense['expense_name']); ?>
            </a>
         </td>
         <td><?php echo e($expense['reference_no']); ?></td>
         <td><?php echo app_format_money($expense['amount'], $currency_name); ?></td>
      </tr>
      <?php } ?>
   </tbody>
   <tfoot>
      <tr>
         <td colspan="4" class="tw-text-right tw-font-semibold"><?php echo _l('fleet_total_expenses'); ?></td>
         <td class="tw-font-semibold"><?php echo app_format_money($total, $currency_name); ?></td>
      </tr>
   </tfoot>
</table>
<?php } else { ?>
<p class="text-muted"><?php echo _l('vehicle_no_expenses'); ?></p>
<?php } ?>
<?php } ?>
