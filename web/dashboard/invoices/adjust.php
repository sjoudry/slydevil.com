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
  ->setAction('adjust.php')
  ->setName('invoice_adjust');

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
    
$adjustment = Text::create()
  ->setName('adjustment')
  ->addLabel('Adjustment')
  ->setClass('form-control')
  ->setDescription('The amount entered will be applied exactly as entered, so -6.00 will remove 6 dollars from an invoice and 6.00 will add 6 dollars to the invoice.')
  ->addValidatorExistance()
  ->addValidatorNumeric();

$reason = Text::create()
  ->setName('adjustment_reason')
  ->addLabel('Explanation')
  ->setClass('form-control')
  ->addValidatorExistance();

$button = Button::create()
  ->setName('payment_submit')
  ->setValue('Add Adjustment');

$fieldset = Fieldset::create()
  ->setId('payment_fieldset')
  ->setLegend('Add Adjustment')
  ->addElement($account)
  ->addElement($amount)
  ->addElement($hidden_invoice)
  ->addElement($adjustment)
  ->addElement($reason)
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

  $result = Database::query(
    "SELECT max(invoice_data_sequence) sequence FROM invoice_data WHERE invoice_id = %s",
    [
      $invoice
    ]
  );
    
  $next = $result->fetch_assoc();
  $next = $next['sequence'];

  Database::query(
    "INSERT INTO invoice_data (invoice_id, invoice_data_sequence, invoice_data_description, invoice_data_quantity, invoice_data_fee, invoice_data_indent) VALUES
    (%s, %s, '', 0, 0, 0)",
    [
      $invoice,
      ++$next
    ]
  );
    
  Database::query(
    "INSERT INTO invoice_data (invoice_id, invoice_data_sequence, invoice_data_description, invoice_data_quantity, invoice_data_fee, invoice_data_indent) VALUES
    (%s, %s, '%s', 1, %f, 0)",
    [
      $invoice,
      ++$next,
      'Adjustment: ' . $_REQUEST['adjustment_reason'],
      $_REQUEST['adjustment']
    ]
  );

  $_SESSION['messages']['info'][] = 'Adjustment added successfully';

  header('Location: /dashboard/invoices/');
  exit;
}

print Theme::htmlDashboardTop('Hosting :: Invoices :: Adjust Invoice');
print $form->returnHTML();
print Theme::htmlDashboardBottom();
