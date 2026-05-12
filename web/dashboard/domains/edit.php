<?php

use SlyDevil\Database;
use SlyDevil\Env;
use SlyDevil\Form\Element\Button;
use SlyDevil\Form\Element\Fieldset;
use SlyDevil\Form\Element\Form;
use SlyDevil\Form\Element\Hidden;
use SlyDevil\Form\Element\Select;
use SlyDevil\Form\Element\Text;
use Slydevil\Login;
use SlyDevil\Session;
use SlyDevil\Theme;

include_once(__DIR__ . '/../../../includes/init.inc.php');

Login::handleLogin("admin");

$form = Form::create()
  ->setAction("edit.php")
  ->setName("domains_edit");

$suffix = "add";
$button_value = "Add Domain";
$domain_id = NULL;
$domain_name = "";
$account_id = "";
if (isset($_REQUEST['id'])) {
  $suffix = "edit";
  $button_value = "Save Changes";
  $domain_id = Env::filterVariable($_REQUEST["id"]);

  $result = Database::query(
    "SELECT *, account_id_public FROM domain JOIN account USING (account_id) WHERE domain_id_public = '%s'",
    [
      $domain_id
    ]
  );

  if ($result->num_rows == 1) {
    $domain = $result->fetch_assoc();

    $domain_name = $domain["domain_name"];
    $account_id  = $domain["account_id_public"];
  }

  $result->close();

  $hidden = Hidden::create()
    ->setName("id")
    ->setValue($domain_id);

  $form->addElement($hidden);
}

$name = Text::create()
  ->setName("domain_name")
  ->setMaxlength(255)
  ->setValue($domain_name)
  ->addLabel("Domain Name")
  ->setClass("form-control")
  ->addValidatorExistance();

$result = Database::query("SELECT account_id_public, account_name FROM account WHERE account_date_deleted IS NULL ORDER BY account_name");
$accounts = ["0" => "--- Select Account ---"];
while ($row = $result->fetch_assoc()) {
  $accounts[$row["account_id_public"]] = $row["account_name"];
}
$account = Select::create()
  ->setName("account_id")
  ->setOptions($accounts)
  ->setSelected($account_id)
  ->addLabel("Account")
  ->setClass("form-control")
  ->addValidatorExistance();

$button = Button::create()
  ->setName("domain_" . $suffix . "_submit")
  ->setValue($button_value);

$fieldset = Fieldset::create()
  ->setId("package_" . $suffix . "_fieldset")
  ->setLegend(ucfirst($suffix) . " Domain")
  ->addElement($name)
  ->addElement($account)
  ->addElement($button);

$form->addElement($fieldset);

if ($form->submitted() && $form->validated()) {
  $duplicate_sql = "SELECT domain_id_public FROM domain WHERE domain_name = '%s'";
  $duplicate_args = [$_REQUEST["domain_name"]];

  if ($domain_id) {
    $duplicate_sql .= " AND domain_id_public <> '%s'";
    $duplicate_args[] = $domain_id;
  }

  $result = Database::query($duplicate_sql, $duplicate_args);
  $count = $result->num_rows;
  $result->close();

  if ($count > 0) {
    $form->addError("Domain Name exists already.");
  }
  else {
    $result = Database::query(
      "SELECT account_id FROM account WHERE account_id_public = '%s'",
      [
        $_REQUEST["account_id"]
      ]
    );

    $account = $result->fetch_assoc();
    $account_id = $account["account_id"];
    $result->close();

    if ($domain_id) {
      Database::query(
        "UPDATE domain SET domain_name = '%s', account_id = %s WHERE domain_id_public = '%s'",
        [
          $_REQUEST["domain_name"],
          $account_id,
          $domain_id
        ]
      );

      $_SESSION["messages"]["info"][] = "Domain updated successfully";
    }
    else {
      Database::query(
        "INSERT INTO domain (domain_id_public, domain_name, account_id, domain_date_added) VALUES
        ('%s', '%s', %s, NOW())",
        [
          Session::cryptPassword($_REQUEST["domain_name"]),
          $_REQUEST["domain_name"],
          $account_id
        ]
      );

      $_SESSION["messages"]["info"][] = "New Domain added successfully";
    }

    header("Location: /dashboard/domains/");
    exit;
  }
}

print Theme::htmlDashboardTop("Hosting :: Domains :: " . ucfirst($suffix));
print $form->returnHTML();
print Theme::htmlDashboardBottom();