<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$status = fleet_render_status_html($booking->id, 'booking', $booking->status, true);
$can_view_invoice = has_permission('invoices', '', 'view');
?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s accounting-template estimate">
          <div class="panel-body">
            <div class="row">
              <?php
              $currency_name = '';
              if (isset($base_currency)) {
                $currency_name = $base_currency->name;
              }
              $sub_total = 0;
              ?>

              <div class="invoice accounting-template">
                <div class="col-md-12 fr1">
                  <div class="col-12">
                    <a href="<?php echo admin_url('fleet/bookings'); ?>"
                      class="btn btn-default pull-right"><?php echo _l('close'); ?></a>
                    <a href="#"
                      onclick="update_info(<?php echo new_html_entity_decode($booking->id); ?>); return false;"
                      class="btn btn-info pull-right mright10"><?php echo _l('update_info'); ?></a>
                    <?php if (!$booking->invoice_id > 0) { ?>
                      <a href="#"
                        onclick="create_invoice(<?php echo new_html_entity_decode($booking->id); ?>); return false;"
                        id="btn-create-invoice"
                        class="btn btn-success pull-right mright10"><?php echo _l('create_invoice'); ?></a>
                    <?php } else { ?>
                      <a href="<?php echo admin_url('invoices#' . $booking->invoice_id); ?>"
                        class="btn pull-right"><?php echo _l('view_invoice'); ?></a>
                    <?php } ?>
                  </div>
                </div>
              </div>
              <div class="col-md-12">
                <hr>
              </div>
            </div>
            <h4 class="h4-color"><?php echo _l('general_info'); ?></h4>
            <hr class="hr-color">
            <div class="row">
              <div class="col-md-6">
                <table class="table table-striped  no-margin">
                  <tbody>
                    <?php echo form_hidden('_booking_id', $booking->id); ?>

                    <tr class="project-overview">
                      <td class="bold" width="30%"><?php echo _l('booking_number'); ?></td>
                      <td><?php echo new_html_entity_decode($booking->number); ?></td>
                    </tr>
                    <tr class="project-overview">
                      <td class="bold" width="30%"><?php echo _l('status'); ?></td>
                      <td><?php echo new_html_entity_decode($status); ?></td>
                    </tr>
                    <tr class="project-overview">
                      <td class="bold" width="30%"><?php echo _l('delivery_date'); ?></td>
                      <td><?php echo _d($booking->delivery_date); ?></td>
                    </tr>
                    <tr class="project-overview">
                      <td class="bold"><?php echo _l('receipt_address'); ?></td>
                      <td><?php echo new_html_entity_decode($booking->receipt_address); ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="col-md-6">
                <table class="table table-striped no-margin">
                  <tbody>
                    <tr class="project-overview">
                      <td class="bold" width="30%"><?php echo _l('customer'); ?></td>
                      <td><a
                          href="<?php echo admin_url('clients/client/' . $booking->userid) ?>"><?php echo get_company_name($booking->userid); ?></a>
                      </td>
                    </tr>
                    <tr class="project-overview">
                      <td class="bold" width="30%"><?php echo _l('delivery_date'); ?></td>
                      <td><?php echo _d($booking->delivery_date); ?></td>
                    </tr>
                    <tr class="project-overview">
                      <td class="bold" width="30%"><?php echo _l('phone'); ?></td>
                      <td><?php echo new_html_entity_decode($booking->phone); ?></td>
                    </tr>
                    <tr class="project-overview">
                      <td class="bold"><?php echo _l('delivery_address'); ?></td>
                      <td><?php echo new_html_entity_decode($booking->delivery_address); ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <h4 class="h4-color mtop25"><?php echo _l('admin_info'); ?></h4>
              </div>
              <?php if ($can_view_invoice) { ?>
              <div class="col-md-6">
                <h4 class="h4-color mtop25"><?php echo _l('financial_summary'); ?></h4>
              </div>
              <?php } ?>
            </div>
            <hr class="hr-color">
            <div class="row">
              <div class="col-md-6">
                <table class="table table-striped  no-margin">
                  <tbody>
                    <tr class="project-overview">
                      <td class="bold" width="30%"><?php echo _l('invoice'); ?></td>
                      <td><a
                          href="<?php echo admin_url('invoices/list_invoices/' . $booking->invoice_id) ?>"><?php echo format_invoice_number($booking->invoice_id); ?></a>
                      </td>
                    </tr>
                    <?php if ($can_view_invoice) { ?>
                    <tr class="project-overview">
                      <td class="bold" width="30%"><?php echo _l('amount'); ?></td>
                      <td><?php echo app_format_money($booking->amount, ''); ?></td>
                    </tr>
                    <?php } ?>
                    <tr class="project-overview">
                      <td class="bold" width="30%"><?php echo _l('fleet_importer'); ?></td>
                      <td><?php echo new_html_entity_decode(isset($booking->importer) ? $booking->importer : ''); ?></td>
                    </tr>
                    <tr class="project-overview">
                      <td class="bold" width="30%"><?php echo _l('importer_invoice_number'); ?></td>
                      <td><?php echo new_html_entity_decode(isset($booking->importer_invoice_number) ? $booking->importer_invoice_number : ''); ?></td>
                    </tr>
                    <tr class="project-overview">
                      <td class="bold" width="30%"><?php echo _l('fleet_exporter'); ?></td>
                      <td><?php echo new_html_entity_decode(isset($booking->exporter) ? $booking->exporter : ''); ?></td>
                    </tr>
                    <tr class="project-overview">
                      <td class="bold"><?php echo _l('admin_note'); ?></td>
                      <td><?php echo new_html_entity_decode($booking->admin_note); ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <?php if ($can_view_invoice) { ?>
              <div class="col-md-6">
                <table class="table table-striped no-margin">
                  <tbody>
                    <?php
                    // Calculate total from invoice items
                    $total_invoice_items = 0;
                    if (isset($invoice_items) && count($invoice_items) > 0) {
                      foreach ($invoice_items as $item) {
                        $total_invoice_items += $item['rate'] * $item['qty'];
                      }
                    }
                    ?>
                    <tr class="project-overview">
                      <td class="bold" width="30%"><?php echo _l('Total Invoice Items'); ?></td>
                      <td><?php echo app_format_money($total_invoice_items, ''); ?></td>
                    </tr>
                    <tr class="project-overview">
                      <td class="bold" width="30%"><?php echo _l('Total Used Cash'); ?></td>
                      <td><span class="text-danger">-<?php echo app_format_money($total_used_cash, ''); ?></span></td>
                    </tr>
                    <tr class="project-overview">
                      <td class="bold" width="30%"><?php echo _l('Total Rented Cash'); ?></td>
                      <td><span class="text-danger">-<?php echo app_format_money($total_rented_cash, ''); ?></span></td>
                    </tr>
                    <tr class="project-overview">
                      <td class="bold" width="30%"><?php echo _l('Profit/Loss'); ?></td>
                      <td>
                        <?php
                        $profit_loss = $total_invoice_items - $total_used_cash - $total_rented_cash;
                        $profit_loss_class = $profit_loss >= 0 ? 'text-success' : 'text-danger';
                        ?>
                        <span class="<?php echo $profit_loss_class; ?>">
                          <?php echo app_format_money($profit_loss, ''); ?>
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>

                <!-- Adjust Cash Button -->
                <div class="row mtop15">
                  <div class="col-md-12">
                    <button type="button" class="btn btn-warning"
                      onclick="adjust_cash(<?php echo $booking->id; ?>); return false;" id="btn-adjust-cash">
                      <i class="fa fa-money"></i> <?php echo _l('adjust_cash'); ?>
                    </button>
                    <small class="text-muted">
                      <?php echo _l('adjust_cash_help_text'); ?>
                    </small>
                  </div>
                </div>
              </div>
              <?php } ?>
            </div>

            <div class="row mtop25">
              <div class="col-md-12">
                <h4 class="h4-color"><?php echo _l('delivery_note'); ?></h4>
                <hr class="hr-color">
                <p class="text-muted mbot15"><?php echo _l('delivery_note_help_text'); ?></p>
                <div class="row">
                  <div class="col-md-6">
                    <?php echo render_input('dn_supplier', 'delivery_note_supplier', $delivery_note['supplier']); ?>
                  </div>
                  <div class="col-md-6">
                    <?php echo render_input('dn_customer', 'delivery_note_customer', $delivery_note['customer']); ?>
                  </div>
                  <div class="col-md-6">
                    <?php echo render_input('dn_contact_person', 'delivery_note_contact_person', $delivery_note['contact_person']); ?>
                  </div>
                  <div class="col-md-6">
                    <?php echo render_input('dn_invoice_no', 'delivery_note_invoice_no', $delivery_note['invoice_no']); ?>
                  </div>
                  <div class="col-md-6">
                    <?php echo render_date_input('dn_dispatch_date', 'delivery_note_dispatch_date', _d($delivery_note['dispatch_date'])); ?>
                  </div>
                  <div class="col-md-12">
                    <?php echo render_textarea('dn_destination', 'delivery_note_destination', $delivery_note['destination'], ['rows' => 2]); ?>
                  </div>
                </div>
                <div class="table-responsive">
                  <table class="table table-bordered" id="delivery-note-items">
                    <thead>
                      <tr>
                        <th width="5%"><?php echo _l('delivery_note_s_no'); ?></th>
                        <th width="45%"><?php echo _l('delivery_note_drivers_name'); ?></th>
                        <th width="22%"><?php echo _l('delivery_note_truck_no'); ?></th>
                        <th width="15%"><?php echo _l('delivery_note_packages'); ?></th>
                        <th width="13%"></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $dn_driver_options = '';
                      foreach ($drivers as $dn_driver) {
                          $dn_driver_options .= '<option value="' . $dn_driver['staffid'] . '">'
                              . html_escape(trim($dn_driver['firstname'] . ' ' . $dn_driver['lastname'])) . '</option>';
                      }

                      foreach ($delivery_note_items as $dn_item) { ?>
                        <tr class="dn-item-row">
                          <td class="dn-s-no"></td>
                          <td>
                            <select class="selectpicker dn-driver" data-width="100%" data-none-selected-text="<?php echo _l('driver'); ?>">
                              <option value=""></option>
                              <?php foreach ($drivers as $dn_driver) { ?>
                                <option value="<?php echo $dn_driver['staffid']; ?>" <?php echo ($dn_item['driver_id'] == $dn_driver['staffid']) ? 'selected' : ''; ?>>
                                  <?php echo html_escape(trim($dn_driver['firstname'] . ' ' . $dn_driver['lastname'])); ?>
                                </option>
                              <?php } ?>
                            </select>
                          </td>
                          <td><input type="text" class="form-control dn-truck-no" value="<?php echo html_escape($dn_item['truck_no']); ?>"></td>
                          <td><input type="number" min="0" class="form-control dn-packages" value="<?php echo (int) $dn_item['packages']; ?>"></td>
                          <td class="text-center">
                            <button type="button" class="btn btn-danger btn-icon" onclick="remove_delivery_note_row(this); return false;"><i class="fa fa-remove"></i></button>
                          </td>
                        </tr>
                      <?php } ?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="3" class="text-right bold"><?php echo _l('delivery_note_total_packages'); ?></td>
                        <td class="bold" id="delivery-note-total">0</td>
                        <td></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
                <div class="text-right">
                  <button type="button" class="btn btn-default" onclick="add_delivery_note_row(); return false;">
                    <i class="fa fa-plus"></i> <?php echo _l('delivery_note_add_row'); ?>
                  </button>
                  <?php if (has_permission('fleet_bookings', '', 'edit')) { ?>
                    <button type="button" class="btn btn-info" onclick="save_delivery_note_items(<?php echo (int) $booking->id; ?>); return false;">
                      <?php echo _l('submit'); ?>
                    </button>
                  <?php } ?>
                  <a href="<?php echo admin_url('fleet/delivery_note/' . $booking->id); ?>" target="_blank" class="btn btn-default">
                    <i class="fa fa-print"></i> <?php echo _l('print_delivery_note'); ?>
                  </a>
                </div>
              </div>
            </div>
            <script>
              var fleet_dn_driver_options_html = <?php echo json_encode($dn_driver_options); ?>;
              var fleet_driver_vehicle_map = <?php echo json_encode((object) $driver_vehicle_map); ?>;
            </script>

            <?php if ($can_view_invoice && isset($invoice_items) && count($invoice_items) > 0) { ?>
              <div class="row mtop25">
                <div class="col-md-12">
                  <h4 class="h4-color"><?php echo _l('invoice_items'); ?></h4>
                  <hr class="hr-color">
                  <div class="table-responsive">
                    <table class="table items items-preview">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th><?php echo _l('invoice_items_list_description'); ?></th>
                          <th><?php echo _l('invoice_item_long_description'); ?></th>
                          <th><?php echo _l('invoice_items_list_rate'); ?></th>
                          <th><?php echo _l('invoice_items_list_qty'); ?></th>
                          <th><?php echo _l('invoice_items_list_amount'); ?></th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $i = 1;
                        foreach ($invoice_items as $item) { ?>
                          <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo $item['description']; ?></td>
                            <td><?php echo $item['long_description']; ?></td>
                            <td><?php echo app_format_money($item['rate'], ''); ?></td>
                            <td><?php echo $item['qty']; ?></td>
                            <td><?php echo app_format_money($item['rate'] * $item['qty'], ''); ?></td>
                          </tr>
                          <?php
                          $i++;
                        } ?>
                      </tbody>
                    </table>
                    <div class="col-md-5 col-md-offset-7">
                      <table class="table text-right">
                        <tbody>
                          <tr>
                            <td>
                              <span class="text-danger  bold">
                                Amount Due </span>
                            </td>
                            <td>
                              <span class="text-danger ">
                                <?php echo app_format_money($total_invoice_items, ''); ?> </span>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            <?php } ?>

            <!-- Add Attachments Section -->
            <div class="row mtop25">
              <div class="col-md-12">
                <h4 class="h4-color"><?php echo _l('booking_attachments'); ?></h4>
                <hr class="hr-color">

                <div class="row attachments">
                  <div class="col-md-12">
                    <div class="attachments">
                      <div class="attachment">
                        <div class="mbot15">
                          <form action="<?php echo admin_url('fleet/upload_booking_attachment'); ?>" class="dropzone"
                            id="booking-attachments-upload"></form>
                        </div>
                      </div>
                      <div class="clearfix"></div>
                      <?php
                      $attachments = get_booking_attachments($booking->id);
                      if (count($attachments) > 0) { ?>
                        <div class="table-responsive">
                          <table class="table table-striped">
                            <thead>
                              <tr>
                                <th><?php echo _l('file_name'); ?></th>
                                <th><?php echo _l('file_date_uploaded'); ?></th>
                                <th><?php echo _l('file_uploaded_by'); ?></th>
                                <th><?php echo _l('options'); ?></th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php foreach ($attachments as $attachment) { ?>
                                <tr>
                                  <td>
                                    <?php echo $attachment['file_name']; ?>
                                  </td>
                                  <td><?php echo _dt($attachment['datecreated']); ?></td>
                                  <td><?php echo get_staff_full_name($attachment['addedfrom']); ?></td>
                                  <td>
                                    <a href="<?php echo admin_url('fleet/download_booking_attachment/' . $attachment['id']); ?>"
                                      class="btn btn-default btn-icon"><i class="fa fa-download"></i></a>
                                    <?php if (has_permission('fleet_bookings', '', 'delete')) { ?>
                                      <a href="<?php echo admin_url('fleet/delete_booking_attachment/' . $attachment['id']); ?>"
                                        class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                                    <?php } ?>
                                  </td>
                                </tr>
                              <?php } ?>
                            </tbody>
                          </table>
                        </div>
                      <?php } ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Attachments Section -->

            <?php if ($booking->rating != 0) { ?>
              <h4 class="h4-color mtop25"><?php echo _l('rating'); ?></h4>
              <hr class="hr-color">
              <div class="row">
                <div class="col-md-6">
                  <table class="table table-striped  no-margin">
                    <tbody>
                      <tr class="project-overview">
                        <td class="bold" width="30%"><?php echo _l('rating'); ?></td>
                        <td>
                          <div class="_star-rating">
                            <span class="fa fa-star margin-top-8" data-rating="1"></span>
                            <span class="fa fa-star margin-top-8" data-rating="2"></span>
                            <span class="fa fa-star margin-top-8" data-rating="3"></span>
                            <span class="fa fa-star margin-top-8" data-rating="4"></span>
                            <span class="fa fa-star margin-top-8" data-rating="5"></span>
                            <input type="hidden" name="rating" class="rating-value"
                              value="<?php echo new_html_entity_decode($booking->rating); ?>">
                          </div>
                        </td>
                      </tr>
                      <tr class="project-overview">
                        <td class="bold"><?php echo _l('rating_comments'); ?></td>
                        <td><?php echo new_html_entity_decode($booking->comments); ?></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            <?php } ?>

            <h4 class="h4-color mtop25"><?php echo _l('logbook'); ?></h4>
            <hr class="hr-color">
            <?php if (is_admin() || has_permission('fleet_work_performance', '', 'create')) { ?>
              <a href="#" class="btn btn-info add-new-logbook mbot15"><?php echo _l('add'); ?></a>
            <?php } ?>


            <table class="table table-logbook scroll-responsive">
              <thead>
                <tr>
                  <th><?php echo _l('name'); ?></th>
                  <th><?php echo _l('booking_number'); ?></th>
                  <th><?php echo _l('vehicle'); ?></th>
                  <th><?php echo _l('driver'); ?></th>
                  <th><?php echo _l('date'); ?></th>
                  <th><?php echo _l('in_hand_cash'); ?></th>
                  <th><?php echo _l('used_cash'); ?></th>
                  <th><?php echo _l('total_cash'); ?></th>
                  <th><?php echo _l('paid_cash'); ?></th>
                  <th><?php echo _l('status'); ?></th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="chosse" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">
              <span class="add-title"><?php echo _l('please_let_us_know_the_reason_for_canceling_the_order') ?></span>
            </h4>
          </div>
          <div class="modal-body">
            <div class="col-md-12">
              <?php echo render_textarea('cancel_reason', 'cancel_reason', ''); ?>
            </div>
          </div>
          <div class="clearfix">
            <br>
            <br>
            <div class="clearfix">
            </div>
            <div class="modal-footer">
              <button class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
              <button type="button" data-status="8"
                class="btn btn-danger cancell_order"><?php echo _l('cancell'); ?></button>
            </div>
          </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
      </div><!-- /.modal -->
    </div><!-- /.modal -->
    <?php $arrAtt = array();
    $arrAtt['data-type'] = 'currency';
    ?>
    <div class="modal fade" id="info-modal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><?php echo _l('update_info') ?></h4>
          </div>
          <?php echo form_open_multipart(admin_url('fleet/booking_update_info'), array('id' => 'info-form')); ?>
          <?php echo form_hidden('id', $booking->id); ?>

          <div class="modal-body">
            <?php echo render_input('amount', 'amount', $booking->amount, 'text', $arrAtt); ?>
            <?php echo render_textarea('importer', 'fleet_importer', isset($booking->importer) ? $booking->importer : ''); ?>
            <?php echo render_input('importer_invoice_number', 'importer_invoice_number', isset($booking->importer_invoice_number) ? $booking->importer_invoice_number : ''); ?>
            <?php echo render_textarea('exporter', 'fleet_exporter', isset($booking->exporter) ? $booking->exporter : ''); ?>
            <?php echo render_textarea('admin_note', 'admin_note', $booking->admin_note); ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-info btn-submit"><?php echo _l('submit'); ?></button>
          </div>
          <?php echo form_close(); ?>
        </div>
      </div>
    </div>

    <?php init_tail(); ?>
    </body>

    </html>
    <div class="modal fade" id="logbook-modal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><?php echo _l('logbook') ?></h4>
          </div>
          <?php echo form_open_multipart(admin_url('fleet/logbook'), array('id' => 'logbook-form')); ?>
          <?php echo form_hidden('id'); ?>

          <div class="modal-body">

            <?php //echo render_select('booking_id', $bookings, array('id', 'number'), 'booking', $booking->id); ?>
            <?php echo render_input('booking_id', '', $booking->id, 'hidden'); ?>
            <?php echo render_input('', 'Booking Id', $booking->number, 'text', ['disabled' => 'true']); ?>
            <?php echo render_select('vehicle_id', $vehicles, array('id', 'name', 'ownership'), 'vehicle'); ?>
            <?php echo render_select('driver_id', $drivers, array('staffid', array('firstname', 'lastname')), 'driver'); ?>
            <?php echo render_input('name', 'name'); ?>
            <?php echo render_input('odometer', 'odometer'); ?>
            <?php echo render_date_input('date', 'date'); ?>
            <?php echo render_textarea('description', 'description'); ?>
            <div class="own-vehicle-fields" style="display: none;">
              <?php echo render_input('hand_cash', 'hand_cash', '0.00', 'text', ['data-type' => 'currency']); ?>
              <?php echo render_input('used_cash', 'used_cash', '0.00', 'text', ['data-type' => 'currency']); ?>
              <?php echo render_select('paymentmode', $payment_modes, ['id', 'name'], 'payment_mode'); ?>
              <div class="logbook-transaction-field" style="display: none;">
                <?php echo render_input('transaction_id', 'logbook_transaction_id'); ?>
              </div>
            </div>
            <div class="rented-vehicle-fields" style="display: none;">
              <?php echo render_input('total_cash', 'total_cash', '0.00', 'text', ['data-type' => 'currency']); ?>
              <?php echo render_input('paid_cash', 'paid_cash', '0.00', 'text', ['data-type' => 'currency']); ?>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-info btn-submit"><?php echo _l('submit'); ?></button>
          </div>
          <?php echo form_close(); ?>
        </div>
      </div>
    </div>
    <?php require 'modules/fleet/assets/js/work_performances/logbook_manage_js.php'; ?>