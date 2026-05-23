<?php

use SlyDevil\Site\Main;

include_once(__DIR__ . '/../../../includes/init.inc.php');

$main = new Main();
$main->getLogin()->handle('admin');

print $main->getTheme()->htmlDashboardTop('Hosting :: Accounts');
print '<div class="button"><a href="edit.php"><i class="fa fa-plus"></i> Add New Account</a></div>';

$result = $main->getDatabase()->query(
  'SELECT
    account_id_public,
    account_name,
    package_name,
    (SELECT COUNT(*) FROM domain WHERE domain.account_id = account.account_id AND domain_date_deleted IS NULL) accounts,
    (SELECT COUNT(*) FROM service WHERE service.account_id = account.account_id AND service_date_deleted IS NULL) services,
    (SELECT COUNT(*) FROM invoice WHERE invoice.account_id = account.account_id AND invoice_date_deleted IS NULL) invoices,
    account_date_added
  FROM
    account
  JOIN
    package
  USING
    (package_id)
  WHERE
    account_date_deleted IS NULL
  ORDER BY
    account_name'
);

if ($result->num_rows) {
  print '<table border="0" cellpadding="2" cellspacing="0" width="100%">';
  print '<tr>';
  print '<th>&nbsp;</th>';
  print '<th>&nbsp;</th>';
  print '<th>Acount</th>';
  print '<th>Package</th>';
  print '<th>Dom</th>';
  print '<th>Ser</th>';
  print '<th>Inv</th>';
  print '<th>Date Added</th>';
  print '</tr>';

  $stripe = 'even';
  while ($row = $result->fetch_assoc()) {
    print '<tr>';
    print '<td class="' . $stripe . '"><a href="edit.php?id=' . $row['account_id_public'] . '"><i class="fa fa-pencil"></i></a></td>';
    print '<td class="' . $stripe . '"><a href="delete.php?id=' . $row['account_id_public'] . '"><i class="fa fa-trash-o"></i></a></td>';
    print '<td class="' . $stripe . '">' . $row['account_name'] . '</td>';
    print '<td class="' . $stripe . '">' . $row['package_name'] . '</td>';
    print '<td class="' . $stripe . '">' . $row['accounts'] . '</td>';
    print '<td class="' . $stripe . '">' . $row['services'] . '</td>';
    print '<td class="' . $stripe . '">' . $row['invoices'] . '</td>';
    print '<td class="' . $stripe . '">' . $row['account_date_added'] . '</td>';
    print '</tr>';

    $stripe = ($stripe == 'even') ? 'odd' : 'even';
  }
  print '</table>';
}
else {
  print '<div class="table-no-data">No accounts</div>';
}

print $main->getTheme()->htmlDashboardBottom();
