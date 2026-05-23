<?php

use SlyDevil\Form\Element\Button;
use SlyDevil\Form\Element\Fieldset;
use SlyDevil\Form\Element\Form;
use SlyDevil\Form\Element\Html;
use SlyDevil\Form\Element\Input;
use SlyDevil\Site\Main;

include_once(__DIR__ . '/../../../includes/init.inc.php');

$main = new Main();
$main->getLogin()->handle('admin');

$form = Form::create('services_delete');

$service_id = $main->getSessionManager()->filterVariable($_REQUEST['id']);

if ($form->submitted() && $form->validated()) {
  $main->getDatabase()->query(
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
    $result = $main->getDatabase()->query(
      "SELECT * FROM service WHERE service_id_public = '%s'",
      [
        $service_id
      ]
    );

    if ($result->num_rows == 1) {
      $service = $result->fetch_assoc();

      $hidden = Input::create('hidden', 'id')
        ->setAttribute('value', $service_id);

      $message = Html::create('delete_confirm', 'Are you sure you want to delete service "' . $service['service_name'] . '"?');

      $button = Button::create('service_delete_submit', 'Yes, Delete Service');

      $fieldset = Fieldset::create('service_delete_fieldset', 'Delete Service')
        ->addElement($message)
        ->addElement($button);

      $form->addElement($hidden)
        ->addElement($fieldset);

      print $main->getTheme()->htmlDashboardTop('Hosting :: Services :: Delete');
      print $form->render();
      print $main->getTheme()->htmlDashboardBottom();
    }
    else {
      $_SESSION['messages']['info'][] = 'No Services to delete';

      header('Location: /dashboard/services/');
      exit;
    }

    $result->close();
  }
}
