<?php

use SlyDevil\Site\Main;

include_once(__DIR__ . '/../../includes/init.inc.php');

$main = new Main();
$main->getLogin()->handle('user');

print $main->getTheme()->htmlDashboardTop('Sly Devil :: Dashboard');

if ($main->getLogin()->getUserId() == 1) {
  $result = $main->getDatabase()->query(
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
  );

  if ($result->num_rows) {
    print '<table border="0" cellpadding="2" cellspacing="0" width="100%">';
    $stripe = 'even';
    while ($row = $result->fetch_assoc()) {
      print '<tr><td class="' . $stripe . '">' . ucfirst($row['type']) . '</td><td class="' . $stripe . '" align="right">' . $row['count'] . '</td></tr>';
      $stripe = ($stripe == 'even') ? 'odd' : 'even';
    }

    print '</table>';
  }
}
else {
  $result = $main->getDatabase()->query(
    'SELECT * FROM user JOIN account USING (account_id) WHERE user_id = %s',
    [
      $main->getLogin()->getUserId()
    ]
  );

  $account = $result->fetch_assoc();

  $result = $main->getDatabase()->query(
    'SELECT * FROM invoice JOIN user USING (account_id) WHERE invoice_date_deleted IS NULL AND user_id = %s ORDER BY invoice_number DESC',
    [
      $main->getLogin()->getUserId()
    ]
  );

  if ($result->num_rows) {
    $invoices = [];
    $last_invoice = 'N/A';
    while ($row = $result->fetch_assoc()) {
      $invoices[$row['invoice_id']] = $row;
      $last_invoice = $row['invoice_date_added'];
    }

    $result = $main->getDatabase()->query(
      'SELECT * FROM invoice_data WHERE invoice_id IN (%s)',
      [
        implode(',', array_keys($invoices))
      ]
    );

    $invoice_data = array();
    while ($row = $result->fetch_assoc()) {
      $invoice_data[$row['invoice_id']][] = $row;
    }

    $result = $main->getDatabase()->query(
      'SELECT invoice_id, payment_amount FROM payment WHERE invoice_id IN (%s)',
      [
        implode(',', array_keys($invoices))
      ]
    );

    $payments = [];
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
      if (isset($payments[$id])) {
        foreach ($payments[$id] as $payment) {
          $paid += $payment;
        }
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

    print '<table border="0" cellpadding="2" cellspacing="0" width="100%" class="account_overview">';
    print '<tr>';
    print '<th colspan="2">Account Overview</th>';
    print '</tr>';
    print '<tr>';
    print '<td width="80%">Account Balance: </td>';
    print '<td width="20%">' . sprintf('$%.2f', $account_balance) . '</td>';
    print '</tr>';
    print '<tr>';
    print '<td>Last Invoiced: </td>';
    print '<td>' . $last_invoice . '</td>';
    print '</tr>';
    print '<tr>';
    print '<td>Last Payment: </td>';
    print '<td>' . $last_payment . '</td>';
    print '</tr>';
    print '<tr>';
    print '<td>Billing Cycle: </td>';
    print '<td>' . $cycles[$account['account_billing']] . '</td>';
    print '</tr>';
    print '</table>';

    print '<table border="0" cellpadding="2" cellspacing="0" width="100%" class="account_overview">';
    print '<tr>';
    print '<th colspan="2">Package Overview</th>';
    print '</tr>';
    print '<tr>';
    print '<td width="80%">Base Fee: </td>';
    print '<td width="20%">' . sprintf('$%.2f', $account_balance) . '</td>';
    print '</tr>';
    print '<tr>';
    print '<td>Extra Domain Fee: </td>';
    print '<td>' . $last_invoice . '</td>';
    print '</tr>';
    print '</table>';

    print '<table border="0" cellpadding="2" cellspacing="0" width="100%">';
    print '<tr>';
    print '<th>&nbsp;</th>';
    print '<th>Invoice Number</th>';
    print '<th>Start Date</th>';
    print '<th>End Date</th>';
    print '<th>Amount</th>';
    print '<th>Payment</th>';
    print '<th>Owing</th>';
    print '</tr>';

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
      if (isset($payments[$id])) {
        foreach ($payments[$id] as $payment) {
          $paid += $payment;
        }
      }

      $balance = round($total - $paid, 2);

      print '<tr>';
      print '<td class="' . $stripe . '"><a href="view.php?id=' . $row['invoice_id_public'] . '"><i class="fa fa-eye"></i></a></td>';
      print '<td class="' . $stripe . '">' . sprintf('SDWH-%05d', $row['invoice_number']) . '</td>';
      print '<td class="' . $stripe . '">' . $row['invoice_date_start'] . '</td>';
      print '<td class="' . $stripe . '">' . $row['invoice_date_end'] . '</td>';
      print '<td class="' . $stripe . '">' . sprintf('$%.2f', $total) . '</td>';
      print '<td class="' . $stripe . '">' . sprintf('$%.2f', $paid) . '</td>';
      print '<td class="' . $stripe . '">' . sprintf('$%.2f', $balance) . '</td>';
      print '</tr>';

      $stripe = ($stripe == 'even') ? 'odd' : 'even';
    }
    print '</table>';
  }
  else {
    print '<div class="table-no-data">No invoices</div>';
  }
}

print $main->getTheme()->htmlDashboardBottom();
