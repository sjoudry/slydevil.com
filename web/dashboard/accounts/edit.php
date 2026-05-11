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
  ->setAction('/dashboard/accounts/edit.php')
  ->setName('accounts_edit');

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
if (isset($_REQUEST['id'])) {
  $suffix = 'edit';
  $button_value = 'Save Changes';
  $account_id = Env::filterVariable($_REQUEST['id']);

  $result = Database::query(
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

  $account_id_master_public = '';
  if ($account_id_master) {
    $result = Database::query('SELECT account_id_public FROM account WHERE account_id = %s', [$account_id_master]);
    $parent = $result->fetch_assoc();
    $account_id_master_public = $parent['account_id_public'];
  }

  $hidden = Hidden::create()
    ->setName('id')
    ->setValue($account_id);

  $form->addElement($hidden);
}

$name = Text::create()
  ->setName('account_name')
  ->setMaxlength(255)
  ->setValue($account_name)
  ->addLabel('Account Name')
  ->setClass('form-control')
  ->addValidatorExistance();

$company = Text::create()
  ->setName('account_company')
  ->setMaxlength(255)
  ->setValue($account_company)
  ->addLabel('Account Company')
  ->setClass('form-control');

$address1 = Text::create()
  ->setName('account_street_address1')
  ->setMaxlength(255)
  ->setValue($account_street_address1)
  ->addLabel('Account Street Address 1')
  ->setClass('form-control');

$address2 = Text::create()
  ->setName('account_street_address2')
  ->setMaxlength(255)
  ->setValue($account_street_address2)
  ->addLabel('Account Street Address 2')
  ->setClass('form-control');

$city = Text::create()
  ->setName('account_city')
  ->setMaxlength(255)
  ->setValue($account_city)
  ->addLabel('Account City/Town')
  ->setClass('form-control');

$province = Text::create()
  ->setName('account_province')
  ->setMaxlength(255)
  ->setValue($account_province)
  ->addLabel('Account Province/State')
  ->setClass('form-control');

$country = Text::create()
  ->setName('account_country')
  ->setMaxlength(255)
  ->setValue($account_country)
  ->addLabel('Account Country')
  ->setClass('form-control');

$postal = Text::create()
  ->setName('account_postal')
  ->setMaxlength(10)
  ->setValue($account_postal)
  ->addLabel('Account Postal')
  ->setClass('form-control');

$phone = Text::create()
  ->setName('account_phone')
  ->setMaxlength(20)
  ->setValue($account_phone)
  ->addLabel('Account Telephone')
  ->setClass('form-control');

$result = Database::query('SELECT account_id_public, account_name FROM account WHERE account_date_deleted IS NULL ORDER BY account_name');
$accounts = ['0' => '--- Select Parent Account ---'];
while ($row = $result->fetch_assoc()) {
  $accounts[$row['account_id_public']] = $row['account_name'];
}

$parent = Select::create()
  ->setName('account_id_master')
  ->setOptions($accounts)
  ->setSelected($account_id_master_public)
  ->addLabel('Account Parent')
  ->setClass('form-control');

$result = Database::query('SELECT package_id_public, package_name FROM package WHERE package_date_deleted IS NULL ORDER BY package_name');
$packages = [];
while ($row = $result->fetch_assoc()) {
  $packages[$row['package_id_public']] = $row['package_name'];
}
$package = Select::create()
  ->setName('package_id')
  ->setOptions($packages)
  ->setSelected($package_id)
  ->addLabel('Account Package')
  ->setClass('form-control');

$cycle = Select::create()
  ->setName('account_billing')
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
  ->setClass('form-control');

$button = Button::create()
  ->setName('package_' . $suffix . '_submit')
  ->setValue($button_value);

$fieldset = Fieldset::create()
  ->setId('package_' . $suffix . '_fieldset')
  ->setLegend(ucfirst($suffix) . ' Package')
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
  $duplicate_args = [$_REQUEST['account_name']];

  if ($account_id) {
    $duplicate_sql .= " AND account_id_public <> '%s'";
    $duplicate_args[] = $account_id;
  }

  $result = Database::query($duplicate_sql, $duplicate_args);
  $count = $result->num_rows;
  $result->close();

  if ($count > 0) {
    $form->addError('Account Name exists already.');
  }
  else {
    $result = Database::query(
      "SELECT package_id FROM package WHERE package_id_public = '%s'",
      [
        $_REQUEST['package_id']
      ]
    );

    $package = $result->fetch_assoc();
    $result->close();
        
    $master_id = 0;
    if (isset($_REQUEST['account_id_master'])) {
      $result = Database::query(
        "SELECT account_id FROM account WHERE account_id_public = '%s'",
        [
          $_REQUEST['account_id_master']
        ]
      );

      $account = $result->fetch_assoc();
      $master_id = $account['account_id'];
      $result->close();
    }

    if ($account_id) {
      Database::query(
        "UPDATE account SET account_name = '%s', account_company = '%s', account_street_address1 = '%s', account_street_address2 = '%s', account_city = '%s', account_province = '%s', account_postal = '%s', account_country = '%s', account_phone = '%s', account_id_master = %d, package_id = %d, account_billing = %s WHERE account_id_public = '%s'",
        [
          $_REQUEST['account_name'],
          $_REQUEST['account_company'],
          $_REQUEST['account_street_address1'],
          $_REQUEST['account_street_address2'],
          $_REQUEST['account_city'],
          $_REQUEST['account_province'],
          $_REQUEST['account_postal'],
          $_REQUEST['account_country'],
          $_REQUEST['account_phone'],
          (int)$master_id,
          $package['package_id'],
          $_REQUEST['account_billing'],
          $account_id
        ]
      );

      $_SESSION['messages']['info'][] = 'Package updated successfully';
    }
    else {
      Database::query(
        "INSERT INTO account (account_id_public, account_name, account_company, account_street_address1, account_street_address2, account_city, account_province, account_postal, account_country, account_phone, account_id_master, package_id, account_billing, account_date_added) VALUES
        ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %s, %s, %s, NOW())",
        [
          Session::cryptPassword($_REQUEST['account_name']),
          $_REQUEST['account_name'],
          $_REQUEST['account_company'],
          $_REQUEST['account_street_address1'],
          $_REQUEST['account_street_address2'],
          $_REQUEST['account_city'],
          $_REQUEST['account_province'],
          $_REQUEST['account_postal'],
          $_REQUEST['account_country'],
          $_REQUEST['account_phone'],
          (int)$master_id,
          $package['package_id'],
          $_REQUEST['account_billing']
        ]
      );

      $_SESSION['messages']['info'][] = 'New Account added successfully';
    }

    header('Location: /dashboard/accounts/');
    exit;
  }
}

print Theme::htmlDashboardTop('Hosting :: Accounts :: ' . ucfirst($suffix));
print $form->returnHTML();
print Theme::htmlDashboardBottom();
