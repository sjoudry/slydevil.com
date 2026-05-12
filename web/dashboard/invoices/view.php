<?php

use SlyDevil\Database;
use Slydevil\Login;
use SlyDevil\Theme;

include_once(__DIR__ . '/../../../includes/init.inc.php');

Login::handleLogin('admin');

print Theme::htmlInvoiceTop('Hosting :: Invoice :: View Invoice');

$result = Database::query(
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
    $_REQUEST['id']
  ]
);

$invoice = $result->fetch_assoc();

$result = Database::query(
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
    
$result = Database::query(
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

print "<table border='0' cellpadding='2' cellspacing='1' width='100%' class='invoice'>\n";
print "<tr>\n";
print "<td valign='top'><img src='/includes/images/logo.png' height='42' width='200' /></td>\n";
print "<td valign='top'>\n";
print "<table border='0' cellpadding='0' cellspacing='0' class='view-data'>\n";
print "<tr>\n";
print "<td align='right'>Invoice:</td>\n";
print "<td>" . sprintf("SDWH-%05d", $invoice["invoice_number"]) . "</td>\n";
print "</tr>\n";
print "<tr>\n";
print "<td align='right'>Invoice Date:</td>\n";
print "<td>" . date("M d, Y", strtotime($invoice["invoice_date_start"])) . "</td>\n";
print "</tr>\n";
print "<tr>\n";
print "<td align='right'>Due Date:</td>\n";
print "<td>" . date("M d, Y", mktime(0, 0, 0, date("m", strtotime($invoice["invoice_date_start"])) + 1, 1, date("Y", strtotime($invoice["invoice_date_start"])))) . "</td>\n";
print "</tr>\n";
print "</table>\n";
print "</td>\n";
print "</tr>\n";
print "</table>\n";

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

print "<table border='0' cellpadding='2' cellspacing='1' width='100%' class='invoice'>\n";
print "<tr>\n";
print "<td valign='top' width='50'></td>\n";
print "<td valign='top' class='sd-address'>" . $address . "</td>\n";
print "</tr>\n";
print "</table>\n";

print "<br /><br />";

print "<table border='0' cellpadding='2' cellspacing='1' width='100%' class='invoice view'>\n";
print "<tr>\n";
print "<th width='75'>Qty</th>\n";
print "<th>Service Description</th>\n";
print "<th width='75'>Fee</th>\n";
print "<th width='75'>Amt</th>\n";
print "</tr>\n";

$total = 0;
foreach ($invoice_data as $data) {
  $subtotal = round($data['invoice_data_quantity'] * $data['invoice_data_fee'], 2);
  $total += $subtotal;
    
  $indent = str_repeat('&nbsp;&nbsp;', $data['invoice_data_indent']);
    
  print "<tr>\n";
  if ($data['invoice_data_quantity'] > 0) {
    print "<td align='right'>" . sprintf('%.2f', round($data['invoice_data_quantity'], 2)) . "</td>\n";
  }
  else {
    print "<td>&nbsp;</td>\n";
  }
  print "<td align='left'>" . $indent . $data['invoice_data_description'] . "</td>\n";
  if ($data['invoice_data_fee'] != 0) {
    print "<td align='right'>" . sprintf('$%.2f', round($data['invoice_data_fee'], 2)) . "</td>\n";
  }
  else {
    print "<td>&nbsp;</td>\n";
  }
  if ($data['invoice_data_quantity'] > 0 && $data['invoice_data_fee'] != 0) {
    print "<td align='right'>" . sprintf('$%.2f', $subtotal) . "</td>\n";
  }
  else {
    print "<td>&nbsp;</td>\n";
  }
  print "</tr>\n";
}

print "<tr>\n";
print "<td></td>\n";
print "<td>&nbsp;</td>\n";
print "<td></td>\n";
print "<td></td>\n";
print "</tr>\n";

$calculated_gst = round(($total * $invoice['invoice_gst_rate']), 2);
$calculated_pst = round(($total * $invoice['invoice_pst_rate']), 2);
    
$total += $calculated_gst + $calculated_pst;

if ($invoice['invoice_gst_rate'] > 0) {
  print "<tr>\n";
  print "<td></td>\n";
  print "<td align='right'>GST (" . $invoice['invoice_gst_rate'] * 100 . "%)</td>\n";
  print "<td></td>\n";
  print "<td align='right'>" . sprintf('$%.2f', $calculated_gst) . "</td>\n";
  print "</tr>\n";
}

if ($invoice['invoice_pst_rate'] > 0) {
  print "<tr>\n";
  print "<td></td>\n";
  print "<td align='right'>PST (" . $invoice['invoice_pst_rate'] * 100 . "%)</td>\n";
  print "<td></td>\n";
  print "<td align='right'>" . sprintf('$%.2f', $calculated_pst) . "</td>\n";
  print "</tr>\n";
}

print "<tr>\n";
print "<td></td>\n";
print "<td align='right'>Total</td>\n";
print "<td></td>\n";
print "<td align='right'>" . sprintf('$%.2f', $total) . "</td>\n";
print "</tr>\n";

print "<tr>\n";
print "<td></td>\n";
print "<td>&nbsp;</td>\n";
print "<td></td>\n";
print "<td></td>\n";
print "</tr>\n";

$paid = 0;
foreach ($payments as $payment) {
  $paid += $payment;
  
  print "<tr>\n";
  print "<td></td>\n";
  print "<td align='right'>Payment</td>\n";
  print "<td></td>\n";
  print "<td align='right'>" . sprintf('$-%.2f', $payment) . "</td>\n";
  print "</tr>\n";
}

$balance = round($total - $paid, 2);

print "<tr>\n";
print "<td></td>\n";
print "<td>&nbsp;</td>\n";
print "<td></td>\n";
print "<td></td>\n";
print "</tr>\n";

print "<tr>\n";
print "<td></td>\n";
print "<td align='right'>Balance</td>\n";
print "<td></td>\n";
print "<td align='right'>" . sprintf('$%.2f', $balance) . "</td>\n";
print "</tr>\n";

print "</table>\n";

print "<table border='0' cellpadding='2' cellspacing='1' width='100%' class='invoice'>\n";
print "<tr>\n";
print "<td class='sd-address' align='right'><span>All prices in Canadian Dollars</span></td>\n";
print "</tr>\n";
print "</table>\n";

print "<table border='0' cellpadding='2' cellspacing='1' width='100%' class='invoice'>\n";
print "<tr>\n";
print "<td valign='top' class='sd-address' width='50%'><u>Make Payment To:</u><br />&nbsp;&nbsp;Scott Joudry<br />&nbsp;&nbsp;174 Acres Road<br />&nbsp;&nbsp;Williamswood, N.S., Canada<br />&nbsp;&nbsp;B3V 1E3</td>\n";
print "<td valign='top' class='sd-address' width='50%'><u>Contact:</u><br/>&nbsp;&nbsp;Email: sj@slydevil.com<br/>&nbsp;&nbsp;Web: https://www.slydevilhost.com/<br/>&nbsp;&nbsp;Phone: 1-(902)-441-6516</td>\n";
print "</tr>\n";
print "</table>\n";

print Theme::htmlInvoiceBottom();
