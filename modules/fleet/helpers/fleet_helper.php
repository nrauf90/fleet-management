<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Handles upload for driver documents
 * @param  mixed $id expense id
 * @return void
 */
function handle_driver_document_attachments($id)
{
    if (isset($_FILES['file']) && _perfex_upload_error($_FILES['file']['error'])) {
        header('HTTP/1.0 400 Bad error');
        echo _perfex_upload_error($_FILES['file']['error']);
        die;
    }
    $path = FLEET_MODULE_UPLOAD_FOLDER . '/driver_documents/' . $id . '/';
    $CI = &get_instance();

    if (isset($_FILES['file']['name'])) {

        // Get the temp file path
        $tmpFilePath = $_FILES['file']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);
            $filename = $_FILES['file']['name'];
            $newFilePath = $path . $filename;
            // Upload the file into the temp dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $attachment = [];
                $attachment[] = [
                    'file_name' => $filename,
                    'filetype' => $_FILES['file']['type'],
                ];

                $CI->misc_model->add_attachment_to_database($id, 'fle_driver_document', $attachment);
            }
        }
    }
}


/**
 * render booking status html
 * @param  [type]  $id           
 * @param  [type]  $type         
 * @param  string  $status_value 
 * @param  boolean $ChangeStatus 
 * @return [type]                
 */
function fleet_render_status_html($id, $type, $status_value = '', $ChangeStatus = true)
{
    $status = '';
    $statuses = [];
    if ($type == 'booking') {
        $statuses = fleet_booking_status();
    } else if ($type == 'logbook') {
        $statuses = fleet_logbook_status();
    } else if ($type == 'work_order') {
        $statuses = fleet_work_order_status();
    } else {
        $statuses = fleet_booking_status();
    }

    foreach ($statuses as $s) {
        if ($s['id'] == $status_value) {
            $status = $s;
            break;
        }
    }

    $outputStatus = '';

    $outputStatus .= '<span class="inline-block label" style="color:' . $status['color'] . ';border:1px solid ' . $status['color'] . '" task-status-table="' . $status_value . '">';
    $outputStatus .= $status['name'];
    $canChangeStatus = (has_permission('service_management', '', 'edit') || is_admin());

    if ($canChangeStatus && $ChangeStatus) {
        $outputStatus .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
        $outputStatus .= '<a href="#" class="dropdown-toggle text-dark dropdown-font-size" id="tableTaskStatus-' . $id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
        $outputStatus .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';
        $outputStatus .= '</a>';

        $outputStatus .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableTaskStatus-' . $id . '">';
        foreach ($statuses as $taskChangeStatus) {
            if ($status_value != $taskChangeStatus['id']) {
                $outputStatus .= '<li>
                <a href="#" onclick="' . $type . '_status_mark_as(\'' . $taskChangeStatus['id'] . '\',' . $id . '); return false;">
                ' . _l('task_mark_as', $taskChangeStatus['name']) . '
                </a>
                </li>';
            }
        }
        $outputStatus .= '</ul>';
        $outputStatus .= '</div>';
    }

    $outputStatus .= '</span>';

    return $outputStatus;
}

/**
 * booking status
 * @param  string $status 
 * @return [type]         
 */
function fleet_booking_status()
{
    $statuses = [

        [
            'id' => 'new',
            'color' => '#2196f3',
            'name' => _l('new'),
            'order' => 1,
            'filter_default' => false,
        ],
        [
            'id' => 'approved',
            'color' => '#3db8da',
            'name' => _l('approved'),
            'order' => 2,
            'filter_default' => false,
        ],
        [
            'id' => 'rejected',
            'color' => '#4caf50',
            'name' => _l('rejected'),
            'order' => 3,
            'filter_default' => true,
        ],
        [
            'id' => 'processing',
            'color' => '#3b82f6',
            'name' => _l('processing'),
            'order' => 4,
            'filter_default' => false,
        ],
        [
            'id' => 'complete',
            'color' => '#84c529',
            'name' => _l('complete'),
            'order' => 5,
            'filter_default' => false,
        ],
        [
            'id' => 'cancelled',
            'color' => '#d71a1a',
            'name' => _l('cancelled'),
            'order' => 6,
            'filter_default' => false,
        ],

    ];

    usort($statuses, function ($a, $b) {
        return $a['order'] - $b['order'];
    });

    return $statuses;
}


/**
 * logbook status
 * @param  string $status 
 * @return [type]         
 */
function fleet_logbook_status()
{

    $statuses = [

        [
            'id' => 'new',
            'color' => '#2196f3',
            'name' => _l('new'),
            'order' => 1,
            'filter_default' => false,
        ],
        [
            'id' => 'processing',
            'color' => '#3b82f6',
            'name' => _l('processing'),
            'order' => 2,
            'filter_default' => false,
        ],
        [
            'id' => 'complete',
            'color' => '#84c529',
            'name' => _l('complete'),
            'order' => 3,
            'filter_default' => false,
        ],
        [
            'id' => 'cancelled',
            'color' => '#d71a1a',
            'name' => _l('cancelled'),
            'order' => 4,
            'filter_default' => false,
        ],
        [
            'id' => 'adjusted',
            'color' => '#84c529',
            'name' => _l('adjusted'),
            'order' => 5,
            'filter_default' => false,
        ],

    ];

    usort($statuses, function ($a, $b) {
        return $a['order'] - $b['order'];
    });

    return $statuses;
}

/**
 * work_order status
 * @param  string $status 
 * @return [type]         
 */
function fleet_work_order_status()
{

    $statuses = [

        [
            'id' => 'open',
            'color' => '#2196f3',
            'name' => _l('open'),
            'order' => 1,
            'filter_default' => false,
        ],
        [
            'id' => 'in_progress',
            'color' => '#3b82f6',
            'name' => _l('in_progress'),
            'order' => 2,
            'filter_default' => false,
        ],
        [
            'id' => 'parts_ordered',
            'color' => '#ffa500',
            'name' => _l('parts_ordered'),
            'order' => 3,
            'filter_default' => false,
        ],
        [
            'id' => 'complete',
            'color' => '#84c529',
            'name' => _l('complete'),
            'order' => 4,
            'filter_default' => false,
        ],

    ];

    usort($statuses, function ($a, $b) {
        return $a['order'] - $b['order'];
    });

    return $statuses;
}

/**
 * [new_html_entity_decode description]
 * @param  [type] $str [description]
 * @return [type]      [description]
 */
if (!function_exists('new_html_entity_decode')) {

    function new_html_entity_decode($str)
    {
        return html_entity_decode($str ?? '');
    }
}


/**
 * Gets the part name by id.
 *
 * @param        $id   The id
 */
if (!function_exists('fleet_get_part_name_by_id')) {
    function fleet_get_part_name_by_id($id)
    {
        $CI = &get_instance();
        $CI->load->model('fleet/fleet_model');
        $part = $CI->fleet_model->get_part($id);

        if ($part) {
            return $part->name;
        }

        return '';
    }
}

/**
 * Gets the part type name by id.
 *
 * @param        $id   The id
 */
if (!function_exists('fleet_get_part_type_name_by_id')) {
    function fleet_get_part_type_name_by_id($id)
    {
        $CI = &get_instance();
        $CI->load->model('fleet/fleet_model');
        $part = $CI->fleet_model->get_data_part_types($id);

        if ($part) {
            return $part->name;
        }

        return '';
    }
}

/**
 * Gets the part group name by id.
 *
 * @param        $id   The id
 */
if (!function_exists('fleet_get_part_group_name_by_id')) {
    function fleet_get_part_group_name_by_id($id)
    {
        $CI = &get_instance();
        $CI->load->model('fleet/fleet_model');
        $part = $CI->fleet_model->get_data_part_groups($id);

        if ($part) {
            return $part->name;
        }

        return '';
    }
}


/**
 * Gets the vehicle name by id.
 *
 * @param        $id   The id
 */
if (!function_exists('fleet_get_vehicle_name_by_id')) {
    function fleet_get_vehicle_name_by_id($id)
    {
        $CI = &get_instance();
        $CI->load->model('fleet/fleet_model');
        $part = $CI->fleet_model->get_vehicle($id);

        if ($part) {
            return $part->name;
        }

        return '';
    }
}

/**
 * Gets the vehicle group name by id.
 *
 * @param        $id   The id
 */
if (!function_exists('fleet_get_vehicle_group_name_by_id')) {
    function fleet_get_vehicle_group_name_by_id($id)
    {
        $CI = &get_instance();
        $CI->load->model('fleet/fleet_model');
        $vehicle = $CI->fleet_model->get_data_vehicle_groups($id);

        if ($vehicle) {
            return $vehicle->name;
        }

        return '';
    }
}

/**
 * Gets the vehicle group name by id.
 *
 * @param        $id   The id
 */
if (!function_exists('fleet_get_vehicle_type_name_by_id')) {
    function fleet_get_vehicle_type_name_by_id($id)
    {
        $CI = &get_instance();
        $CI->load->model('fleet/fleet_model');
        $vehicle = $CI->fleet_model->get_data_vehicle_types($id);

        if ($vehicle) {
            return $vehicle->name;
        }

        return '';
    }
}

/**
 * Gets the vehicle group name by id.
 *
 * @param        $id   The id
 */
if (!function_exists('fleet_get_vehicle_current_meter')) {
    function fleet_get_vehicle_current_meter($id)
    {
        $CI = &get_instance();
        $CI->load->model('fleet/fleet_model');
        $current_meter = $CI->fleet_model->get_vehicle_current_meter($id);

        if ($current_meter) {
            return $current_meter;
        }

        return '';
    }
}

/**
 * Gets the vehicle group name by id.
 *
 * @param        $id   The id
 */
if (!function_exists('fleet_get_vehicle_current_meter_date')) {
    function fleet_get_vehicle_current_meter_date($id)
    {
        $CI = &get_instance();
        $CI->load->model('fleet/fleet_model');
        $current_meter_date = $CI->fleet_model->get_vehicle_current_meter_date($id);

        if ($current_meter_date) {
            return $current_meter_date;
        }

        return '';
    }
}


/**
 * Gets the vehicle group name by id.
 *
 * @param        $id   The id
 */
if (!function_exists('fleet_get_vehicle_current_operator')) {
    function fleet_get_vehicle_current_operator($id)
    {
        $CI = &get_instance();
        $CI->load->model('fleet/fleet_model');
        $current_operator = $CI->fleet_model->get_vehicle_current_operator($id);

        if ($current_operator) {
            return get_staff_full_name($current_operator);
        }

        return '';
    }
}

/**
 * get status modules wh
 * @param  string $module_name 
 * @return boolean             
 */
if (!function_exists('fleet_get_status_modules')) {
    function fleet_get_status_modules($module_name)
    {
        $CI = &get_instance();

        $sql = 'select * from ' . db_prefix() . 'modules where module_name = "' . $module_name . '" AND active =1 ';
        $module = $CI->db->query($sql)->row();
        if ($module) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * Get booking attachments
 * @param  integer $booking_id
 * @return array
 */
function get_booking_attachments($booking_id)
{
    $CI = &get_instance();
    $CI->load->model('fleet/fleet_model');
    return $CI->fleet_model->get_booking_attachments($booking_id);
}

/**
 * Whether invoice should use fleet left-panel layout (parties + billing under invoice number).
 * @param  object $invoice
 * @return bool
 */
function fleet_invoice_uses_custom_layout($invoice)
{
    if (!$invoice) {
        return false;
    }

    if (!empty($invoice->from_fleet)) {
        return true;
    }

    if (!empty(trim((string) ($invoice->importer ?? ''))) || !empty(trim((string) ($invoice->exporter ?? '')))) {
        return true;
    }

    return !empty(fleet_get_invoice_driver_vehicle_rows($invoice));
}

/**
 * Get driver + vehicle rows for a fleet-linked invoice.
 * @param  object $invoice
 * @return array<int, array{driver: string, vehicle: string}>
 */
function fleet_get_invoice_driver_vehicle_rows($invoice)
{
    if (empty($invoice->from_fleet)) {
        return [];
    }

    $CI = &get_instance();
    $CI->load->model('fleet/fleet_model');
    $booking = $CI->fleet_model->get_booking_by_invoice_id($invoice->id);

    if (!$booking) {
        return [];
    }

    return $CI->fleet_model->get_booking_driver_vehicle_rows($booking->id);
}

/**
 * Get driver names for a fleet invoice
 * @param  object $invoice
 * @return array
 */
function fleet_get_invoice_driver_names($invoice)
{
    $rows = fleet_get_invoice_driver_vehicle_rows($invoice);

    return array_column($rows, 'driver');
}

/**
 * Build importer/exporter, billing/shipping, and driver rows block for invoice views.
 * @param  object $invoice
 * @param  string $context html|pdf
 * @return string
 */
function fleet_build_invoice_parties_block($invoice, $context = 'html')
{
    if (!$invoice) {
        return '';
    }

    $importer = trim((string) ($invoice->importer ?? ''));
    $exporter = trim((string) ($invoice->exporter ?? ''));
    $driver_rows = fleet_get_invoice_driver_vehicle_rows($invoice);
    $show_shipping = $invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1;

    if ($importer === '' && $exporter === '' && !$show_shipping && empty($driver_rows)) {
        // Still show billing block for custom layout fleet invoices
        if (!fleet_invoice_uses_custom_layout($invoice)) {
            return '';
        }
    }

    $is_pdf = $context === 'pdf';
    $html = '';

    if ($is_pdf) {
        $html .= '<table cellpadding="2"><tr>';
        $html .= '<td width="50%" valign="top"><b>' . _l('invoice_importer') . ':</b><br />' . ($importer !== '' ? nl2br(html_escape($importer)) : '&mdash;') . '</td>';
        $html .= '<td width="50%" valign="top"><b>' . _l('invoice_exporter') . ':</b><br />' . ($exporter !== '' ? nl2br(html_escape($exporter)) : '&mdash;') . '</td>';
        $html .= '</tr></table><br />';

        $html .= '<table cellpadding="2"><tr>';
        $html .= '<td width="50%" valign="top"><b>' . _l('invoice_bill_to') . ':</b><div style="color:#424242;">' . format_customer_info($invoice, 'invoice', 'billing') . '</div></td>';
        if ($show_shipping) {
            $html .= '<td width="50%" valign="top"><b>' . _l('ship_to') . ':</b><div style="color:#424242;">' . format_customer_info($invoice, 'invoice', 'shipping') . '</div></td>';
        } else {
            $html .= '<td width="50%" valign="top">&nbsp;</td>';
        }
        $html .= '</tr></table>';

        if (!empty($driver_rows)) {
            $html .= '<br /><b>' . _l('invoice_drivers') . ':</b><br />';
            foreach ($driver_rows as $row) {
                $html .= html_escape(_l('invoice_driver')) . ': ' . html_escape($row['driver']);
                $html .= ' | ' . html_escape(_l('fleet_truck_number')) . ': ' . html_escape($row['vehicle'] !== '' ? $row['vehicle'] : '—');
                $html .= '<br />';
            }
        }

        return $html;
    }

    $html .= '<div class="fleet-invoice-left-panel mtop15">';
    $html .= '<div class="row">';
    $html .= '<div class="col-md-6 col-sm-6"><p class="fleet-invoice-parties-label bold">' . html_escape(_l('invoice_importer')) . '</p><div class="tw-text-neutral-500">' . ($importer !== '' ? nl2br(html_escape($importer)) : '&mdash;') . '</div></div>';
    $html .= '<div class="col-md-6 col-sm-6"><p class="fleet-invoice-parties-label bold">' . html_escape(_l('invoice_exporter')) . '</p><div class="tw-text-neutral-500">' . ($exporter !== '' ? nl2br(html_escape($exporter)) : '&mdash;') . '</div></div>';
    $html .= '</div>';
    $html .= '<div class="row mtop10">';
    $html .= '<div class="col-md-6 col-sm-6"><p class="fleet-invoice-parties-label bold">' . html_escape(_l('invoice_bill_to')) . '</p><address class="tw-text-neutral-500">' . format_customer_info($invoice, 'invoice', 'billing', true) . '</address></div>';
    if ($show_shipping) {
        $html .= '<div class="col-md-6 col-sm-6"><p class="fleet-invoice-parties-label bold">' . html_escape(_l('ship_to')) . '</p><address class="tw-text-neutral-500">' . format_customer_info($invoice, 'invoice', 'shipping') . '</address></div>';
    }
    $html .= '</div>';

    if (!empty($driver_rows)) {
        $html .= '<div class="row mtop10"><div class="col-md-12"><p class="fleet-invoice-parties-label bold">' . html_escape(_l('invoice_drivers')) . '</p>';
        foreach ($driver_rows as $row) {
            $html .= '<p class="fleet-invoice-driver-row tw-mb-0 tw-text-neutral-500">';
            $html .= '<span class="bold">' . html_escape(_l('invoice_driver')) . ':</span> ' . html_escape($row['driver']);
            $html .= ' &nbsp;|&nbsp; ';
            $html .= '<span class="bold">' . html_escape(_l('fleet_truck_number')) . ':</span> ' . html_escape($row['vehicle'] !== '' ? $row['vehicle'] : '—');
            $html .= '</p>';
        }
        $html .= '</div></div>';
    }

    $html .= '</div>';

    return $html;
}

/**
 * Render fleet invoice parties block under invoice number (left column).
 * @param  object $invoice
 * @return void
 */
function fleet_invoice_left_panel_layout_action($invoice)
{
    if (!$invoice || !fleet_invoice_uses_custom_layout($invoice)) {
        return;
    }

    echo fleet_build_invoice_parties_block($invoice, 'html');
}

/**
 * Append parties block to PDF organization column.
 * @param  string $organization_info
 * @param  object $invoice
 * @return string
 */
function fleet_invoice_pdf_organization_filter($organization_info, $invoice = null)
{
    if (!$invoice || !fleet_invoice_uses_custom_layout($invoice)) {
        return $organization_info;
    }

    $block = fleet_build_invoice_parties_block($invoice, 'pdf');
    if ($block === '') {
        return $organization_info;
    }

    return $organization_info . '<br /><br />' . $block;
}

/**
 * Keep only dates and metadata on PDF right column when using custom layout.
 * @param  string $invoice_info
 * @param  object $invoice
 * @return string
 */
function fleet_invoice_pdf_info_layout_filter($invoice_info, $invoice = null)
{
    if (!$invoice || !fleet_invoice_uses_custom_layout($invoice)) {
        return $invoice_info;
    }

    $date_label = preg_quote(_l('invoice_data_date'), '/');
    if (preg_match('/(' . $date_label . '.*)$/s', $invoice_info, $matches)) {
        $invoice_info = $matches[1];
    }

    $importer_invoice_number = trim((string) ($invoice->importer_invoice_number ?? ''));

    return '<b>' . _l('importer_invoice_number') . '</b><br />'
        . ($importer_invoice_number !== '' ? html_escape($importer_invoice_number) : '&mdash;')
        . '<br /><br />' . $invoice_info;
}

/**
 * Append drivers to fleet invoice PDF info block
 * @param  string $invoice_info
 * @param  object $invoice
 * @return string
 */
function fleet_invoice_drivers_pdf_filter($invoice_info, $invoice = null)
{
    if (!$invoice || fleet_invoice_uses_custom_layout($invoice)) {
        return $invoice_info;
    }

    $names = fleet_get_invoice_driver_names($invoice);

    if (empty($names)) {
        return $invoice_info;
    }

    $invoice_info .= _l('invoice_drivers') . ': ' . implode(', ', array_map('html_escape', $names)) . '<br />';

    return $invoice_info;
}

/**
 * Render drivers on fleet invoice HTML views
 * @param  object $invoice
 * @return void
 */
function fleet_invoice_drivers_html_action($invoice)
{
    if (!$invoice || fleet_invoice_uses_custom_layout($invoice)) {
        return;
    }

    $names = fleet_get_invoice_driver_names($invoice);

    if (empty($names)) {
        return;
    }

    $drivers = implode(', ', array_map('html_escape', $names));

    if (defined('CLIENTS_AREA') && CLIENTS_AREA) {
        echo '<p class="tw-mb-0 tw-text-normal">';
        echo '<span class="tw-font-medium tw-text-neutral-700">' . html_escape(_l('invoice_drivers')) . ':</span> ';
        echo $drivers;
        echo '</p>';
    } else {
        echo '<p class="no-mbot">';
        echo '<span class="bold">' . html_escape(_l('invoice_drivers')) . ':</span> ';
        echo $drivers;
        echo '</p>';
    }
}

/**
 * Get global predefined invoice client note and terms from Perfex settings.
 * @return array{clientnote: string, terms: string}
 */
function fleet_get_predefined_invoice_notes()
{
    return [
        'clientnote' => clear_textarea_breaks(get_option('predefined_clientnote_invoice')),
        'terms'      => clear_textarea_breaks(get_option('predefined_terms_invoice')),
    ];
}

/**
 * Clear fleet booking invoice link when an invoice is deleted.
 * @param  integer $invoice_id
 * @return void
 */
function fleet_on_invoice_deleted($invoice_id)
{
    $CI = &get_instance();
    $CI->load->model('fleet/fleet_model');
    $CI->fleet_model->clear_booking_invoice_relation($invoice_id);
}

/**
 * Render importer/exporter fields on invoice create/edit form.
 * @param  object|false $invoice
 * @return void
 */
function fleet_invoice_importer_exporter_form_fields($invoice)
{
    $importer = '';
    $exporter = '';
    $importer_invoice_number = '';

    if ($invoice && is_object($invoice)) {
        $importer = isset($invoice->importer) ? $invoice->importer : '';
        $exporter = isset($invoice->exporter) ? $invoice->exporter : '';
        $importer_invoice_number = isset($invoice->importer_invoice_number) ? $invoice->importer_invoice_number : '';
    }

    echo '<div id="fleet-invoice-parties-fields" class="row">';
    echo '<div class="col-md-6">';
    echo render_textarea('importer', 'invoice_importer', $importer);
    echo '</div>';
    echo '<div class="col-md-6">';
    echo render_textarea('exporter', 'invoice_exporter', $exporter);
    echo '</div>';
    echo '<div class="col-md-12">';
    echo render_input('importer_invoice_number', 'importer_invoice_number', $importer_invoice_number);
    echo '</div>';
    echo '</div>';
}

/**
 * Append importer/exporter to invoice PDF info block (legacy non-custom layout).
 * @param  string $invoice_info
 * @param  object $invoice
 * @return string
 */
function fleet_invoice_importer_exporter_pdf_filter($invoice_info, $invoice = null)
{
    if (!$invoice || fleet_invoice_uses_custom_layout($invoice)) {
        return $invoice_info;
    }

    $importer = isset($invoice->importer) ? trim((string) $invoice->importer) : '';
    $exporter = isset($invoice->exporter) ? trim((string) $invoice->exporter) : '';
    $importer_invoice_number = isset($invoice->importer_invoice_number) ? trim((string) $invoice->importer_invoice_number) : '';

    if ($importer !== '') {
        $invoice_info .= _l('invoice_importer') . ': ' . html_escape($importer) . '<br />';
    }

    if ($exporter !== '') {
        $invoice_info .= _l('invoice_exporter') . ': ' . html_escape($exporter) . '<br />';
    }

    if ($importer_invoice_number !== '') {
        $invoice_info .= _l('importer_invoice_number') . ': ' . html_escape($importer_invoice_number) . '<br />';
    }

    return $invoice_info;
}

/**
 * Render importer/exporter on invoice HTML views (legacy non-custom layout).
 * @param  object $invoice
 * @return void
 */
function fleet_invoice_importer_exporter_html_action($invoice)
{
    if (!$invoice || fleet_invoice_uses_custom_layout($invoice)) {
        return;
    }

    $importer = isset($invoice->importer) ? trim((string) $invoice->importer) : '';
    $exporter = isset($invoice->exporter) ? trim((string) $invoice->exporter) : '';
    $importer_invoice_number = isset($invoice->importer_invoice_number) ? trim((string) $invoice->importer_invoice_number) : '';

    if ($importer === '' && $exporter === '' && $importer_invoice_number === '') {
        return;
    }

    $is_client = defined('CLIENTS_AREA') && CLIENTS_AREA;

    if ($importer !== '') {
        if ($is_client) {
            echo '<p class="tw-mb-0 tw-text-normal">';
            echo '<span class="tw-font-medium tw-text-neutral-700">' . html_escape(_l('invoice_importer')) . ':</span> ';
            echo html_escape($importer);
            echo '</p>';
        } else {
            echo '<p class="no-mbot">';
            echo '<span class="bold">' . html_escape(_l('invoice_importer')) . ':</span> ';
            echo html_escape($importer);
            echo '</p>';
        }
    }

    if ($exporter !== '') {
        if ($is_client) {
            echo '<p class="tw-mb-0 tw-text-normal">';
            echo '<span class="tw-font-medium tw-text-neutral-700">' . html_escape(_l('invoice_exporter')) . ':</span> ';
            echo html_escape($exporter);
            echo '</p>';
        } else {
            echo '<p class="no-mbot">';
            echo '<span class="bold">' . html_escape(_l('invoice_exporter')) . ':</span> ';
            echo html_escape($exporter);
            echo '</p>';
        }
    }

    if ($importer_invoice_number !== '') {
        if ($is_client) {
            echo '<p class="tw-mb-0 tw-text-normal">';
            echo '<span class="tw-font-medium tw-text-neutral-700">' . html_escape(_l('importer_invoice_number')) . ':</span> ';
            echo html_escape($importer_invoice_number);
            echo '</p>';
        } else {
            echo '<p class="no-mbot">';
            echo '<span class="bold">' . html_escape(_l('importer_invoice_number')) . ':</span> ';
            echo html_escape($importer_invoice_number);
            echo '</p>';
        }
    }
}
