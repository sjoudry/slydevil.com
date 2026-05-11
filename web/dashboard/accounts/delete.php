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
  ->setAction('/dashboard/accounts/delete.php')
  ->setName('accounts_delete');

$account_id = Env::filterVariable($_REQUEST['id']);
                
if ($form->submitted() && $form->validated()) {
  Database::query(
    "UPDATE account SET account_date_deleted = NOW() WHERE account_id_public = '%s'",
    [
      $account_id
    ]
  );

  $_SESSION['messages']['info'][] = 'Accounts deleted successfully';
    
  header('Location: /dashboard/accounts/');
  exit;
}
else {
  if (isset($_REQUEST['id'])) {
    $result = Database::query(
      "SELECT * FROM account WHERE account_id_public = '%s'",
      [
        $account_id
      ]
    );
                  
    if ($result->num_rows == 1) {
      $account = $result->fetch_assoc();

      $hidden = Hidden::create()
        ->setName('id')
        ->setValue($account_id);
  
      $message = Html::create()
        ->setContent("Are you sure you want to delete account '" . $account['account_name'] . "'?");

      $button = Button::create()
        ->setName('account_delete_submit')
        ->setValue('Yes, Delete Account');

      $fieldset = Fieldset::create()
        ->setId('account_delete_fieldset')
        ->setLegend('Delete Account')
        ->addElement($message)
        ->addElement($button);

      $form->addElement($hidden)
        ->addElement($fieldset);
          
      print Theme::htmlDashboardTop('Hosting :: Accounts :: Delete');
      print $form->returnHTML();
      print Theme::htmlDashboardBottom();
    }
    else {
      $_SESSION['messages']['info'][] = 'No Accounts to delete';
          
      header('Location: /dashboard/accounts/');
      exit;
    }

    $result->close();
  }
}
