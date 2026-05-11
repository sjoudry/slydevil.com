<?php

use SlyDevil\Database;
use SlyDevil\Env;
use SlyDevil\Form\Element\Button;
use SlyDevil\Form\Element\Fieldset;
use SlyDevil\Form\Element\Form;
use SlyDevil\Form\Element\Hidden;
use SlyDevil\Form\Element\Text;
use Slydevil\Login;
use SlyDevil\Session;
use SlyDevil\Theme;

include_once(__DIR__ . '/../../../includes/init.inc.php');

Login::handleLogin('admin');

$form = Form::create()
  ->setAction('/dashboard/packages/edit.php')
  ->setName('packages_edit');

$suffix = 'add';
$button_value = 'Add Package';
$package_id = NULL;
$package_name = '';
$package_fee = '';
$package_domains = '';
$package_domains_fee = '';
if (isset($_REQUEST['id'])) {
  $suffix = 'edit';
  $button_value = 'Save Changes';
  $package_id  = Env::filterVariable($_REQUEST['id']);
  
  $result = Database::query(
    "SELECT * FROM package WHERE package_id_public = '%s'",
    [
      $package_id
    ]
  );
            
  if ($result->num_rows == 1) {
    $package = $result->fetch_assoc();

    $package_name = $package['package_name'];
    $package_fee = $package['package_fee'];
    $package_domains = $package['package_domains'];
    $package_domains_fee = $package['package_domains_fee'];
  }

  $result->close();
    
  $hidden = Hidden::create()
    ->setName('id')
    ->setValue($package_id);

  $form->addElement($hidden);
}

$name = Text::create()
  ->setName('package_name')
  ->setMaxlength(255)
  ->setValue($package_name)
  ->addLabel('Package Name')
  ->setClass('form-control')
  ->addValidatorExistance();

$fee = Text::create()
  ->setName('package_fee')
  ->setMaxlength(6)
  ->setValue($package_fee)
  ->addLabel('Base Fee')
  ->setClass('form-control')
  ->addValidatorExistance()
  ->addValidatorNumeric();

$domains = Text::create()
  ->setName('package_domains')
  ->setMaxlength(3)
  ->setValue($package_domains)
  ->addLabel('Domains')
  ->setClass('form-control')
  ->addValidatorExistance()
  ->addValidatorNumeric();
    
$extra_domains = Text::create()
  ->setName('package_domains_fee')
  ->setMaxlength(6)
  ->setValue($package_domains_fee)
  ->addLabel('Extra Domains Fee')
  ->setClass('form-control')
  ->addValidatorExistance()
  ->addValidatorNumeric();

$button = Button::create()
  ->setName('package_' . $suffix . '_submit')
  ->setValue($button_value);

$fieldset = Fieldset::create()
  ->setId('package_' . $suffix . '_fieldset')
  ->setLegend(ucfirst($suffix) . ' Package')
  ->addElement($name)
  ->addElement($fee)
  ->addElement($domains)
  ->addElement($extra_domains)
  ->addElement($button);

$form->addElement($fieldset);
 
if ($form->submitted() && $form->validated()) {
  $duplicate_sql = "SELECT package_id_public FROM package WHERE package_name = '%s'";
  $duplicate_args = [$_REQUEST['package_name']];

  if ($package_id) {
    $duplicate_sql .= " AND package_id_public <> '%s'";
    $duplicate_args[] = $package_id;
  }

  $result = Database::query($duplicate_sql, $duplicate_args);
    
  $count = $result->num_rows;
    
  $result->close();

  if ($count > 0) {
    $form->addError('Package Name exists already.');
  }
  else {
    if ($package_id) {
      Database::query(
        "UPDATE package SET package_name = '%s', package_fee = %.2f, package_domains = %d, package_domains_fee = %.2f WHERE package_id_public = '%s'",
        [
          $_REQUEST['package_name'],
          $_REQUEST['package_fee'],
          $_REQUEST['package_domains'],
          $_REQUEST['package_domains_fee'],
          $package_id
        ]
      );
            
      $_SESSION['messages']['info'][] = 'Package updated successfully';
    }
    else {
      Database::query(
        "INSERT INTO package (package_id_public, package_name, package_fee, package_domains, package_domains_fee, package_date_added) VALUES
        ('%s', '%s', %.2f, %d, %.2f, NOW())",
        [
          Session::cryptPassword($_REQUEST['package_name']),
          $_REQUEST['package_name'],
          $_REQUEST['package_fee'],
          $_REQUEST['package_domains'],
          $_REQUEST['package_domains_fee']
        ]
      );

      $_SESSION['messages']['info'][] = 'New Package added successfully';
    }
        
    header('Location: /dashboard/packages/');
    exit;
  }
}

print Theme::htmlDashboardTop('Hosting :: Packages :: ' . ucfirst($suffix));
print $form->returnHTML();
print Theme::htmlDashboardBottom();
