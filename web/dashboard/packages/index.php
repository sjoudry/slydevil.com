<?php

use SlyDevil\Site\Main;

include_once(__DIR__ . '/../../../includes/init.inc.php');

$main = new Main();
$main->getLogin()->handle('admin');

print $main->getTheme()->htmlDashboardTop('Hosting :: Packages');
print '<div class="button"><a href="edit.php"><i class="fa fa-plus"></i> Add New Package</a></div>';

$result = $main->getDatabase()->query(
  'SELECT
    package_id_public,
    package_name,
    (SELECT COUNT(*) FROM account WHERE account.package_id = package.package_id AND account_date_deleted IS NULL) accounts,
    package_date_added
  FROM
    package
  WHERE
    package_date_deleted IS NULL
  ORDER BY
    package_name'
);

if ($result->num_rows) {
  print '<table border="0" cellpadding="2" cellspacing="0" width="100%">';
  print '<tr>';
  print '<th>&nbsp;</th>';
  print '<th>&nbsp;</th>';
  print '<th>Package Name</th>';
  print '<th>Accounts</th>';
  print '<th>Date Added</th>';
  print '</tr>';

  $stripe = 'even';
  while ($row = $result->fetch_assoc()) {
    print '<tr>';
    print '<td class="' . $stripe . '"><a href="edit.php?id=' . $row['package_id_public'] . '"><i class="fa fa-pencil"></i></a></td>';
    print '<td class="' . $stripe . '"><a href="delete.php?id=' . $row['package_id_public'] . '"><i class="fa fa-trash-o"></i></a></td>';
    print '<td class="' . $stripe . '">' . $row['package_name'] . '</td>';
    print '<td class="' . $stripe . '">' . $row['accounts'] . '</td>';
    print '<td class="' . $stripe . '">' . $row['package_date_added'] . '</td>';
    print '</tr>';

    $stripe = ($stripe == 'even') ? 'odd' : 'even';
  }
  print '</table>';
}
else {
  print '<div class="table-no-data">No packages</div>';
}

print $main->getTheme()->htmlDashboardBottom();
