<?php

use SlyDevil\Form\Element\Button;
use SlyDevil\Form\Element\Fieldset;
use SlyDevil\Form\Element\Form;
use SlyDevil\Form\Element\Input;
use SlyDevil\Site\Main;

include_once(__DIR__ . '/../../../includes/init.inc.php');

$main = new Main();
$main->getLogin()->handle('admin');

$form = Form::create('packages_edit');

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
  $package_id = $main->getSessionManager()->filterVariable($_REQUEST['id']);

  $result = $main->getDatabase()->query(
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

  $hidden = Input::create('hidden', 'id')
    ->setAttribute('value', $package_id);

  $form->addElement($hidden);
}

$name = Input::create('text', 'package_name')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $package_name)
  ->setAttribute('class', 'form-control')
  ->addLabel('Package Name')
  ->addValidator('existance');

$fee = Input::create('text', 'package_fee')
  ->setAttribute('maxlength', 6)
  ->setAttribute('value', $package_fee)
  ->setAttribute('class', 'form-control')
  ->addLabel('Base Fee')
  ->addValidator('existance')
  ->addValidator('numeric');

$domains = Input::create('text', 'package_domains')
  ->setAttribute('maxlength', 3)
  ->setAttribute('value', $package_domains)
  ->setAttribute('class', 'form-control')
  ->addLabel('Domains')
  ->addValidator('existance')
  ->addValidator('numeric');

$extra_domains = Input::create('text', 'package_domains_fee')
  ->setAttribute('maxlength', 6)
  ->setAttribute('value', $package_domains_fee)
  ->setAttribute('class', 'form-control')
  ->addLabel('Extra Domains Fee')
  ->addValidator('existance')
  ->addValidator('numeric');

$button = Button::create('package_' . $suffix . '_submit', $button_value);

$fieldset = Fieldset::create('package_' . $suffix . '_fieldset', ucfirst($suffix) . ' Package')
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

  $result = $main->getDatabase()->query($duplicate_sql, $duplicate_args);

  $count = $result->num_rows;

  $result->close();

  if ($count > 0) {
    $main->getErrorHandler()->addError('Package Name exists already.');
  }
  else {
    if ($package_id) {
      $main->getDatabase()->query(
        "UPDATE package SET package_name = '%s', package_fee = %.2f, package_domains = %d, package_domains_fee = %.2f WHERE package_id_public = '%s'",
        [
          $main->getSessionManager()->filterVariable($_REQUEST['package_name']),
          $main->getSessionManager()->filterVariable($_REQUEST['package_fee']),
          $main->getSessionManager()->filterVariable($_REQUEST['package_domains']),
          $main->getSessionManager()->filterVariable($_REQUEST['package_domains_fee']),
          $package_id
        ]
      );

      $_SESSION['messages']['info'][] = 'Package updated successfully';
    }
    else {
      $main->getDatabase()->query(
        "INSERT INTO package (package_id_public, package_name, package_fee, package_domains, package_domains_fee, package_date_added) VALUES
        ('%s', '%s', %.2f, %d, %.2f, NOW())",
        [
          $main->getSessionManager()->cryptPassword($main->getSessionManager()->filterVariable($_REQUEST['package_name'])),
          $main->getSessionManager()->filterVariable($_REQUEST['package_name']),
          $main->getSessionManager()->filterVariable($_REQUEST['package_fee']),
          $main->getSessionManager()->filterVariable($_REQUEST['package_domains']),
          $main->getSessionManager()->filterVariable($_REQUEST['package_domains_fee'])
        ]
      );

      $_SESSION['messages']['info'][] = 'New Package added successfully';
    }

    header('Location: /dashboard/packages/');
    exit;
  }
}

print $main->getTheme()->htmlDashboardTop('Hosting :: Packages :: ' . ucfirst($suffix));
print $form->render();
print $main->getTheme()->htmlDashboardBottom();
