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
  ->setAction('delete.php')
  ->setName('services_delete');

$service_id = Env::filterVariable($_REQUEST['id']);
                
if ($form->submitted() && $form->validated()) {
  Database::query(
    "UPDATE service SET service_date_deleted = NOW() WHERE service_id_public = '%s'",
    [
      $service_id
    ]
  );

  $_SESSION['messages']['info'][] = 'Services deleted successfully';
    
  header('Location: /dashboard/services/');
  exit;
}
else {
  if (isset($_REQUEST['id'])) {
    $result = Database::query(
      "SELECT * FROM service WHERE service_id_public = '%s'",
      [
        $service_id
      ]
    );
                    
    if ($result->num_rows == 1) {
      $service = $result->fetch_assoc();

      $hidden = Hidden::create()
        ->setName('id')
        ->setValue($service_id);
    
      $message = Html::create()
        ->setContent("Are you sure you want to delete service '" . $service['service_name'] . "'?");

      $button = Button::create()
        ->setName('service_delete_submit')
        ->setValue('Yes, Delete Service');

      $fieldset = Fieldset::create()
        ->setId('service_delete_fieldset')
        ->setLegend('Delete Service')
        ->addElement($message)
        ->addElement($button);

      $form->addElement($hidden)
        ->addElement($fieldset);
            
      print Theme::htmlDashboardTop('Hosting :: Services :: Delete');
      print $form->returnHTML();
      print Theme::htmlDashboardBottom();
    }
    else {
      $_SESSION['messages']['info'][] = 'No Services to delete';
            
      header('Location: /dashboard/services/');
      exit;
    }

    $result->close();
  }
}
