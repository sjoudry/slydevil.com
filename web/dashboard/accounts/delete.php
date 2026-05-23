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

$form = Form::create('accounts_delete');

$account_id = $main->getSessionManager()->filterVariable($_REQUEST['id']);

if ($form->submitted() && $form->validated()) {
  $main->getDatabase()->query(
    "UPDATE account SET account_date_deleted = NOW() WHERE account_id_public = '%s'",
    [
      $account_id
    ]
  );

  $main->getSessionManager()->addMessage('Account deleted successfully');

  header('Location: /dashboard/accounts/');
  exit;
}
else {
  if (isset($_REQUEST['id'])) {
    $result = $main->getDatabase()->query(
      "SELECT * FROM account WHERE account_id_public = '%s'",
      [
        $account_id
      ]
    );

    if ($result->num_rows == 1) {
      $account = $result->fetch_assoc();

      $hidden = Input::create('hidden', 'id')
        ->setAttribute('value', $account_id);

      $message = Html::create('delete_confirm', 'Are you sure you want to delete account "' . $account['account_name'] . '"?');

      $button = Button::create('account_delete_submit', 'Yes, Delete Account');

      $fieldset = Fieldset::create('account_delete_fieldset', 'Delete Account')
        ->addElement($message)
        ->addElement($button);

      $form->addElement($hidden)
        ->addElement($fieldset);

      print $main->getTheme()->htmlDashboardTop('Hosting :: Accounts :: Delete');
      print $form->render();
      print $main->getTheme()->htmlDashboardBottom();
    }
    else {
      $main->getSessionManager()->addMessage('No Accounts to delete');

      header('Location: /dashboard/accounts/');
      exit;
    }

    $result->close();
  }
}
