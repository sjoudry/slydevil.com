<?php

use SlyDevil\Database;
use Slydevil\Login;
use SlyDevil\Theme;

include_once(__DIR__ . '/../../../includes/init.inc.php');

Login::handleLogin('admin');

print Theme::htmlDashboardTop('Hosting :: Invoices :: Reports');

$result = Database::query(
  'SELECT
    *
  FROM
    invoice
  JOIN
    account
  USING
    (account_id)
  LEFT JOIN
    payment
  USING
    (invoice_id)
  WHERE
    invoice_date_deleted IS NULL
  ORDER BY
    invoice_date_start DESC'
);

$invoices = [];
while ($row = $result->fetch_assoc()) {
  $invoices[$row['invoice_id']] = $row;
}

$result = Database::query(
  'SELECT
    *
  FROM
    invoice_data
  WHERE
    invoice_id IN (%s)',
  [
    implode(',', array_keys($invoices))
  ]
);

$invoice_data = [];
while ($row = $result->fetch_assoc()) {
  $invoice_data[$row['invoice_id']][] = $row;
}

print "<table border='0' cellpadding='2' cellspacing='0' width='100%'>\n";
print "<tr>\n";
print "<th>Year</th>\n";
print "<th>Invoices</th>\n";
print "<th>Total Billed</th>\n";
print "<th>Tax</th>\n";
print "<th>Total</th>\n";
print "</tr>\n";

$years = [];
foreach ($invoices as $id => $row) {
  $total = 0;
  foreach ($invoice_data[$id] as $data) {
    $total += round(($data['invoice_data_quantity'] * $data['invoice_data_fee']), 2);
  }

  $calculated_gst = round(($total * $row['invoice_gst_rate']), 2);
  $calculated_pst = round(($total * $row['invoice_pst_rate']), 2);
    
  $year = date('Y', strtotime($invoices[$id]['invoice_date_start']));

  if (!isset($years[$year])) {
    $years[$year] = [
      'invoices' => 0,
      'billed' => 0,
      'taxed' => 0,
    ];
  }
  $years[$year]['invoices']++;
  $years[$year]['billed'] += $total;
  $years[$year]['taxed']  += $calculated_gst + $calculated_pst;
}

$stripe = 'even';
foreach ($years as $year => $data) {
  print "<tr>\n";
  print "<td class='" . $stripe . "'>" . $year . "</td>\n";
  print "<td class='" . $stripe . "'>" . $data["invoices"] . "</td>\n";
  print "<td class='" . $stripe . "'>" . sprintf("$%.2f", round($data["billed"], 2)) . "</td>\n";
  print "<td class='" . $stripe . "'>" . sprintf("$%.2f", round($data["taxed"], 2)) . "</td>\n";
  print "<td class='" . $stripe . "'>" . sprintf("$%.2f", round($data["billed"] + $data["taxed"], 2)) . "</td>\n";
  print "</tr>\n";

  $stripe = ($stripe == 'even') ? 'odd' : 'even';
}
print "</table>\n";

print Theme::htmlDashboardBottom();
