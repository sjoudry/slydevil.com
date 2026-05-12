<?php

use SlyDevil\Database;
use SlyDevil\Form\Element\Base;
use SlyDevil\Form\Element\Button;
use SlyDevil\Form\Element\Checkbox;
use SlyDevil\Form\Element\Fieldset;
use SlyDevil\Form\Element\Form;
use SlyDevil\Form\Element\Select;
use Slydevil\Login;
use SlyDevil\Theme;

include_once(__DIR__ . '/../../../includes/init.inc.php');

Login::handleLogin('admin');

print Theme::htmlDashboardTop('Hosting :: Invoices');
print "<div class='button'><a href='reports.php'><i class='fa fa-bar-chart-o'></i> View Reports</a></div>\n";

// filter form prep
$result = Database::query(
  'SELECT
    account_id_public,
    account_name
  FROM
    account
  ORDER BY
    account_name'
);
$accounts = ['0' => '-- Select Account --'];
while ($row = $result->fetch_assoc()) {
  $accounts[$row['account_id_public']] = $row['account_name'];
}

$form = Form::create()
  ->setAction('index.php')
  ->setName('invoice_filter');

$account = Select::create()
  ->setName('account_id')
  ->setOptions($accounts)
  ->setClass('form-control')
  ->addLabel('Account');

$paid = Checkbox::create()
  ->setName('paid_invoices')
  ->setValue(1)
  ->setChecked((empty($_REQUEST['paid_invoices'])) ? FALSE : TRUE)
  ->addLabel('Include Paid Invoices', Base::FORM_ELEMENT_LABEL_ALIGN_RIGHT);
    
$button = Button::create()
  ->setName('invoice_filter_submit')
  ->setValue('Filter');
    
$fieldset = Fieldset::create()
  ->setId('invoice_filter_fieldset')
  ->setLegend("<i class='fa fa-filter'></i> Filter Invoices")
  ->setCollapsible(TRUE)
  ->setCollapsed((isset($_REQUEST['account_id']) || !empty($_REQUEST['paid_invoices'])) ? FALSE : TRUE)
  ->addElement($account)
  ->addElement($paid)
  ->addElement($button);

$form->addElement($fieldset);

if ($form->submitted()) {
  $account->setSelected($_REQUEST['account_id']);

  $button2 = Button::create()
    ->setName('invoices_filter_reset')
    ->setValue('Reset');
        
  $fieldset->addElement($button2);
}

if (isset($_REQUEST['invoices_filter_reset']) || (isset($_REQUEST['account_id']) && empty($_REQUEST['account_id']) && empty($_REQUEST['paid_invoices']))) {
  header('Location: /dashboard/invoices/');
  exit;
}

print $form->returnHTML();

$args = [];
$sql = '
  SELECT
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
';
if ($form->submitted()) {
  if (isset($_REQUEST['account_id']) && !empty($_REQUEST['account_id'])) {
    $sql .= "AND account.account_id_public = '%s'";
    $args[] = $_REQUEST['account_id'];
  }
}
$sql .= 'ORDER BY invoice_number DESC';

$result = Database::query($sql, $args);

if ($result->num_rows) {
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
    
  $result = Database::query(
    'SELECT
      invoice_id,
      payment_amount
    FROM
      payment
    WHERE
      invoice_id IN (%s)',
    [
      implode(',', array_keys($invoices))
    ]
  );

  $payments = [];
  while ($row = $result->fetch_assoc()) {
    $payments[$row['invoice_id']][] = $row['payment_amount'];
  }
    
  $count = 0;
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
        
    if (empty($_REQUEST['paid_invoices']) && $balance == 0) {
      continue;
    }

    $count++;
  }

  if ($count) {
    print "<table border='0' cellpadding='2' cellspacing='0' width='100%'>\n";
    print "<tr>\n";
    print "<th>&nbsp;</th>\n";
    print "<th>&nbsp;</th>\n";
    print "<th>&nbsp;</th>\n";
    print "<th>Invoice Number</th>\n";
    print "<th>Start Date</th>\n";
    print "<th>End Date</th>\n";
    print "<th>Account</th>\n";
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
      if (isset($payments[$id])) {
        foreach ($payments[$id] as $payment) {
          $paid += $payment;
        }
      }
            
      $balance = round($total - $paid, 2);
            
      if (empty($_REQUEST['paid_invoices']) && $balance == 0) {
        continue;
      }

      print "<tr>\n";
      print "<td class='" . $stripe . "'><a href='view.php?id=" . $row["invoice_id_public"] . "'><i class='fa fa-eye'></i></a></td>\n";
      print "<td class='" . $stripe . "'><a href='pay.php?id=" . $row["invoice_id_public"] . "'><i class='fa fa-usd'></i></a></td>\n";
      print "<td class='" . $stripe . "'><a href='adjust.php?id=" . $row["invoice_id_public"] . "'><i class='fa fa-pencil'></i></a></td>\n";
      print "<td class='" . $stripe . "'>" . sprintf("SDWH-%05d", $row["invoice_number"]) . "</td>\n";
      print "<td class='" . $stripe . "'>" . $row["invoice_date_start"] . "</td>\n";
      print "<td class='" . $stripe . "'>" . $row["invoice_date_end"] . "</td>\n";
      print "<td class='" . $stripe . "'>" . $row["account_name"] . "</td>\n";
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
else {
  print "<div class='table-no-data'>No invoices</div>\n";
}

print Theme::htmlDashboardBottom();