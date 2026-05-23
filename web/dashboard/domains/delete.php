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

$form = Form::create('domains_delete');

$domain_id = $main->getSessionManager()->filterVariable($_REQUEST['id']);

if ($form->submitted() && $form->validated()) {
  $main->getDatabase()->query(
    "UPDATE domain SET domain_date_deleted = NOW() WHERE domain_id_public = '%s'",
    [
      $domain_id
    ]
  );

  $main->getSessionManager()->addMessage('Domain deleted successfully');

  header('Location: /dashboard/domains/');
  exit;
}
else {
  if (isset($_REQUEST['id'])) {
    $result = $main->getDatabase()->query(
      "SELECT * FROM domain WHERE domain_id_public = '%s'",
      [
        $domain_id
      ]
    );

    if ($result->num_rows == 1) {
      $domain = $result->fetch_assoc();

      $hidden = Input::create('hidden', 'id')
        ->setAttribute('value', $domain_id);

      $message = Html::create('delete_confirm', 'Are you sure you want to delete domain "' . $domain['domain_name'] . '"?');

      $button = Button::create('domain_delete_submit', 'Yes, Delete Domain');

      $fieldset = Fieldset::create('domain_delete_fieldset', 'Delete Domain')
        ->addElement($message)
        ->addElement($button);

      $form->addElement($hidden)
        ->addElement($fieldset);

      print $main->getTheme()->htmlDashboardTop('Hosting :: Domains :: Delete');
      print $form->render();
      print $main->getTheme()->htmlDashboardBottom();
    }
    else {
      $main->getSessionManager()->addMessage('No Domains to delete');

      header('Location: /dashboard/domains/');
      exit;
    }

    $result->close();
  }
}
