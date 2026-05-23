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

$form = Form::create('packages_delete');

$package_id = $main->getSessionManager()->filterVariable($_REQUEST['id']);

if ($form->submitted() && $form->validated()) {
  $main->getDatabase()->query(
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
    $result = $main->getDatabase()->query(
      "SELECT * FROM package WHERE package_id_public = '%s'",
      [
        $package_id
      ]
    );

    if ($result->num_rows == 1) {
      $package = $result->fetch_assoc();

      $hidden = Input::create('hidden', 'id')
        ->setAttribute('value', $package_id);

      $message = Html::create('delete_confirm', 'Are you sure you want to delete package "' . $package['package_name'] . '"?');

      $button = Button::create('package_delete_submit', 'Yes, Delete Package');

      $fieldset = Fieldset::create('package_delete_fieldset', 'Delete Package')
        ->addElement($message)
        ->addElement($button);

      $form->addElement($hidden)
        ->addElement($fieldset);

      print $main->getTheme()->htmlDashboardTop('Hosting :: Packages :: Delete');
      print $form->render();
      print $main->getTheme()->htmlDashboardBottom();
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
