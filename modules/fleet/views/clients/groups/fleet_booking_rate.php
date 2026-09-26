<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (isset($client)) {
    $CI = &get_instance();
    $CI->load->model('fleet/fleet_model');
    $defaults = $CI->fleet_model->get_client_fleet_defaults($client->userid);
    $fixed_rate = $defaults && $defaults->fixed_rate > 0 ? (float) $defaults->fixed_rate : 0;
    $importer = $defaults && isset($defaults->importer) ? $defaults->importer : '';
    $exporter = $defaults && isset($defaults->exporter) ? $defaults->exporter : '';
    $receipt_address = $defaults && isset($defaults->receipt_address) ? $defaults->receipt_address : '';
    $delivery_address = $defaults && isset($defaults->delivery_address) ? $defaults->delivery_address : '';
    $arrAtt = ['data-type' => 'currency'];
?>
<h4 class="customer-profile-group-heading">
    <?php echo _l('fleet_booking_rate'); ?>
</h4>
<?php echo form_open(admin_url('fleet/save_client_booking_rate')); ?>
<?php echo form_hidden('client_id', $client->userid); ?>
<div class="row">
    <div class="col-md-6">
        <?php echo render_input('fixed_rate', 'fleet_fixed_booking_rate', $fixed_rate > 0 ? $fixed_rate : '', 'text', $arrAtt); ?>
        <p class="text-muted"><?php echo _l('fleet_booking_rate_help'); ?></p>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <?php echo render_textarea('importer', 'fleet_importer', $importer); ?>
        <p class="text-muted"><?php echo _l('fleet_importer_help'); ?></p>
    </div>
    <div class="col-md-6">
        <?php echo render_textarea('exporter', 'fleet_exporter', $exporter); ?>
        <p class="text-muted"><?php echo _l('fleet_exporter_help'); ?></p>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <?php echo render_textarea('receipt_address', 'receipt_address', $receipt_address); ?>
        <p class="text-muted"><?php echo _l('fleet_receipt_address_help'); ?></p>
    </div>
    <div class="col-md-6">
        <?php echo render_textarea('delivery_address', 'delivery_address', $delivery_address); ?>
        <p class="text-muted"><?php echo _l('fleet_delivery_address_help'); ?></p>
    </div>
</div>
<div class="text-right">
    <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
</div>
<?php echo form_close(); ?>
<?php } ?>
