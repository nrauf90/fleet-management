<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<p class="text-muted"><?php echo _l('fuel_routes_help'); ?></p>
<div>
	<a href="#" class="btn btn-info add-new-fuel-route mbot15"><?php echo _l('new_fuel_route'); ?></a>
</div>
<div class="row">
	<div class="col-md-12">
		<?php 
			$table_data = array(
        _l('id'),
				_l('fuel_route_name'),
        _l('fuel_route_amount'),
        _l('addedfrom'),
				_l('datecreated'),
				);
			render_datatable($table_data,'fuel-routes');
		?>
	</div>
</div>
<div class="clearfix"></div>
<div class="modal fade" id="fuel-route-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">
          <span class="add-title"><?php echo _l('new_fuel_route'); ?></span>
          <span class="edit-title hide"><?php echo _l('edit_fuel_route'); ?></span>
        </h4>
      </div>
      <?php echo form_open(admin_url('fleet/add_fuel_route'),array('id'=>'fuel-route-form'));?>
      <?php echo form_hidden('id'); ?>
      <div class="modal-body">
        <?php echo render_input('name','fuel_route_name','','text',array('placeholder'=>_l('fuel_route_name_placeholder'))); ?>
        <?php echo render_input('amount','fuel_route_amount','','number',array('step'=>'any','min'=>'0')); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="submit" class="btn btn-info btn-submit"><?php echo _l('submit'); ?></button>
      </div>
      <?php echo form_close(); ?>  
    </div>
  </div>
</div>
<?php
// fleet.php is owned by another task; register this tab's JS on the footer
// hook from here (views render before init_tail() runs app_admin_footer).
if (!function_exists('fleet_fuel_routes_footer_components')) {
    function fleet_fuel_routes_footer_components()
    {
        echo '<script src="' . module_dir_url(FLEET_MODULE_NAME, 'assets/js/settings/fuel_routes.js') . '"></script>';
    }
}
hooks()->add_action('app_admin_footer', 'fleet_fuel_routes_footer_components');
?>
