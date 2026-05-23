<?php

use SlyDevil\Form\Element\Button;
use SlyDevil\Form\Element\Fieldset;
use SlyDevil\Form\Element\Form;
use SlyDevil\Form\Element\Input;
use SlyDevil\Form\Element\Select;
use SlyDevil\Site\Main;

include_once(__DIR__ . '/../../../includes/init.inc.php');

$main = new Main();
$main->getLogin()->handle('admin');

print $main->getTheme()->htmlDashboardTop('Hosting :: Invoices');
print '<div class="button"><a href="reports.php"><i class="fa fa-bar-chart-o"></i> View Reports</a></div>';

// filter form prep
$result = $main->getDatabase()->query(
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

$form = Form::create('invoice_filter');

$account = Select::create('account_id')
  ->setOptions($accounts)
  ->setAttribute('class', 'form-control')
  ->addLabel('Account');

$paid = Input::create('checkbox', 'paid_invoices')
  ->setAttribute('value', 1)
  ->setAttribute('checked', (empty($_REQUEST['paid_invoices'])) ? FALSE : TRUE)
  ->addLabel('Include Paid Invoices', Input::FORM_ELEMENT_LABEL_ALIGN_RIGHT);

$button = Button::create('invoice_filter_submit', 'Filter');

$fieldset = Fieldset::create('invoice_filter_fieldset', '<i class="fa fa-filter"></i> Filter Invoices')
  ->setCollapsible(TRUE)
  ->setCollapsed((isset($_REQUEST['account_id']) || !empty($_REQUEST['paid_invoices'])) ? FALSE : TRUE)
  ->addElement($account)
  ->addElement($paid)
  ->addElement($button);

$form->addElement($fieldset);

if ($form->submitted()) {
  $account->setSelected($main->getSessionManager()->filterVariable($_REQUEST['account_id']));

  $button2 = Button::create('invoices_filter_reset', 'Reset');

  $fieldset->addElement($button2);
}

if (isset($_REQUEST['invoices_filter_reset']) || (isset($_REQUEST['account_id']) && empty($_REQUEST['account_id']) && empty($_REQUEST['paid_invoices']))) {
  header('Location: /dashboard/invoices/');
  exit;
}

print $form->render();

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
    $args[] = $main->getSessionManager()->filterVariable($_REQUEST['account_id']);
  }
}
$sql .= 'ORDER BY invoice_number DESC';

$result = $main->getDatabase()->query($sql, $args);

if ($result->num_rows) {
  $invoices = [];
  while ($row = $result->fetch_assoc()) {
    $invoices[$row['invoice_id']] = $row;
  }

  $result = $main->getDatabase()->query(
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

  $result = $main->getDatabase()->query(
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
    print '<table border="0" cellpadding="2" cellspacing="0" width="100%">';
    print '<tr>';
    print '<th>&nbsp;</th>';
    print '<th>&nbsp;</th>';
    print '<th>&nbsp;</th>';
    print '<th>Invoice Number</th>';
    print '<th>Start Date</th>';
    print '<th>End Date</th>';
    print '<th>Account</th>';
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

      if (empty($_REQUEST['paid_invoices']) && $balance == 0) {
        continue;
      }

      print '<tr>';
      print '<td class="' . $stripe . '"><a href="view.php?id=' . $row['invoice_id_public'] . '"><i class="fa fa-eye"></i></a></td>';
      print '<td class="' . $stripe . '"><a href="pay.php?id=' . $row['invoice_id_public'] . '"><i class="fa fa-usd"></i></a></td>';
      print '<td class="' . $stripe . '"><a href="adjust.php?id=' . $row['invoice_id_public'] . '"><i class="fa fa-pencil"></i></a></td>';
      print '<td class="' . $stripe . '">' . sprintf('SDWH-%05d', $row['invoice_number']) . '</td>';
      print '<td class="' . $stripe . '">' . $row['invoice_date_start'] . '</td>';
      print '<td class="' . $stripe . '">' . $row['invoice_date_end'] . '</td>';
      print '<td class="' . $stripe . '">' . $row['account_name'] . '</td>';
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
else {
  print '<div class="table-no-data">No invoices</div>';
}

print $main->getTheme()->htmlDashboardBottom();