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
  ->setAction('/dashboard/users/delete.php')
  ->setName('users_delete');

$user_id = Env::filterVariable($_REQUEST['id']);
                
if ($form->submitted() && $form->validated()) {
  Database::query(
    "UPDATE user SET user_date_deleted = NOW() WHERE user_id_public = '%s'",
    [
      $user_id
    ]
  );

  $_SESSION['messages']['info'][] = 'User deleted successfully';
    
  header('Location: /dashboard/users/');
  exit;
}
else {
  if (isset($_REQUEST['id'])) {
    $result = Database::query(
      "SELECT * FROM user WHERE user_id_public = '%s'",
      [
        $user_id
      ]
    );
                    
    if ($result->num_rows == 1) {
      $user = $result->fetch_assoc();

      $hidden = Hidden::create()
        ->setName('id')
        ->setValue($user_id);
    
      $message = Html::create()
        ->setContent("Are you sure you want to delete user '" . $user['user_username'] . "'?");

      $button = Button::create()
        ->setName('user_delete_submit')
        ->setValue('Yes, Delete User');

      $fieldset = Fieldset::create()
        ->setId('user_delete_fieldset')
        ->setLegend('Delete User')
        ->addElement($message)
        ->addElement($button);

      $form->addElement($hidden)
          ->addElement($fieldset);
            
      print Theme::htmlDashboardTop('Sly Devil :: Users :: Delete');
      print $form->returnHTML();
      print Theme::htmlDashboardBottom();
    }
    else {
      $_SESSION['messages']['info'][] = 'No Users to delete';
            
      header('Location: /dashboard/users/');
      exit;
    }
  }
}
