<?php

use SlyDevil\Database;
use SlyDevil\Form\Element\Base;
use SlyDevil\Form\Element\Button;
use SlyDevil\Form\Element\Fieldset;
use SlyDevil\Form\Element\Form;
use SlyDevil\Form\Element\Password;
use SlyDevil\Form\Element\Select;
use SlyDevil\Form\Element\Text;
use Slydevil\Login;
use SlyDevil\Session;
use SlyDevil\Theme;

include_once(__DIR__ . '/../../includes/init.inc.php');

Login::handleLogin('user');

$form = Form::create()
  ->setAction('/dashboard/details.php')
  ->setName('account_details');

$result = Database::query('SELECT * FROM account JOIN user USING (account_id) WHERE user_id = %s', [Login::$userId]);
$account = $result->fetch_assoc();

$company = Text::create()
  ->setName('account_company')
  ->setMaxlength(255)
  ->setValue($account['account_company'])
  ->addLabel('Account Company')
  ->setClass('form-control');

$address1 = Text::create()
  ->setName('account_street_address1')
  ->setMaxlength(255)
  ->setValue($account['account_street_address1'])
  ->addLabel('Account Street Address 1')
  ->setClass('form-control');

$address2 = Text::create()
  ->setName('account_street_address2')
  ->setMaxlength(255)
  ->setValue($account['account_street_address2'])
  ->addLabel('Account Street Address 2')
  ->setClass('form-control');

$city = Text::create()
  ->setName('account_city')
  ->setMaxlength(255)
  ->setValue($account['account_city'])
  ->addLabel('Account City/Town')
  ->setClass('form-control');

$province = Text::create()
  ->setName('account_province')
  ->setMaxlength(255)
  ->setValue($account['account_province'])
  ->addLabel('Account Province/State')
  ->setClass('form-control');

$country = Text::create()
  ->setName('account_country')
  ->setMaxlength(255)
  ->setValue($account['account_country'])
  ->addLabel('Account Country')
  ->setClass('form-control');

$postal = Text::create()
  ->setName('account_postal')
  ->setMaxlength(10)
  ->setValue($account['account_postal'])
  ->addLabel('Account Postal')
  ->setClass('form-control');

$phone = Text::create()
  ->setName('account_phone')
  ->setMaxlength(20)
  ->setValue($account['account_phone'])
  ->addLabel('Account Telephone')
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
  ->setSelected($account['account_billing'])
  ->addLabel('Account Billing Cycle')
  ->setClass('form-control');

$account_fieldset = Fieldset::create()
  ->setId('details_fieldset')
  ->setLegend('Account Details')
  ->addElement($company)
  ->addElement($address1)
  ->addElement($address2)
  ->addElement($city)
  ->addElement($province)
  ->addElement($country)
  ->addElement($postal)
  ->addElement($phone)
  ->addElement($cycle);

$username = Text::create()
  ->setName('user_username')
  ->setMaxlength(255)
  ->setValue($account['user_username'])
  ->addLabel('User Email Address')
  ->setClass('form-control')
  ->addValidatorExistance()
  ->addValidatorEmail();

$first_name = Text::create()
  ->setName('user_first_name')
  ->setMaxlength(255)
  ->setValue($account['user_first_name'])
  ->setClass('form-control')
  ->addLabel('User First Name')
  ->addValidatorExistance();
    
$last_name = Text::create()
  ->setName('user_last_name')
  ->setMaxlength(255)
  ->setValue($account['user_last_name'])
  ->setClass('form-control')
  ->addLabel('User Last Name')
  ->addValidatorExistance();

$password = Password::create()
  ->setName('user_password')
  ->setMaxlength(16)
  ->addLabel('User Password')
  ->setClass('form-control')
  ->addValidatorContainsLowercase()
  ->addValidatorContainsNumber()
  ->addValidatorContainsUppercase()
  ->addValidatorLengthLong(16)
  ->addValidatorLengthShort(8)
  ->addValidatorMatch('user_username', 'User Email Address', Base::VALIDATES_IF_FIELD_NOT_MATCHES)
  ->setDescription('Only fill in this field to change the password.');

$user_fieldset = Fieldset::create()
  ->setId('user_fieldset')
  ->setLegend('User Details')
  ->addElement($username)
  ->addElement($first_name)
  ->addElement($last_name)
  ->addElement($password);

$button = Button::create()
  ->setName('details_submit')
  ->setValue('Update');
    
$form->addElement($account_fieldset)
  ->addElement($user_fieldset)
  ->addElement($button);

if ($form->submitted() && $form->validated()) {
  $result = Database::query(
    "SELECT 1 FROM user WHERE user_username = '%s' AND user_id <> %s",
    [
      $_REQUEST['user_username'],
      Login::$userId
    ]
  );

  if ($result->num_rows) {
    $form->addError('Email Address is already in use.');
  }
  else {
    Database::query(
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
        $_REQUEST['account_company'],
        $_REQUEST['account_street_address1'],
        $_REQUEST['account_street_address2'],
        $_REQUEST['account_city'],
        $_REQUEST['account_province'],
        $_REQUEST['account_country'],
        $_REQUEST['account_postal'],
        $_REQUEST['account_phone'],
        $_REQUEST['account_billing'],
        $account['account_id']
      ]
    );
        
    Database::query(
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
        $_REQUEST['user_username'],
        $_REQUEST['user_first_name'],
        $_REQUEST['user_last_name'],
        (empty($_REQUEST['user_password']) ? $account['user_password'] : Session::cryptPassword($_REQUEST['user_password'])),
        $account['user_id']
      ]
    );
        
    $_SESSION[Login::LOGIN_PASSWORD] = (empty($_REQUEST['user_password']) ? $account['user_password'] : Session::cryptPassword($_REQUEST['user_password']));

    $_SESSION['messages']['info'][] = 'Account updated successfully';

    header('Location: /dashboard/details.php');
    exit;
  }
}

print Theme::htmlDashboardTop('Sly Devil :: Account Details');
print $form->returnHTML();
print Theme::htmlDashboardBottom();

?>
