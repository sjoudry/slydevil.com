<?php

use SlyDevil\Database;
use SlyDevil\Env;
use SlyDevil\Form\Element\Button;
use SlyDevil\Form\Element\Fieldset;
use SlyDevil\Form\Element\Form;
use SlyDevil\Form\Element\Hidden;
use SlyDevil\Form\Element\Select;
use SlyDevil\Form\Element\Text;
use Slydevil\Login;
use SlyDevil\Session;
use SlyDevil\Theme;

include_once(__DIR__ . '/../../../includes/init.inc.php');

Login::handleLogin('admin');

$form = Form::create()
  ->setAction('edit.php')
  ->setName('services_edit');

$suffix = 'add';
$button_value = 'Add Service';
$service_id = NULL;
$service_name = '';
$service_fee = '';
$account_id = '';
if (isset($_REQUEST['id'])) {
  $suffix = 'edit';
  $button_value = 'Save Changes';
  $service_id = Env::filterVariable($_REQUEST['id']);
    
  $result = Database::query(
    "SELECT * FROM service JOIN account USING (account_id) WHERE service_id_public = '%s'",
    [
      $service_id
    ]
  );
                
  if ($result->num_rows == 1) {
    $service = $result->fetch_assoc();

    $service_name = $service['service_name'];
    $service_fee  = $service['service_fee'];
    $account_id   = $service['account_id_public'];
  }

  $result->close();
    
  $hidden = Hidden::create()
    ->setName('id')
    ->setValue($service_id);

  $form->addElement($hidden);
}

$name = Text::create()
  ->setName('service_name')
  ->setMaxlength(255)
  ->setValue($service_name)
  ->addLabel('Service Name')
  ->setClass('form-control')
  ->addValidatorExistance();

$fee = Text::create()
  ->setName('service_fee')
  ->setMaxlength(6)
  ->setValue($service_fee)
  ->addLabel('Service Fee')
  ->setClass('form-control')
  ->addValidatorExistance()
  ->addValidatorNumeric();

$result = Database::query('SELECT account_id_public, account_name FROM account WHERE account_date_deleted IS NULL ORDER BY account_name');
$accounts = ['0' => '--- Select Account ---'];
while ($row = $result->fetch_assoc()) {
  $accounts[$row['account_id_public']] = $row['account_name'];
}
$account = Select::create()
  ->setName('account_id')
  ->setOptions($accounts)
  ->setSelected($account_id)
  ->addLabel('Account')
  ->setClass('form-control')
  ->addValidatorExistance();

$button = Button::create()
  ->setName('service_' . $suffix . '_submit')
  ->setValue($button_value);

$fieldset = Fieldset::create()
  ->setId('service_' . $suffix . '_fieldset')
  ->setLegend(ucfirst($suffix) . ' Service')
  ->addElement($name)
  ->addElement($fee)
  ->addElement($account)
  ->addElement($button);

$form->addElement($fieldset);
 
if ($form->submitted() && $form->validated()) {
  $result = Database::query(
    "SELECT account_id FROM account WHERE account_id_public = '%s'",
    [
      $_REQUEST['account_id']
    ]
  );

  $account = $result->fetch_assoc();
  $account_id = $account['account_id'];
  $result->close();
        
  $duplicate_sql = "SELECT service_id_public FROM service WHERE service_name = '%s' AND account_id = %s";
  $duplicate_args = [
    $_REQUEST['service_name'],
    $account_id
  ];

  if ($service_id) {
    $duplicate_sql .= " AND service_id_public <> '%s'";
    $duplicate_args[] = $service_id;
  }

  $result = Database::query($duplicate_sql, $duplicate_args);
  $count = $result->num_rows;
  $result->close();

  if ($count > 0) {
    $form->addError('Service Name exists already.');
  }
  else {
    if ($service_id) {
      Database::query(
        "UPDATE service SET service_name = '%s', service_fee = %.2f, account_id = %s WHERE service_id_public = '%s'",
        [
          $_REQUEST['service_name'],
          $_REQUEST['service_fee'],
          $account_id,
          $service_id
        ]
      );
            
      $_SESSION['messages']['info'][] = 'Service updated successfully';
    }
    else {
      Database::query(
        "INSERT INTO service (service_id_public, service_name, service_fee, account_id, service_date_added) VALUES
        ('%s', '%s', %.2f, %s, NOW())",
        [
          Session::cryptPassword($_REQUEST['service_name'] . time()),
          $_REQUEST['service_name'],
          $_REQUEST['service_fee'],
          $account_id,
        ]
      );

      $_SESSION['messages']['info'][] = 'New Service added successfully';
    }
        
    header('Location: /dashboard/services/');
    exit;
  }
}

print Theme::htmlDashboardTop('Hosting :: Packages :: ' . ucfirst($suffix));
print $form->returnHTML();
print Theme::htmlDashboardBottom();
