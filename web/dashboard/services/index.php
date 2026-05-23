<?php

use SlyDevil\Site\Main;

include_once(__DIR__ . '/../../../includes/init.inc.php');

$main = new Main();
$main->getLogin()->handle('admin');

print $main->getTheme()->htmlDashboardTop('Hosting :: Services');
print '<div class="button"><a href="edit.php"><i class="fa fa-plus"></i> Add New Service</a></div>';

$result = $main->getDatabase()->query(
  'SELECT
    service_id_public,
    service_name,
    account_name,
    service_date_added
  FROM
    service
  JOIN
    account
  USING
    (account_id)
  WHERE
    service_date_deleted IS NULL
  ORDER BY
    service_name'
);

if ($result->num_rows) {
  print '<table border="0" cellpadding="2" cellspacing="0" width="100%">';
  print '<tr>';
  print '<th>&nbsp;</th>';
  print '<th>&nbsp;</th>';
  print '<th>Service Name</th>';
  print '<th>Account Name</th>';
  print '<th>Date Added</th>';
  print '</tr>';

  $stripe = 'even';
  while ($row = $result->fetch_assoc()) {
    print '<tr>';
    print '<td class="' . $stripe . '"><a href="edit.php?id=' . $row['service_id_public'] . '"><i class="fa fa-pencil"></i></a></td>';
    print '<td class="' . $stripe . '"><a href="delete.php?id=' . $row['service_id_public'] . '"><i class="fa fa-trash-o"></i></a></td>';
    print '<td class="' . $stripe . '">' . $row['service_name'] . '</td>';
    print '<td class="' . $stripe . '">' . $row['account_name'] . '</td>';
    print '<td class="' . $stripe . '">' . $row['service_date_added'] . '</td>';
    print '</tr>';

    $stripe = ($stripe == 'even') ? 'odd' : 'even';
  }
  print '</table>';
}
else {
  print '<div class="table-no-data">No services</div>';
}

print $main->getTheme()->htmlDashboardBottom();
