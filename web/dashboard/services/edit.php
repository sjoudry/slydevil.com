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

$form = Form::create('services_edit');

$suffix = 'add';
$button_value = 'Add Service';
$service_id = NULL;
$service_name = '';
$service_fee = '';
$account_id = '';
if (isset($_REQUEST['id'])) {
  $suffix = 'edit';
  $button_value = 'Save Changes';
  $service_id = $main->getSessionManager()->filterVariable($_REQUEST['id']);

  $result = $main->getDatabase()->query(
    "SELECT * FROM service JOIN account USING (account_id) WHERE service_id_public = '%s'",
    [
      $service_id
    ]
  );

  if ($result->num_rows == 1) {
    $service = $result->fetch_assoc();

    $service_name = $service['service_name'];
    $service_fee = $service['service_fee'];
    $account_id = $service['account_id_public'];
  }

  $result->close();

  $hidden = Input::create('hidden', 'id')
    ->setAttribute('value', $service_id);

  $form->addElement($hidden);
}

$name = Input::create('text', 'service_name')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $service_name)
  ->setAttribute('class', 'form-control')
  ->addLabel('Service Name')
  ->addValidator('existance');

$fee = Input::create('text', 'service_fee')
  ->setAttribute('maxlength', 6)
  ->setAttribute('value', $service_fee)
  ->setAttribute('class', 'form-control')
  ->addLabel('Service Fee')
  ->addValidator('existance')
  ->addValidator('numeric');

$result = $main->getDatabase()->query('SELECT account_id_public, account_name FROM account WHERE account_date_deleted IS NULL ORDER BY account_name');
$accounts = ['0' => '--- Select Account ---'];
while ($row = $result->fetch_assoc()) {
  $accounts[$row['account_id_public']] = $row['account_name'];
}
$account = Select::create('account_id')
  ->setOptions($accounts)
  ->setSelected($account_id)
  ->addLabel('Account')
  ->setAttribute('class', 'form-control')
  ->addValidator('existance');

$button = Button::create('service_' . $suffix . '_submit', $button_value);

$fieldset = Fieldset::create('service_' . $suffix . '_fieldset', ucfirst($suffix) . ' Service')
  ->addElement($name)
  ->addElement($fee)
  ->addElement($account)
  ->addElement($button);

$form->addElement($fieldset);

if ($form->submitted() && $form->validated()) {
  $result = $main->getDatabase()->query(
    "SELECT account_id FROM account WHERE account_id_public = '%s'",
    [
      $main->getSessionManager()->filterVariable($_REQUEST['account_id'])
    ]
  );

  $account = $result->fetch_assoc();
  $account_id = $account['account_id'];
  $result->close();

  $duplicate_sql = "SELECT service_id_public FROM service WHERE service_name = '%s' AND account_id = %s";
  $duplicate_args = [
    $main->getSessionManager()->filterVariable($_REQUEST['service_name']),
    $account_id
  ];

  if ($service_id) {
    $duplicate_sql .= " AND service_id_public <> '%s'";
    $duplicate_args[] = $service_id;
  }

  $result = $main->getDatabase()->query($duplicate_sql, $duplicate_args);
  $count = $result->num_rows;
  $result->close();

  if ($count > 0) {
    $main->getErrorHandler()->addError('Service Name exists already.');
  }
  else {
    if ($service_id) {
      $main->getDatabase()->query(
        "UPDATE service SET service_name = '%s', service_fee = %.2f, account_id = %s WHERE service_id_public = '%s'",
        [
          $main->getSessionManager()->filterVariable($_REQUEST['service_name']),
          $main->getSessionManager()->filterVariable($_REQUEST['service_fee']),
          $account_id,
          $service_id
        ]
      );

      $main->getSessionManager()->addMessage('Service updated successfully');
    }
    else {
      $main->getDatabase()->query(
        "INSERT INTO service (service_id_public, service_name, service_fee, account_id, service_date_added) VALUES
        ('%s', '%s', %.2f, %s, NOW())",
        [
          $main->getSessionManager()->cryptPassword($main->getSessionManager()->filterVariable($_REQUEST['service_name'] . time())),
          $main->getSessionManager()->filterVariable($_REQUEST['service_name']),
          $main->getSessionManager()->filterVariable($_REQUEST['service_fee']),
          $account_id,
        ]
      );

      $main->getSessionManager()->addMessage('New Service added successfully');
    }

    header('Location: /dashboard/services/');
    exit;
  }
}

print $main->getTheme()->htmlDashboardTop('Hosting :: Packages :: ' . ucfirst($suffix));
print $form->render();
print $main->getTheme()->htmlDashboardBottom();
