<?php

use SlyDevil\Form\Element\Button;
use SlyDevil\Form\Element\Fieldset;
use SlyDevil\Form\Element\Form;
use SlyDevil\Form\Element\Input;
use SlyDevil\Form\Element\Select;
use SlyDevil\Form\Utility\ValidatorManager;
use Slydevil\Site\Login;
use SlyDevil\Site\Main;

include_once(__DIR__ . '/../../includes/init.inc.php');

$main = new Main();
$main->getLogin()->handle('user');

$form = Form::create('account_details');

$result = $main->getDatabase()->query(
  'SELECT * FROM account JOIN user USING (account_id) WHERE user_id = %s',
  [
    $main->getLogin()->getUserId()
  ]
);
$account = $result->fetch_assoc();

$company = Input::create('text', 'account_company')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $account['account_company'])
  ->setAttribute('class', 'form-control')
  ->addLabel('Account Company');

$address1 = Input::create('text', 'account_street_address1')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $account['account_street_address1'])
  ->setAttribute('class', 'form-control')
  ->addLabel('Account Street Address 1');

$address2 = Input::create('text', 'account_street_address2')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $account['account_street_address2'])
  ->setAttribute('class', 'form-control')
  ->addLabel('Account Street Address 2');

$city = Input::create('text', 'account_city')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $account['account_city'])
  ->setAttribute('class', 'form-control')
  ->addLabel('Account City/Town');

$province = Input::create('text', 'account_province')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $account['account_province'])
  ->setAttribute('class', 'form-control')
  ->addLabel('Account Province/State');

$country = Input::create('text', 'account_country')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $account['account_country'])
  ->setAttribute('class', 'form-control')
  ->addLabel('Account Country');

$postal = Input::create('text', 'account_postal')
  ->setAttribute('maxlength', 10)
  ->setAttribute('value', $account['account_postal'])
  ->setAttribute('class', 'form-control')
  ->addLabel('Account Postal');

$phone = Input::create('text', 'account_phone')
  ->setAttribute('maxlength', 20)
  ->setAttribute('value', $account['account_phone'])
  ->setAttribute('class', 'form-control')
  ->addLabel('Account Telephone');

$cycle = Select::create('account_billing')
  ->setOptions(
    [
      '1' => 'Monthly (12 invoices per year)',
      '2' => 'Quarterly (4 invoices per year)',
      '3' => 'Semi-Annually (2 invoices per year)',
      '4' => 'Annually (1 invoice per year)',
    ]
  )
  ->setSelected($account['account_billing'])
  ->addLabel('Account Billing Cycle')
  ->setAttribute('class', 'form-control');

$account_fieldset = Fieldset::create('details_fieldset', 'Account Details')
  ->addElement($company)
  ->addElement($address1)
  ->addElement($address2)
  ->addElement($city)
  ->addElement($province)
  ->addElement($country)
  ->addElement($postal)
  ->addElement($phone)
  ->addElement($cycle);

$username = Input::create('text', 'user_username')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $account['user_username'])
  ->setAttribute('class', 'form-control')
  ->addLabel('User Email Address')
  ->addValidator('existance')
  ->addValidator('email');

$first_name = Input::create('text', 'user_first_name')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $account['user_first_name'])
  ->setAttribute('class', 'form-control')
  ->addLabel('User First Name')
  ->addValidator('existance');

$last_name = Input::create('text', 'user_last_name')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $account['user_last_name'])
  ->setAttribute('class', 'form-control')
  ->addLabel('User Last Name')
  ->addValidator('existance');

$password = Input::create('password', 'user_password')
  ->setAttribute('maxlength', 16)
  ->setAttribute('class', 'form-control')
  ->addLabel('User Password')
  ->addValidator('contains_lowercase')
  ->addValidator('contains_number')
  ->addValidator('contains_uppercase')
  ->addValidator('length_long', NULL, 16)
  ->addValidator('length_short', NULL, 8)
  ->addValidator('match', NULL, $username, ValidatorManager::FIELD_NOT_MATCHES)
  ->setDescription('Only fill in this field to change the password.');

$user_fieldset = Fieldset::create('user_fieldset', 'User Details')
  ->addElement($username)
  ->addElement($first_name)
  ->addElement($last_name)
  ->addElement($password);

$button = Button::create('details_submit', 'Update');

$form->addElement($account_fieldset)
  ->addElement($user_fieldset)
  ->addElement($button);

if ($form->submitted() && $form->validated()) {
  $result = $main->getDatabase()->query(
    "SELECT 1 FROM user WHERE user_username = '%s' AND user_id <> %s",
    [
      $main->getSessionManager()->filterVariable($_REQUEST['user_username']),
      $main->getLogin()->getUserId()
    ]
  );

  if ($result->num_rows) {
    $main->getErrorHandler()->addError('Email Address is already in use.');
  }
  else {
    $main->getDatabase()->query(
      "UPDATE
        account
      SET
        account_company = '%s',
        account_street_address1 = '%s',
        account_street_address2 = '%s',
        account_city = '%s',
        account_province = '%s',
        account_country = '%s',
        account_postal = '%s',
        account_phone = '%s',
        account_billing = %s
      WHERE
        account_id = %s",
      [
        $main->getSessionManager()->filterVariable($_REQUEST['account_company']),
        $main->getSessionManager()->filterVariable($_REQUEST['account_street_address1']),
        $main->getSessionManager()->filterVariable($_REQUEST['account_street_address2']),
        $main->getSessionManager()->filterVariable($_REQUEST['account_city']),
        $main->getSessionManager()->filterVariable($_REQUEST['account_province']),
        $main->getSessionManager()->filterVariable($_REQUEST['account_country']),
        $main->getSessionManager()->filterVariable($_REQUEST['account_postal']),
        $main->getSessionManager()->filterVariable($_REQUEST['account_phone']),
        $main->getSessionManager()->filterVariable($_REQUEST['account_billing']),
        $account['account_id']
      ]
    );

    $main->getDatabase()->query(
      "UPDATE
        user
      SET
        user_username = '%s',
        user_first_name = '%s',
        user_last_name = '%s',
        user_password = '%s'
      WHERE
        user_id = %s",
      [
        $main->getSessionManager()->filterVariable($_REQUEST['user_username']),
        $main->getSessionManager()->filterVariable($_REQUEST['user_first_name']),
        $main->getSessionManager()->filterVariable($_REQUEST['user_last_name']),
        (empty($_REQUEST['user_password'])
          ? $account['user_password']
          : $main->getSessionManager()->cryptPassword($main->getSessionManager()->filterVariable($_REQUEST['user_password']))),
        $main->getSessionManager()->filterVariable($account['user_id'])
      ]
    );

    if (empty($_REQUEST['user_password'])) {
      $main->getLogin()->updateSessionPassword($account['user_password'], FALSE);
    }
    else {
      $main->getLogin()->updateSessionPassword($_REQUEST['user_password']);
    }

    $main->getSessionManager()->addMessage('Account updated successfully');

    header('Location: /dashboard/details.php');
    exit;
  }
}

print $main->getTheme()->htmlDashboardTop('Sly Devil :: Account Details');
print $form->render();
print $main->getTheme()->htmlDashboardBottom();

?>
