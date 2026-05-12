<?php

use SlyDevil\Database;
use SlyDevil\Env;
use SlyDevil\Form\Element\Button;
use SlyDevil\Form\Element\Fieldset;
use SlyDevil\Form\Element\Form;
use SlyDevil\Form\Element\Hidden;
use SlyDevil\Form\Element\Text;
use Slydevil\Login;
use SlyDevil\Theme;

include_once(__DIR__ . '/../../../includes/init.inc.php');

Login::handleLogin('admin');

$invoice_id = Env::filterVariable($_REQUEST['id']);

$result = Database::query(
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

$result = Database::query(
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
    
$result = Database::query(
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

$form = Form::create()
  ->setAction('pay.php')
  ->setName('invoice_pay');

$hidden_invoice = Hidden::create()
  ->setName('id')
  ->setValue($invoice_id);

$account = Text::create()
  ->setName('account')
  ->addLabel('Account')
  ->setReadOnly(TRUE)
  ->setValue($invoice['account_name'])
  ->setClass('form-control');

$amount = Text::create()
  ->setName('amount')
  ->addLabel('Amount')
  ->setReadOnly(TRUE)
  ->setValue(sprintf('$%.2f', $balance))
  ->setClass('form-control');
    
$payment = Text::create()
  ->setName('payment')
  ->addLabel('Payment')
  ->setClass('form-control')
  ->addValidatorExistance()
  ->addValidatorNumeric();

$button = Button::create()
  ->setName('payment_submit')
  ->setValue('Add Payment');

$fieldset = Fieldset::create()
  ->setId('payment_fieldset')
  ->setLegend('Add Payment')
  ->addElement($account)
  ->addElement($amount)
  ->addElement($hidden_invoice)
  ->addElement($payment)
  ->addElement($button);

$form->addElement($fieldset);

if ($form->submitted() && $form->validated()) {
  $result = Database::query(
    "SELECT invoice_id FROM invoice WHERE invoice_id_public = '%s'",
    [
      $invoice_id
    ]
  );

  $invoice = $result->fetch_assoc();
  $invoice = $invoice['invoice_id'];

  $result->close();

  Database::query(
    "INSERT INTO payment (invoice_id, payment_amount, payment_date_added) VALUES
    ('%s', %f, NOW())",
    [
      $invoice,
      $_REQUEST['payment']
    ]
  );

  $_SESSION['messages']['info'][] = 'Payment added successfully';

  header('Location: /dashboard/invoices/');
  exit;
}

print Theme::htmlDashboardTop('Hosting :: Invoices :: Add Payment');
print $form->returnHTML();
print Theme::htmlDashboardBottom();
