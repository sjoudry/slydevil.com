<?php

use SlyDevil\Form\Element\Button;
use SlyDevil\Form\Element\Fieldset;
use SlyDevil\Form\Element\Form;
use SlyDevil\Form\Element\Input;
use SlyDevil\Site\Main;

include_once(__DIR__ . '/../../../includes/init.inc.php');

$main = new Main();
$main->getLogin()->handle('admin');

$invoice_id = $main->getSessionManager()->filterVariable($_REQUEST['id']);

$result = $main->getDatabase()->query(
  "SELECT
    invoice_id,
    invoice_gst_rate,
    invoice_pst_rate,
    account_name
  FROM
    invoice
  JOIN
    account
  USING
    (account_id)
  WHERE
    invoice_id_public = '%s'",
  [
    $invoice_id
  ]
);

$invoice = $result->fetch_assoc();

$result = $main->getDatabase()->query(
  "SELECT
    *
  FROM
    invoice_data
  WHERE
    invoice_id = '%s'",
  [
    $invoice['invoice_id']
  ]
);

$total = 0;
while ($row = $result->fetch_assoc()) {
  $total += round(($row['invoice_data_quantity'] * $row['invoice_data_fee']), 2);
}

$result = $main->getDatabase()->query(
  "SELECT
    payment_amount
  FROM
    payment
  WHERE
    invoice_id = '%s'",
  [
    $invoice['invoice_id']
  ]
);

$payments = 0;
while ($row = $result->fetch_assoc()) {
  $payments += $row['payment_amount'];
}

$calculated_gst = round(($total * $invoice['invoice_gst_rate']), 2);
$calculated_pst = round(($total * $invoice['invoice_pst_rate']), 2);
$total += $calculated_gst + $calculated_pst;
$balance = round($total - $payments, 2);

$form = Form::create('invoice_pay');

$hidden_invoice = Input::create('hidden', 'id')
  ->setAttribute('value', $invoice_id);

$account = Input::create('text', 'account')
  ->addLabel('Account')
  ->setAttribute('readonly', TRUE)
  ->setAttribute('value', $invoice['account_name'])
  ->setAttribute('class', 'form-control');

$amount = Input::create('text', 'amount')
  ->addLabel('Amount')
  ->setAttribute('readonly', TRUE)
  ->setAttribute('value', sprintf('$%.2f', $balance))
  ->setAttribute('class', 'form-control');

$payment = Input::create('text', 'payment')
  ->addLabel('Payment')
  ->setAttribute('class', 'form-control')
  ->addValidator('existance')
  ->addValidator('numeric');

$button = Button::create('payment_submit', 'Add Payment');

$fieldset = Fieldset::create('payment_fieldset', 'Add Payment')
  ->addElement($account)
  ->addElement($amount)
  ->addElement($hidden_invoice)
  ->addElement($payment)
  ->addElement($button);

$form->addElement($fieldset);

if ($form->submitted() && $form->validated()) {
  $result = $main->getDatabase()->query(
    "SELECT invoice_id FROM invoice WHERE invoice_id_public = '%s'",
    [
      $invoice_id
    ]
  );

  $invoice = $result->fetch_assoc();
  $invoice = $invoice['invoice_id'];

  $result->close();

  $main->getDatabase()->query(
    "INSERT INTO payment (invoice_id, payment_amount, payment_date_added) VALUES
    ('%s', %f, NOW())",
    [
      $invoice,
      $main->getSessionManager()->filterVariable($_REQUEST['payment'])
    ]
  );

  $main->getSessionManager()->addMessage('Payment added successfully');

  header('Location: /dashboard/invoices/');
  exit;
}

print $main->getTheme()->htmlDashboardTop('Hosting :: Invoices :: Add Payment');
print $form->render();
print $main->getTheme()->htmlDashboardBottom();
