<?php

use SlyDevil\Site\Main;

include_once(__DIR__ . '/../../../includes/init.inc.php');

$main = new Main();
$main->getLogin()->handle('admin');

print $main->getTheme()->htmlInvoiceTop('Hosting :: Invoice :: View Invoice');

$result = $main->getDatabase()->query(
  "SELECT
    *
  FROM
    invoice
  JOIN
    account
  USING
    (account_id)
  JOIN
    user
  USING
    (account_id)
  WHERE
    invoice_id_public = '%s'",
  [
    $main->getSessionManager()->filterVariable($_REQUEST['id'])
  ]
);

$invoice = $result->fetch_assoc();

$result = $main->getDatabase()->query(
  'SELECT
    *
  FROM
    invoice_data
  WHERE
    invoice_id = %s
  ORDER BY
    invoice_data_sequence',
  [
    $invoice['invoice_id']
  ]
);

$invoice_data = [];
while ($row = $result->fetch_assoc()) {
  $invoice_data[] = $row;
}

$result = $main->getDatabase()->query(
  "SELECT
    invoice_id,
    payment_amount
  FROM
    payment
  WHERE
    invoice_id = %s",
  [
    $invoice['invoice_id']
  ]
);

$payments = [];
while ($row = $result->fetch_assoc()) {
  $payments[] = $row['payment_amount'];
}

print '<table border="0" cellpadding="2" cellspacing="1" width="100%" class="invoice">';
print '<tr>';
print '<td valign="top"><img src="/includes/images/logo.png" height="42" width="200" /></td>';
print '<td valign="top">';
print '<table border="0" cellpadding="0" cellspacing="0" class="view-data">';
print '<tr>';
print '<td align="right">Invoice:</td>';
print '<td>' . sprintf('SDWH-%05d', $invoice['invoice_number']) . '</td>';
print '</tr>';
print '<tr>';
print '<td align="right">Invoice Date:</td>';
print '<td>' . date('M d, Y', strtotime($invoice['invoice_date_start'])) . '</td>';
print '</tr>';
print '<tr>';
print '<td align="right">Due Date:</td>';
print '<td>' . date('M d, Y', mktime(0, 0, 0, date('m', strtotime($invoice['invoice_date_start'])) + 1, 1, date('Y', strtotime($invoice['invoice_date_start'])))) . '</td>';
print '</tr>';
print '</table>';
print '</td>';
print '</tr>';
print '</table>';

$address = $invoice['user_first_name'] . ' ' . $invoice['user_last_name'];
if (!empty($invoice['account_company'])) {
  $address .= '<br />' . $invoice['account_company'];
}
if (!empty($invoice['account_street_address1'])) {
  $address .= '<br />' . $invoice['account_street_address1'];
}
if (!empty($invoice['account_street_address2'])) {
  $address .= '<br />' . $invoice['account_street_address2'];
}
if (!empty($invoice['account_city']) || !empty($invoice['account_province']) || !empty($invoice['account_country'])) {
  $address .= '<br />';

  if (!empty($invoice['account_city'])) {
    $address .= $invoice['account_city'];

    if (!empty($invoice['account_province'])) {
      $address .= ', ' . $invoice['account_province'];

      if (!empty($invoice['account_country'])) {
        $address .= ', ' . $invoice['account_country'];
      }
    }
    else {
      if (!empty($invoice['account_country'])) {
        $address .= ', ' . $invoice['account_country'];
      }
    }
  }
  else {
    if (!empty($invoice['account_province'])) {
      $address .= $invoice['account_province'];

      if (!empty($invoice['account_country'])) {
        $address .= ', ' . $invoice['account_country'];
      }
    }
  }
}
if (!empty($invoice['account_postal'])) {
  $address .= '<br />' . $invoice['account_postal'];
}

print '<br />';

print '<table border="0" cellpadding="2" cellspacing="1" width="100%" class="invoice">';
print '<tr>';
print '<td valign="top" width="50"></td>';
print '<td valign="top" class="sd-address">' . $address . '</td>';
print '</tr>';
print '</table>';

print '<br /><br />';

print '<table border="0" cellpadding="2" cellspacing="1" width="100%" class="invoice view">';
print '<tr>';
print '<th width="75">Qty</th>';
print '<th>Service Description</th>';
print '<th width="75">Fee</th>';
print '<th width="75">Amt</th>';
print '</tr>';

$total = 0;
foreach ($invoice_data as $data) {
  $subtotal = round($data['invoice_data_quantity'] * $data['invoice_data_fee'], 2);
  $total += $subtotal;

  $indent = str_repeat('&nbsp;&nbsp;', $data['invoice_data_indent']);

  print "<tr>";
  if ($data['invoice_data_quantity'] > 0) {
    print '<td align="right">' . sprintf('%.2f', round($data['invoice_data_quantity'], 2)) . '</td>';
  }
  else {
    print '<td>&nbsp;</td>';
  }
  print '<td align="left">' . $indent . $data['invoice_data_description'] . '</td>';
  if ($data['invoice_data_fee'] != 0) {
    print '<td align="right">' . sprintf('$%.2f', round($data['invoice_data_fee'], 2)) . '</td>';
  }
  else {
    print '<td>&nbsp;</td>';
  }
  if ($data['invoice_data_quantity'] > 0 && $data['invoice_data_fee'] != 0) {
    print '<td align="right">' . sprintf('$%.2f', $subtotal) . '</td>';
  }
  else {
    print '<td>&nbsp;</td>';
  }
  print '</tr>';
}

print '<tr>';
print '<td></td>';
print '<td>&nbsp;</td>';
print '<td></td>';
print '<td></td>';
print '</tr>';

$calculated_gst = round(($total * $invoice['invoice_gst_rate']), 2);
$calculated_pst = round(($total * $invoice['invoice_pst_rate']), 2);

$total += $calculated_gst + $calculated_pst;

if ($invoice['invoice_gst_rate'] > 0) {
  print '<tr>';
  print '<td></td>';
  print '<td align="right">GST (' . $invoice['invoice_gst_rate'] * 100 . '%)</td>';
  print '<td></td>';
  print '<td align="right">' . sprintf('$%.2f', $calculated_gst) . '</td>';
  print '</tr>';
}

if ($invoice['invoice_pst_rate'] > 0) {
  print '<tr>';
  print '<td></td>';
  print '<td align="right">PST (' . $invoice['invoice_pst_rate'] * 100 . '%)</td>';
  print '<td></td>';
  print '<td align="right">' . sprintf('$%.2f', $calculated_pst) . '</td>';
  print '</tr>';
}

print '<tr>';
print '<td></td>';
print '<td align="right">Total</td>';
print '<td></td>';
print '<td align="right">' . sprintf('$%.2f', $total) . '</td>';
print '</tr>';

print '<tr>';
print '<td></td>';
print '<td>&nbsp;</td>';
print '<td></td>';
print '<td></td>';
print '</tr>';

$paid = 0;
foreach ($payments as $payment) {
  $paid += $payment;

  print '<tr>';
  print '<td></td>';
  print '<td align="right">Payment</td>';
  print '<td></td>';
  print '<td align="right">' . sprintf('$-%.2f', $payment) . '</td>';
  print '</tr>';
}

$balance = round($total - $paid, 2);

print '<tr>';
print '<td></td>';
print '<td>&nbsp;</td>';
print '<td></td>';
print '<td></td>';
print '</tr>';

print '<tr>';
print '<td></td>';
print '<td align="right">Balance</td>';
print '<td></td>';
print '<td align="right">' . sprintf('$%.2f', $balance) . '</td>';
print '</tr>';

print '</table>';

print '<table border="0" cellpadding="2" cellspacing="1" width="100%" class="invoice">';
print '<tr>';
print '<td class="sd-address" align="right"><span>All prices in Canadian Dollars</span></td>';
print '</tr>';
print '</table>';

print '<table border="0" cellpadding="2" cellspacing="1" width="100%" class="invoice">';
print '<tr>';
print '<td valign="top" class="sd-address" width="50%"><u>Make Payment To:</u><br />&nbsp;&nbsp;Scott Joudry<br />&nbsp;&nbsp;174 Acres Road<br />&nbsp;&nbsp;Williamswood, N.S., Canada<br />&nbsp;&nbsp;B3V 1E3</td>';
print '<td valign="top" class="sd-address" width="50%"><u>Contact:</u><br/>&nbsp;&nbsp;Email: sj@slydevil.com<br/>&nbsp;&nbsp;Web: https://www.slydevilhost.com/<br/>&nbsp;&nbsp;Phone: 1-(902)-441-6516</td>';
print '</tr>';
print '</table>';

print $main->getTheme()->htmlInvoiceBottom();
