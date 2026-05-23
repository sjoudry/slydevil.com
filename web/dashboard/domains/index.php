<?php

use SlyDevil\Form\Element\Button;
use SlyDevil\Form\Element\Fieldset;
use SlyDevil\Form\Element\Form;
use SlyDevil\Form\Element\Select;
use SlyDevil\Site\Main;

include_once(__DIR__ . '/../../../includes/init.inc.php');

$main = new Main();
$main->getLogin()->handle('admin');

print $main->getTheme()->htmlDashboardTop('Hosting :: Domains');
print '<div class="button"><a href="edit.php"><i class="fa fa-plus"></i> Add New Domain</a></div>';

// filter form prep
$result = $main->getDatabase()->query(
  'SELECT
    account_id_public,
    account_name
  FROM
    account
  WHERE
    account_date_deleted IS NULL
  ORDER BY
    account_name'
);
$accounts = ['0' => '-- Select Account --'];
while ($row = $result->fetch_assoc()) {
  $accounts[$row['account_id_public']] = $row['account_name'];
}

$form = Form::create('domains_filter');

$account = Select::create('account_id')
  ->setOptions($accounts)
  ->addLabel('Account')
  ->setAttribute('class', 'form-control');

$button = Button::create('domains_filter_submit', 'Filter');

$fieldset = Fieldset::create('domains_filter_fieldset', '<i class="fa fa-filter"></i> Filter Domains')
  ->setCollapsible(TRUE)
  ->setCollapsed((isset($_REQUEST['account_id'])) ? FALSE : TRUE)
  ->addElement($account)
  ->addElement($button);

$form->addElement($fieldset);

if ($form->submitted()) {
  $account->setSelected($main->getSessionManager()->filterVariable($_REQUEST['account_id']));

  $button2 = Button::create('domains_filter_reset', 'Reset');

  $fieldset->addElement($button2);
}

if (isset($_REQUEST['domains_filter_reset']) || (isset($_REQUEST['account_id']) && empty($_REQUEST['account_id']))) {
  header('Location: /dashboard/domains/');
  exit;
}

print $form->render();

// retrieve domains
$args = [];
$sql = '
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
';
if ($form->submitted()) {
  $sql .= "AND account_id_public = '%s'";
  $args[] = $main->getSessionManager()->filterVariable($_REQUEST['account_id']);
}
$sql .= 'ORDER BY domain_name';

$result = $main->getDatabase()->query($sql, $args);

if ($result->num_rows) {
  print '<table border="0" cellpadding="2" cellspacing="0" width="100%">';
  print '<tr>';
  print '<th>&nbsp;</th>';
  print '<th>&nbsp;</th>';
  print '<th>Domain Name</th>';
  print '<th>Account</th>';
  print '<th>Date Added</th>';
  print '</tr>';

  $stripe = 'even';
  while ($row = $result->fetch_assoc()) {
    print '<tr>';
    print '<td class="' . $stripe . '"><a href="edit.php?id=' . $row['domain_id_public'] . '"><i class="fa fa-pencil"></i></a></td>';
    print '<td class="' . $stripe . '"><a href="delete.php?id=' . $row['domain_id_public'] . '"><i class="fa fa-trash-o"></i></a></td>';
    print '<td class="' . $stripe . '">' . $row['domain_name'] . '</td>';
    print '<td class="' . $stripe . '">' . $row['account_name'] . '</td>';
    print '<td class="' . $stripe . '">' . $row['domain_date_added'] . '</td>';
    print '</tr>';

    $stripe = ($stripe == 'even') ? 'odd' : 'even';
  }
  print '</table>';
}
else {
  print '<div class="table-no-data">No domains</div>';
}

print $main->getTheme()->htmlDashboardBottom();
