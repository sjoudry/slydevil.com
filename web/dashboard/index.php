<?php

use SlyDevil\Database\Query;
use SlyDevil\Login;
use SlyDevil\Theme;

include_once(__DIR__ . '/../../includes/init.inc.php');

Login::handleLogin('user');

print Theme::htmlDashboardTop('Sly Devil :: Dashboard');

if (Login::$userId == 1) {
  $result = Query::create(
    "SELECT 'users' AS type, COUNT(*) AS count FROM user WHERE user_date_deleted IS NULL
    UNION
    SELECT 'packages' AS type, COUNT(*) AS count FROM package WHERE package_date_deleted IS NULL
    UNION
    SELECT 'accounts' AS type, COUNT(*) AS count FROM account WHERE account_date_deleted IS NULL
    UNION
    SELECT 'domains' AS type, COUNT(*) AS count FROM domain WHERE domain_date_deleted IS NULL
    UNION
    SELECT 'services' AS type, COUNT(*) AS count FROM service WHERE service_date_deleted IS NULL
    UNION
    SELECT 'invoices' AS type, COUNT(*) AS count FROM invoice WHERE invoice_date_deleted IS NULL"
  )->result();

  if ($result->num_rows) {
    print "<table border='0' cellpadding='2' cellspacing='0' width='100%'>\n";
    $stripe = 'even';
    while ($row = $result->fetch_assoc()) {
      print "<tr><td class='" . $stripe . "'>" . ucfirst($row['type']) . "</td><td class='" . $stripe . "' align='right'>" . $row['count'] . "</td></tr>\n";
      $stripe = ($stripe == 'even') ? 'odd' : 'even';
    }
                
    print "</table>\n";
  }
}
else {
  $result = Query::create(
    'SELECT * FROM user JOIN account USING (account_id) WHERE user_id = %s',
    [
      Login::$userId
    ]
  )->result();
    
  $account = $result->fetch_assoc();

  $result = Query::create(
    'SELECT * FROM invoice JOIN user USING (account_id) WHERE invoice_date_deleted IS NULL AND user_id = %s ORDER BY invoice_number DESC',
    [
      Login::$userId
    ]
  )->result();

  if ($result->num_rows) {
    $invoices = [];
    $last_invoice = 'N/A';
    while ($row = $result->fetch_assoc()) {
      $invoices[$row['invoice_id']] = $row;
      $last_invoice = $row['invoice_date_added'];
    }
    
    $result = Query::create(
      'SELECT * FROM invoice_data WHERE invoice_id IN (%s)',
      [
        implode(',', array_keys($invoices))
      ]
    )->result();

    $invoice_data = array();
    while ($row = $result->fetch_assoc()) {
      $invoice_data[$row['invoice_id']][] = $row;
    }
    
    $result = Query::create(
      'SELECT invoice_id, payment_amount FROM payment WHERE invoice_id IN (%s)',
      [
        implode(',', array_keys($invoices))
      ]
    )->result();

    $payments = array();
    $last_payment = 'N/A';
    while ($row = $result->fetch_assoc()) {
      $payments[$row['invoice_id']][] = $row['payment_amount'];
      $last_payment = sprintf('$%.2f', $row['payment_amount']);
    }

    $account_balance = 0;
    foreach ($invoices as $id => $row) {
      $total = 0;
      foreach ($invoice_data[$id] as $data) {
        $total += round(($data['invoice_data_quantity'] * $data['invoice_data_fee']), 2);
      }

      $calculated_gst = round(($total * $row['invoice_gst_rate']), 2);
      $calculated_pst = round(($total * $row['invoice_pst_rate']), 2);
            
      $total += $calculated_gst + $calculated_pst;
            
      $paid = 0;
      foreach ($payments[$id] as $payment) {
        $paid += $payment;
      }
            
      $balance = round($total - $paid, 2);
            
      $account_balance += $balance;
    }
        
    $cycles = array(
      '1' => 'Monthly',
      '2' => 'Quarterly',
      '3' => 'Semi-Annually',
      '4' => 'Annually',
    );

    print "<table border='0' cellpadding='2' cellspacing='0' width='100%' class='account_overview'>\n";
    print "<tr>\n";
    print "<th colspan='2'>Account Overview</th>\n";
    print "</tr>\n";
    print "<tr>\n";
    print "<td width='80%'>Account Balance: </td>\n";
    print "<td width='20%'>" . sprintf("$%.2f", $account_balance) . "</td>\n";
    print "</tr>\n";
    print "<tr>\n";
    print "<td>Last Invoiced: </td>\n";
    print "<td>" . $last_invoice . "</td>\n";
    print "</tr>\n";
    print "<tr>\n";
    print "<td>Last Payment: </td>\n";
    print "<td>" . $last_payment . "</td>\n";
    print "</tr>\n";
    print "<tr>\n";
    print "<td>Billing Cycle: </td>\n";
    print "<td>" . $cycles[$account["account_billing"]] . "</td>\n";
    print "</tr>\n";
    print "</table>\n";
    
    print "<table border='0' cellpadding='2' cellspacing='0' width='100%' class='account_overview'>\n";
    print "<tr>\n";
    print "<th colspan='2'>Package Overview</th>\n";
    print "</tr>\n";
    print "<tr>\n";
    print "<td width='80%'>Base Fee: </td>\n";
    print "<td width='20%'>" . sprintf("$%.2f", $account_balance) . "</td>\n";
    print "</tr>\n";
    print "<tr>\n";
    print "<td>Extra Domain Fee: </td>\n";
    print "<td>" . $last_invoice . "</td>\n";
    print "</tr>\n";
    print "</table>\n";

    print "<table border='0' cellpadding='2' cellspacing='0' width='100%'>\n";
    print "<tr>\n";
    print "<th>&nbsp;</th>\n";
    print "<th>Invoice Number</th>\n";
    print "<th>Start Date</th>\n";
    print "<th>End Date</th>\n";
    print "<th>Amount</th>\n";
    print "<th>Payment</th>\n";
    print "<th>Owing</th>\n";
    print "</tr>\n";

    $stripe = 'even';
    foreach ($invoices as $id => $row) {
      $total = 0;
      foreach ($invoice_data[$id] as $data) {
        $total += round(($data['invoice_data_quantity'] * $data['invoice_data_fee']), 2);
      }

      $calculated_gst = round(($total * $row['invoice_gst_rate']), 2);
      $calculated_pst = round(($total * $row['invoice_pst_rate']), 2);
            
      $total += $calculated_gst + $calculated_pst;
            
      $paid = 0;
      foreach ($payments[$id] as $payment) {
        $paid += $payment;
      }
            
      $balance = round($total - $paid, 2);

      print "<tr>\n";
      print "<td class='" . $stripe . "'><a href='view.php?id=" . $row["invoice_id_public"] . "'><i class='fa fa-eye'></i></a></td>\n";
      print "<td class='" . $stripe . "'>" . sprintf("SDWH-%05d", $row["invoice_number"]) . "</td>\n";
      print "<td class='" . $stripe . "'>" . $row["invoice_date_start"] . "</td>\n";
      print "<td class='" . $stripe . "'>" . $row["invoice_date_end"] . "</td>\n";
      print "<td class='" . $stripe . "'>" . sprintf("$%.2f", $total) . "</td>\n";
      print "<td class='" . $stripe . "'>" . sprintf("$%.2f", $paid) . "</td>\n";
      print "<td class='" . $stripe . "'>" . sprintf("$%.2f", $balance) . "</td>\n";
      print "</tr>\n";
            
      $stripe = ($stripe == 'even') ? 'odd' : 'even';
    }
    print "</table>\n";
  }
  else {
    print "<div class='table-no-data'>No invoices</div>\n";
  }
}

print Theme::htmlDashboardBottom();
