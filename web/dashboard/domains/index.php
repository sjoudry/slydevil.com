<?php

use SlyDevil\Database;
use SlyDevil\Form\Element\Button;
use SlyDevil\Form\Element\Fieldset;
use SlyDevil\Form\Element\Form;
use SlyDevil\Form\Element\Select;
use Slydevil\Login;
use SlyDevil\Theme;

include_once(__DIR__ . '/../../../includes/init.inc.php');

Login::handleLogin("admin");

print Theme::htmlDashboardTop("Hosting :: Domains");
print "<div class='button'><a href='edit.php'><i class='fa fa-plus'></i> Add New Domain</a></div>\n";

// filter form prep
$result = Database::query(
  "SELECT
    account_id_public,
    account_name
  FROM
    account
  WHERE
    account_date_deleted IS NULL
  ORDER BY
    account_name"
);
$accounts = ["0" => "-- Select Account --"];
while ($row = $result->fetch_assoc()) {
  $accounts[$row["account_id_public"]] = $row["account_name"];
}

$form = Form::create()
  ->setAction("index.php")
  ->setName("domains_filter");

$account = Select::create()
  ->setName("account_id")
  ->setOptions($accounts)
  ->addLabel("Account")
  ->setClass("form-control");

$button = Button::create()
  ->setName("domains_filter_submit")
  ->setValue("Filter");
    
$fieldset = Fieldset::create()
  ->setId("domains_filter_fieldset")
  ->setLegend("<i class='fa fa-filter'></i> Filter Domains")
  ->setCollapsible(TRUE)
  ->setCollapsed((isset($_REQUEST['account_id'])) ? FALSE : TRUE)
  ->addElement($account)
  ->addElement($button);

$form->addElement($fieldset);

if ($form->submitted()) {
  $account->setSelected($_REQUEST["account_id"]);

  $button2 = Button::create()
    ->setName("domains_filter_reset")
    ->setValue("Reset");
        
  $fieldset->addElement($button2);
}

if (isset($_REQUEST['domains_filter_reset']) || (isset($_REQUEST["account_id"]) && empty($_REQUEST["account_id"]))) {
  header("Location: /dashboard/domains/");
  exit;
}

print $form->returnHTML();

// retrive domains
$args = [];
$sql = "
  SELECT
    domain_id_public,
    domain_name,
    account_id_public,
    account_name,
    domain_date_added
  FROM
    domain
  JOIN
    account
  USING
    (account_id)
  WHERE
    domain_date_deleted IS NULL
";
if ($form->submitted()) {
  $sql .= "AND account_id_public = '%s'";
  $args[] = $_REQUEST["account_id"];
}
$sql .= "ORDER BY domain_name";

$result = Database::query($sql, $args);

if ($result->num_rows) {
  print "<table border='0' cellpadding='2' cellspacing='0' width='100%'>\n";
  print "<tr>\n";
  print "<th>&nbsp;</th>\n";
  print "<th>&nbsp;</th>\n";
  print "<th>Domain Name</th>\n";
  print "<th>Account</th>\n";
  print "<th>Date Added</th>\n";
  print "</tr>\n";

  $stripe = "even";
  while ($row = $result->fetch_assoc()) {
    print "<tr>\n";
    print "<td class='" . $stripe . "'><a href='edit.php?id=" . $row["domain_id_public"] . "'><i class='fa fa-pencil'></i></a></td>\n";
    print "<td class='" . $stripe . "'><a href='delete.php?id=" . $row["domain_id_public"] . "'><i class='fa fa-trash-o'></i></a></td>\n";
    print "<td class='" . $stripe . "'>" . $row["domain_name"] . "</td>\n";
    print "<td class='" . $stripe . "'>" . $row["account_name"] . "</td>\n";
    print "<td class='" . $stripe . "'>" . $row["domain_date_added"] . "</td>\n";
    print "</tr>\n";

    $stripe = ($stripe == 'even') ? 'odd' : 'even';
  }
  print "</table>\n";
}
else {
  print "<div class='table-no-data'>No domains</div>\n";
}

print Theme::htmlDashboardBottom();
