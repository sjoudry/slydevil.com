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

$form = Form::create('users_delete');

$user_id = $main->getSessionManager()->filterVariable($_REQUEST['id']);

if ($form->submitted() && $form->validated()) {
  $main->getDatabase()->query(
    "UPDATE user SET user_date_deleted = NOW() WHERE user_id_public = '%s'",
    [
      $user_id
    ]
  );

  $main->getSessionManager()->addMessage('User deleted successfully');

  header('Location: /dashboard/users/');
  exit;
}
else {
  if (isset($_REQUEST['id'])) {
    $result = $main->getDatabase()->query(
      "SELECT * FROM user WHERE user_id_public = '%s'",
      [
        $user_id
      ]
    );

    if ($result->num_rows == 1) {
      $user = $result->fetch_assoc();

      $hidden = Input::create('hidden', 'id')
        ->setAttribute('value', $user_id);

      $message = Html::create('confirm', 'Are you sure you want to delete user "' . $user['user_username'] . '"?');

      $button = Button::create('user_delete_submit', 'Yes, Delete User');

      $fieldset = Fieldset::create('user_delete_fieldset', 'Delete User')
        ->addElement($message)
        ->addElement($button);

      $form->addElement($hidden)
        ->addElement($fieldset);

      print $main->getTheme()->htmlDashboardTop('Sly Devil :: Users :: Delete');
      print $form->render();
      print $main->getTheme()->htmlDashboardBottom();
    }
    else {
      $main->getSessionManager()->addMessage('No Users to delete');

      header('Location: /dashboard/users/');
      exit;
    }
  }
}
