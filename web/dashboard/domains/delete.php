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

Login::handleLogin("admin");

$form = Form::create()
  ->setAction("delete.php")
  ->setName("domains_delete");

$domain_id = Env::filterVariable($_REQUEST["id"]);
                
if ($form->submitted() && $form->validated()) {
  Database::query(
    "UPDATE domain SET domain_date_deleted = NOW() WHERE domain_id_public = '%s'",
    [
      $domain_id
    ]
  );

  $_SESSION["messages"]["info"][] = "Domains deleted successfully";
  
  header("Location: /dashboard/domains/");
  exit;
}
else {
  if (isset($_REQUEST['id'])) {
    $result = Database::query(
      "SELECT * FROM domain WHERE domain_id_public = '%s'",
      [
        $domain_id
      ]
    );
                    
    if ($result->num_rows == 1) {
      $domain = $result->fetch_assoc();

      $hidden = Hidden::create()
        ->setName("id")
        ->setValue($domain_id);
    
      $message = Html::create()
        ->setContent("Are you sure you want to delete domain '" . $domain["domain_name"] . "'?");

      $button = Button::create()
        ->setName("domain_delete_submit")
        ->setValue("Yes, Delete Domain");

      $fieldset = Fieldset::create()
        ->setId("domain_delete_fieldset")
        ->setLegend("Delete Domain")
        ->addElement($message)
        ->addElement($button);

      $form->addElement($hidden)
        ->addElement($fieldset);
          
      print Theme::htmlDashboardTop("Hosting :: Domains :: Delete");
      print $form->returnHTML();
      print Theme::htmlDashboardBottom();
    }
    else {
      $_SESSION["messages"]["info"][] = "No Domains to delete";
          
      header("Location: /dashboard/domains/");
      exit;
    }

    $result->close();
  }
}
