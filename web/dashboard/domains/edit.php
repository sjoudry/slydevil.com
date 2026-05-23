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

$form = Form::create('domains_edit');

$suffix = 'add';
$button_value = 'Add Domain';
$domain_id = NULL;
$domain_name = '';
$account_id = '';
if (isset($_REQUEST['id'])) {
  $suffix = 'edit';
  $button_value = 'Save Changes';
  $domain_id = $main->getSessionManager()->filterVariable($_REQUEST['id']);

  $result = $main->getDatabase()->query(
    "SELECT *, account_id_public FROM domain JOIN account USING (account_id) WHERE domain_id_public = '%s'",
    [
      $domain_id
    ]
  );

  if ($result->num_rows == 1) {
    $domain = $result->fetch_assoc();

    $domain_name = $domain['domain_name'];
    $account_id  = $domain['account_id_public'];
  }

  $result->close();

  $hidden = Input::create('hidden', 'id')
    ->setAttribute('value', $domain_id);

  $form->addElement($hidden);
}

$name = Input::create('text', 'domain_name')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $domain_name)
  ->setAttribute('class', 'form-control')
  ->addLabel('Domain Name')
  ->addValidator('existance');

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

$button = Button::create('domain_' . $suffix . '_submit', $button_value);

$fieldset = Fieldset::create('package_' . $suffix . '_fieldset', ucfirst($suffix) . ' Domain')
  ->addElement($name)
  ->addElement($account)
  ->addElement($button);

$form->addElement($fieldset);

if ($form->submitted() && $form->validated()) {
  $duplicate_sql = "SELECT domain_id_public FROM domain WHERE domain_name = '%s'";
  $duplicate_args = [$main->getSessionManager()->filterVariable($_REQUEST['domain_name'])];

  if ($domain_id) {
    $duplicate_sql .= " AND domain_id_public <> '%s'";
    $duplicate_args[] = $domain_id;
  }

  $result = $main->getDatabase()->query($duplicate_sql, $duplicate_args);
  $count = $result->num_rows;
  $result->close();

  if ($count > 0) {
    $main->getErrorHandler()->addError('Domain Name exists already.');
  }
  else {
    $result = $main->getDatabase()->query(
      "SELECT account_id FROM account WHERE account_id_public = '%s'",
      [
        $main->getSessionManager()->filterVariable($_REQUEST['account_id'])
      ]
    );

    $account = $result->fetch_assoc();
    $account_id = $account['account_id'];
    $result->close();

    if ($domain_id) {
      $main->getDatabase()->query(
        "UPDATE domain SET domain_name = '%s', account_id = %s WHERE domain_id_public = '%s'",
        [
          $main->getSessionManager()->filterVariable($_REQUEST['domain_name']),
          $account_id,
          $domain_id
        ]
      );

      $main->getSessionManager()->addMessage('Domain updated successfully');
    }
    else {
      $main->getDatabase()->query(
        "INSERT INTO domain (domain_id_public, domain_name, account_id, domain_date_added) VALUES
        ('%s', '%s', %s, NOW())",
        [
          $main->getSessionManager()->cryptPassword($main->getSessionManager()->filterVariable($_REQUEST['domain_name'])),
          $main->getSessionManager()->filterVariable($_REQUEST['domain_name']),
          $account_id
        ]
      );

      $main->getSessionManager()->addMessage('New Domain added successfully');
    }

    header('Location: /dashboard/domains/');
    exit;
  }
}

print $main->getTheme()->htmlDashboardTop('Hosting :: Domains :: ' . ucfirst($suffix));
print $form->render();
print $main->getTheme()->htmlDashboardBottom();