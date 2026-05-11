<?php

use SlyDevil\Database;
use SlyDevil\Env;
use SlyDevil\Form\Element\Button;
use SlyDevil\Form\Element\Fieldset;
use SlyDevil\Form\Element\Form;
use SlyDevil\Form\Element\Hidden;
use SlyDevil\Form\Element\Html;
use Slydevil\Login;
use SlyDevil\Theme;

include_once(__DIR__ . '/../../../includes/init.inc.php');

Login::handleLogin('admin');

$form = Form::create()
  ->setAction('/dashboard/packages/delete.php')
  ->setName('packages_delete');

$package_id = Env::filterVariable($_REQUEST['id']);
                
if ($form->submitted() && $form->validated()) {
  Database::query(
    "UPDATE package SET package_date_deleted = NOW() WHERE package_id_public = '%s'",
    [
      $package_id
    ]
  );

  $_SESSION['messages']['info'][] = 'Packages deleted successfully';
    
  header('Location: /dashboard/packages/');
  exit;
}
else {
  if (isset($_REQUEST['id'])) {
    $result = Database::query(
      "SELECT * FROM package WHERE package_id_public = '%s'",
      [
        $package_id
      ]
    );
                  
    if ($result->num_rows == 1) {
      $package = $result->fetch_assoc();

      $hidden = Hidden::create()
        ->setName('id')
        ->setValue($package_id);
  
      $message = Html::create()
        ->setContent("Are you sure you want to delete package '" . $package['package_name'] . "'?");

      $button = Button::create()
        ->setName('package_delete_submit')
        ->setValue('Yes, Delete Package');

      $fieldset = Fieldset::create()
        ->setId('package_delete_fieldset')
        ->setLegend('Delete Package')
        ->addElement($message)
        ->addElement($button);

      $form->addElement($hidden)
        ->addElement($fieldset);
          
      print Theme::htmlDashboardTop('Hosting :: Packages :: Delete');
      print $form->returnHTML();
      print Theme::htmlDashboardBottom();
    }
    else {
      $_SESSION['messages']['info'][] = 'No Packages to delete';
          
      header('Location: /dashboard/packages/');
      exit;
    }

    $result->close();
  }
}

?>
