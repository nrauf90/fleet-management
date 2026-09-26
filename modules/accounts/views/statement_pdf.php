<?php

defined('BASEPATH') or exit('No direct script access allowed');
$dimensions = $pdf->getPageDimensions();

$info_right_column = '<div style="color:#424242;">';
$info_right_column .= format_organization_info();
$info_right_column .= '</div>';

$info_left_column = pdf_logo_url();

pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->ln(10);

$currency = $statement['currency'];
$account_label = $statement['account'] === 'cash'
    ? _l('accounts_cash')
    : ($statement['account'] === 'bank' ? _l('accounts_bank') : _l('accounts_all_accounts'));

$summary = '';
$summary .= '<h2>' . _l('accounts_statement') . '</h2>';
$summary .= '<div style="color:#676767;">' . _l('accounts_statement_period', [
    _d($statement['from']),
    _d($statement['to']),
]) . ' — ' . $account_label . '</div>';
$summary .= '<hr />';
$summary .= '
<table cellpadding="4" border="0" style="color:#424242;" width="100%">
   <tbody>
      <tr>
          <td align="left">' . _l('accounts_beginning_balance') . ':</td>
          <td align="right">' . app_format_money($statement['beginning_balance'], $currency) . '</td>
      </tr>
      <tr>
          <td align="left">' . _l('accounts_total_credits') . ':</td>
          <td align="right">' . app_format_money($statement['total_credits'], $currency) . '</td>
      </tr>
      <tr>
          <td align="left">' . _l('accounts_total_debits') . ':</td>
          <td align="right">' . app_format_money($statement['total_debits'], $currency) . '</td>
      </tr>
  </tbody>
  <tfoot>
      <tr>
        <td align="left"><b>' . _l('accounts_closing_balance') . '</b>:</td>
        <td align="right"><b>' . app_format_money($statement['closing_balance'], $currency) . '</b></td>
    </tr>
  </tfoot>
</table>';

$pdf->writeHTMLCell($dimensions['wk'] - ($dimensions['rm'] + $dimensions['lm']), '', '', $pdf->getY(), $summary, 0, 1, false, true, 'L', true);

$pdf->ln(6);

$running = $statement['beginning_balance'];

$tblhtml = '<table width="100%" cellspacing="0" cellpadding="6" border="0">
<thead>
 <tr height="10" bgcolor="#e8e8e8" style="color:#424242;">
     <th width="11%"><b>' . _l('accounts_date') . '</b></th>
     <th width="10%"><b>' . _l('accounts_account') . '</b></th>
     <th width="29%"><b>' . _l('accounts_description') . '</b></th>
     <th width="12%" align="right"><b>' . _l('accounts_credit') . '</b></th>
     <th width="12%" align="right"><b>' . _l('accounts_debit') . '</b></th>
     <th width="14%" align="right"><b>' . _l('accounts_balance') . '</b></th>
     <th width="12%"><b>' . _l('accounts_source') . '</b></th>
 </tr>
</thead>
<tbody>
 <tr>
     <td width="11%">' . _d($statement['from']) . '</td>
     <td width="10%"></td>
     <td width="29%">' . _l('accounts_beginning_balance') . '</td>
     <td width="12%" align="right"></td>
     <td width="12%" align="right"></td>
     <td width="14%" align="right">' . app_format_money($statement['beginning_balance'], $currency, true) . '</td>
     <td width="12%"></td>
 </tr>';

$count = 0;
foreach ($statement['transactions'] as $tx) {
    $is_credit = $tx['transaction_type'] === 'credit';
    $running += $is_credit ? (float) $tx['amount'] : -(float) $tx['amount'];

    $tx_account = !empty($tx['account']) && $tx['account'] === 'bank' ? _l('accounts_bank') : _l('accounts_cash');

    $details = e($tx['description']);
    if (!empty($tx['reference'])) {
        $details .= '<br /><span style="color:#676767;">' . e($tx['reference']) . '</span>';
    }

    $tblhtml .= '<tr' . (++$count % 2 ? '' : ' bgcolor="#f6f5f5"') . '>
  <td width="11%">' . _d($tx['transaction_date']) . '</td>
  <td width="10%">' . e($tx_account) . '</td>
  <td width="29%">' . $details . '</td>
  <td width="12%" align="right">' . ($is_credit ? app_format_money($tx['amount'], $currency, true) : '') . '</td>
  <td width="12%" align="right">' . (!$is_credit ? app_format_money($tx['amount'], $currency, true) : '') . '</td>
  <td width="14%" align="right">' . app_format_money($running, $currency, true) . '</td>
  <td width="12%">' . e(accounts_source_label($tx['source_type'])) . '</td>
</tr>';
}

if ($count === 0) {
    $tblhtml .= '<tr><td colspan="7" align="center" style="color:#676767;">' . _l('accounts_no_transactions') . '</td></tr>';
}

$tblhtml .= '</tbody>
        <tfoot>
         <tr style="color:#424242;">
             <td colspan="5" align="right"><b>' . _l('accounts_closing_balance') . '</b></td>
             <td align="right"><b>' . app_format_money($statement['closing_balance'], $currency) . '</b></td>
             <td></td>
         </tr>
     </tfoot>
 </table>';

$pdf->writeHTML($tblhtml, true, false, false, false, '');
