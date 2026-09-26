<?php defined('BASEPATH') or exit('No direct script access allowed');
$dispatch_date = !empty($delivery_note['dispatch_date']) && $delivery_note['dispatch_date'] != '0000-00-00'
    ? strtoupper(date('d-F-Y', strtotime($delivery_note['dispatch_date'])))
    : '';
$total_packages = 0;
foreach ($items as $item) {
    $total_packages += (int) $item['packages'];
}

$company_logo = get_option('company_logo_dark') != '' ? get_option('company_logo_dark') : get_option('company_logo');
$company_logo_path = FCPATH . 'uploads/company/' . $company_logo;
$show_logo = $company_logo != '' && file_exists($company_logo_path);

$signature_image = get_option('signature_image');
$signature_path = FCPATH . 'uploads/company/' . $signature_image;
$show_signature = $signature_image != '' && file_exists($signature_path);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php echo _l('delivery_note') . ' - ' . $booking->number; ?></title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: Arial, Helvetica, sans-serif; color: #000; margin: 0; padding: 40px 50px 90px; }
    .dn-logo { text-align: left; margin-bottom: 15px; }
    .dn-logo img { max-height: 90px; max-width: 260px; }
    .dn-header { text-align: center; font-size: 26px; font-weight: bold; text-decoration: underline; text-transform: uppercase; margin-bottom: 40px; }
    table.dn-info { width: 100%; border-collapse: collapse; margin-bottom: 35px; font-size: 15px; }
    table.dn-info td { padding: 5px 4px; vertical-align: top; }
    table.dn-info td.dn-label { width: 240px; font-weight: bold; }
    table.dn-items { width: 100%; border-collapse: collapse; font-size: 15px; }
    table.dn-items th, table.dn-items td { border: 1px solid #000; padding: 8px 10px; text-align: left; }
    table.dn-items th { font-weight: bold; }
    table.dn-items td.dn-center { text-align: center; }
    table.dn-total { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 16px; font-weight: bold; }
    .dn-signature { margin-top: 60px; font-size: 15px; }
    .dn-signature .dn-sig-label { font-weight: bold; margin-bottom: 8px; }
    .dn-signature img { max-height: 130px; max-width: 360px; }
    .dn-signature .dn-sig-line { border-bottom: 1px solid #000; width: 240px; margin-top: 40px; }
    .dn-actions { margin-bottom: 25px; }
    .dn-watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 0; opacity: 0.06; }
    .dn-watermark img { width: 620px; }
    .dn-content { position: relative; z-index: 1; }
    .dn-footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 10px 0 14px; text-align: center; font-size: 13px; color: #000; z-index: 2; background: #fff; }
    .dn-footer .dn-footer-line { margin: 3px 0; }
    .dn-footer .dn-footer-item { display: inline-block; margin: 0 18px; }
    .dn-footer svg { width: 15px; height: 15px; vertical-align: -2px; margin-right: 6px; stroke: #00a651; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    @media print { .dn-actions { display: none; } body { padding: 0 0 90px; } }
  </style>
</head>
<body>
  <?php if ($show_logo) { ?>
  <div class="dn-watermark">
    <img src="<?php echo base_url('uploads/company/' . $company_logo); ?>" alt="">
  </div>
  <?php } ?>

  <div class="dn-actions">
    <button type="button" onclick="window.print()"><?php echo _l('print_delivery_note'); ?></button>
  </div>

  <div class="dn-content">

  <?php if ($show_logo) { ?>
  <div class="dn-logo">
    <img src="<?php echo base_url('uploads/company/' . $company_logo); ?>" alt="<?php echo get_option('companyname'); ?>">
  </div>
  <?php } ?>

  <div class="dn-header"><?php echo _l('delivery_note'); ?></div>

  <table class="dn-info">
    <tr>
      <td class="dn-label"><?php echo _l('delivery_note_supplier'); ?></td>
      <td><?php echo new_html_entity_decode($delivery_note['supplier']); ?></td>
    </tr>
    <tr>
      <td class="dn-label"><?php echo _l('delivery_note_contact_person'); ?></td>
      <td><?php echo new_html_entity_decode($delivery_note['contact_person']); ?></td>
    </tr>
    <tr>
      <td class="dn-label"><?php echo _l('delivery_note_invoice_no'); ?></td>
      <td><?php echo new_html_entity_decode($delivery_note['invoice_no']); ?></td>
    </tr>
    <tr>
      <td class="dn-label"><?php echo _l('delivery_note_no_of_trucks'); ?></td>
      <td><?php echo str_pad(count($items), 2, '0', STR_PAD_LEFT); ?></td>
    </tr>
    <tr>
      <td class="dn-label"><?php echo _l('delivery_note_destination'); ?></td>
      <td><?php echo new_html_entity_decode($delivery_note['destination']); ?></td>
    </tr>
    <tr>
      <td class="dn-label"><?php echo _l('delivery_note_dispatch_date'); ?></td>
      <td><?php echo $dispatch_date; ?></td>
    </tr>
  </table>

  <table class="dn-items">
    <thead>
      <tr>
        <th width="8%"><?php echo _l('delivery_note_s_no'); ?></th>
        <th width="45%"><?php echo _l('delivery_note_drivers_name'); ?></th>
        <th width="27%"><?php echo _l('delivery_note_truck_no'); ?></th>
        <th width="20%"><?php echo _l('delivery_note_packages'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php
      $i = 1;
      foreach ($items as $item) { ?>
        <tr>
          <td class="dn-center"><?php echo $i; ?></td>
          <td><?php echo !empty($item['driver_id']) ? new_html_entity_decode(get_staff_full_name($item['driver_id'])) : ''; ?></td>
          <td><?php echo new_html_entity_decode($item['truck_no']); ?></td>
          <td><?php echo (int) $item['packages']; ?></td>
        </tr>
        <?php
        $i++;
      } ?>
    </tbody>
  </table>

  <table class="dn-total">
    <tr>
      <td width="70%" style="padding-left:10px;"><?php echo _l('delivery_note_total_packages'); ?></td>
      <td><?php echo $total_packages; ?></td>
    </tr>
  </table>

  <div class="dn-signature">
    <div class="dn-sig-label"><?php echo _l('authorized_signature_text'); ?></div>
    <?php if ($show_signature) { ?>
      <img src="<?php echo base_url('uploads/company/' . $signature_image); ?>" alt="<?php echo _l('authorized_signature_text'); ?>">
    <?php } else { ?>
      <div class="dn-sig-line"></div>
    <?php } ?>
  </div>
  </div>

  <div class="dn-footer">
    <div class="dn-footer-line">
      <span class="dn-footer-item"><svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>+971 4 267 0264</span>
      <span class="dn-footer-item"><svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>tanveer@zaroonilogistic.com</span>
      <span class="dn-footer-item"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>www.Zaroonilogistic.com</span>
    </div>
    <div class="dn-footer-line">
      <span class="dn-footer-item"><svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>Office No: 1115, IT Plaza, Silicon Oasis, Nadd Hessa, Dubai - UAE</span>
    </div>
  </div>
</body>
</html>
