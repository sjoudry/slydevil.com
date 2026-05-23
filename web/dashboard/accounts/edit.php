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

$form = Form::create('accounts_edit');

$suffix = 'add';
$button_value = 'Add Account';
$account_id = NULL;
$account_name = '';
$account_company = '';
$account_street_address1 = '';
$account_street_address2 = '';
$account_city = '';
$account_province = '';
$account_postal = '';
$account_country = '';
$account_phone = '';
$account_id_master = '';
$package_id = '';
$account_billing = '';
$account_date_added = '';
$account_id_master_public = '';
if (isset($_REQUEST['id'])) {
  $suffix = 'edit';
  $button_value = 'Save Changes';
  $account_id = $main->getSessionManager()->filterVariable($_REQUEST['id']);

  $result = $main->getDatabase()->query(
    "SELECT *, package_id_public FROM account JOIN package USING (package_id) WHERE account_id_public = '%s'",
    [
      $account_id
    ]
  );

  if ($result->num_rows == 1) {
    $account = $result->fetch_assoc();

    $account_name = $account['account_name'];
    $account_company = $account['account_company'];
    $account_street_address1 = $account['account_street_address1'];
    $account_street_address2 = $account['account_street_address2'];
    $account_city = $account['account_city'];
    $account_province = $account['account_province'];
    $account_postal = $account['account_postal'];
    $account_country = $account['account_country'];
    $account_phone = $account['account_phone'];
    $account_id_master = $account['account_id_master'];
    $package_id = $account['package_id'];
    $account_billing = $account['account_billing'];
    $account_date_added = $account['account_date_added'];
  }

  $result->close();

  if ($account_id_master) {
    $result = $main->getDatabase()->query('SELECT account_id_public FROM account WHERE account_id = %s', [$account_id_master]);
    $parent = $result->fetch_assoc();
    $account_id_master_public = $parent['account_id_public'];
  }

  $hidden = Input::create('hidden', 'id')
    ->setAttribute('value', $account_id);

  $form->addElement($hidden);
}

$name = Input::create('text', 'account_name')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $account_name)
  ->setAttribute('class', 'form-control')
  ->addLabel('Account Name')
  ->addValidator('existance');

$company = Input::create('text', 'account_company')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $account_company)
  ->setAttribute('class', 'form-control')
  ->addLabel('Account Company');

$address1 = Input::create('text', 'account_street_address1')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $account_street_address1)
  ->setAttribute('class', 'form-control')
  ->addLabel('Account Street Address 1');

$address2 = Input::create('text', 'account_street_address2')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $account_street_address2)
  ->setAttribute('class', 'form-control')
  ->addLabel('Account Street Address 2');

$city = Input::create('text', 'account_city')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $account_city)
  ->setAttribute('class', 'form-control')
  ->addLabel('Account City/Town');

$province = Input::create('text', 'account_province')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $account_province)
  ->setAttribute('class', 'form-control')
  ->addLabel('Account Province/State');

$country = Input::create('text', 'account_country')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $account_country)
  ->setAttribute('class', 'form-control')
  ->addLabel('Account Country');

$postal = Input::create('text', 'account_postal')
  ->setAttribute('maxlength', 10)
  ->setAttribute('value', $account_postal)
  ->setAttribute('class', 'form-control')
  ->addLabel('Account Postal');

$phone = Input::create('text', 'account_phone')
  ->setAttribute('maxlength', 20)
  ->setAttribute('value', $account_phone)
  ->setAttribute('class', 'form-control')
  ->addLabel('Account Telephone');

$result = $main->getDatabase()->query('SELECT account_id_public, account_name FROM account WHERE account_date_deleted IS NULL ORDER BY account_name');
$accounts = ['0' => '--- Select Parent Account ---'];
while ($row = $result->fetch_assoc()) {
  $accounts[$row['account_id_public']] = $row['account_name'];
}

$parent = Select::create('account_id_master')
  ->setOptions($accounts)
  ->setSelected($account_id_master_public)
  ->addLabel('Account Parent')
  ->setAttribute('class', 'form-control');

$result = $main->getDatabase()->query('SELECT package_id_public, package_name FROM package WHERE package_date_deleted IS NULL ORDER BY package_name');
$packages = [];
while ($row = $result->fetch_assoc()) {
  $packages[$row['package_id_public']] = $row['package_name'];
}
$package = Select::create('package_id')
  ->setOptions($packages)
  ->setSelected($package_id)
  ->addLabel('Account Package')
  ->setAttribute('class', 'form-control');

$cycle = Select::create('account_billing')
  ->setOptions(
    [
      '1' => 'Monthly (12 invoices per year)',
      '2' => 'Quarterly (4 invoices per year)',
      '3' => 'Semi-Annually (2 invoices per year)',
      '4' => 'Annually (1 invoice per year)',
    ]
  )
  ->setSelected($account_billing)
  ->addLabel('Account Billing Cycle')
  ->setAttribute('class', 'form-control');

$button = Button::create('package_' . $suffix . '_submit', $button_value);

$fieldset = Fieldset::create('package_' . $suffix . '_fieldset', ucfirst($suffix) . ' Package')
  ->addElement($name)
  ->addElement($company)
  ->addElement($address1)
  ->addElement($address2)
  ->addElement($city)
  ->addElement($province)
  ->addElement($country)
  ->addElement($postal)
  ->addElement($phone)
  ->addElement($parent)
  ->addElement($package)
  ->addElement($cycle)
  ->addElement($button);

$form->addElement($fieldset);

if ($form->submitted() && $form->validated()) {
  $duplicate_sql = "SELECT account_id_public FROM account WHERE account_name = '%s'";
  $duplicate_args = [$main->getSessionManager()->filterVariable($_REQUEST['account_name'])];

  if ($account_id) {
    $duplicate_sql .= " AND account_id_public <> '%s'";
    $duplicate_args[] = $account_id;
  }

  $result = $main->getDatabase()->query($duplicate_sql, $duplicate_args);
  $count = $result->num_rows;
  $result->close();

  if ($count > 0) {
    $main->getErrorHandler()->addError('Account Name exists already.');
  }
  else {
    $result = $main->getDatabase()->query(
      "SELECT package_id FROM package WHERE package_id_public = '%s'",
      [
        $main->getSessionManager()->filterVariable($_REQUEST['package_id'])
      ]
    );

    $package = $result->fetch_assoc();
    $result->close();

    $master_id = 0;
    if (isset($_REQUEST['account_id_master'])) {
      $result = $main->getDatabase()->query(
        "SELECT account_id FROM account WHERE account_id_public = '%s'",
        [
          $main->getSessionManager()->filterVariable($_REQUEST['account_id_master'])
        ]
      );

      $account = $result->fetch_assoc();
      $master_id = $account['account_id'];
      $result->close();
    }

    if ($account_id) {
      $main->getDatabase()->query(
        "UPDATE account SET account_name = '%s', account_company = '%s', account_street_address1 = '%s', account_street_address2 = '%s', account_city = '%s', account_province = '%s', account_postal = '%s', account_country = '%s', account_phone = '%s', account_id_master = %d, package_id = %d, account_billing = %s WHERE account_id_public = '%s'",
        [
          $main->getSessionManager()->filterVariable($_REQUEST['account_name']),
          $main->getSessionManager()->filterVariable($_REQUEST['account_company']),
          $main->getSessionManager()->filterVariable($_REQUEST['account_street_address1']),
          $main->getSessionManager()->filterVariable($_REQUEST['account_street_address2']),
          $main->getSessionManager()->filterVariable($_REQUEST['account_city']),
          $main->getSessionManager()->filterVariable($_REQUEST['account_province']),
          $main->getSessionManager()->filterVariable($_REQUEST['account_postal']),
          $main->getSessionManager()->filterVariable($_REQUEST['account_country']),
          $main->getSessionManager()->filterVariable($_REQUEST['account_phone']),
          (int)$master_id,
          $package['package_id'],
          $main->getSessionManager()->filterVariable($_REQUEST['account_billing']),
          $account_id
        ]
      );

      $main->getSessionManager()->addMessage('Package updated successfully');
    }
    else {
      $main->getDatabase()->query(
        "INSERT INTO account (account_id_public, account_name, account_company, account_street_address1, account_street_address2, account_city, account_province, account_postal, account_country, account_phone, account_id_master, package_id, account_billing, account_date_added) VALUES
        ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %s, %s, %s, NOW())",
        [
          $main->getSessionManager()->cryptPassword($main->getSessionManager()->filterVariable($_REQUEST['account_name'])),
          $main->getSessionManager()->filterVariable($_REQUEST['account_name']),
          $main->getSessionManager()->filterVariable($_REQUEST['account_company']),
          $main->getSessionManager()->filterVariable($_REQUEST['account_street_address1']),
          $main->getSessionManager()->filterVariable($_REQUEST['account_street_address2']),
          $main->getSessionManager()->filterVariable($_REQUEST['account_city']),
          $main->getSessionManager()->filterVariable($_REQUEST['account_province']),
          $main->getSessionManager()->filterVariable($_REQUEST['account_postal']),
          $main->getSessionManager()->filterVariable($_REQUEST['account_country']),
          $main->getSessionManager()->filterVariable($_REQUEST['account_phone']),
          (int)$master_id,
          $package['package_id'],
          $main->getSessionManager()->filterVariable($_REQUEST['account_billing'])
        ]
      );

      $main->getSessionManager()->addMessage('New Account added successfully');
    }

    header('Location: /dashboard/accounts/');
    exit;
  }
}

print $main->getTheme()->htmlDashboardTop('Hosting :: Accounts :: ' . ucfirst($suffix));
print $form->render();
print $main->getTheme()->htmlDashboardBottom();
