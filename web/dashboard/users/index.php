<?php

use SlyDevil\Site\Main;

include_once(__DIR__ . '/../../../includes/init.inc.php');

$main = new Main();
$main->getLogin()->handle("admin");

print $main->getTheme()->htmlDashboardTop("Sly Devil :: Users");
print '<div class="button"><a href="edit.php"><i class="fa fa-plus"></i> Add New User</a></div>';

$result = $main->getDatabase()->query(
  "SELECT
    user_id_public,
    user_username,
    user_first_name,
    user_last_name,
    account_name,
    DATE_FORMAT(user_date_added, '%%Y-%%m-%%d') date_added
  FROM
    user
  JOIN
    account
  USING
    (account_id)
  WHERE
    user_date_deleted IS NULL
  ORDER BY
    user_first_name, user_last_name"
);

if ($result->num_rows) {
  print '<table border="0" cellpadding="2" cellspacing="0" width="100%">';
  print '<tr>';
  print '<th>&nbsp;</th>';
  print '<th>&nbsp;</th>';
  print '<th>First Name</th>';
  print '<th>Last Name</th>';
  print '<th>Username</th>';
  print '<th>Account</th>';
  print '<th>Date Added</th>';
  print '</tr>';

  $stripe = 'even';
  while ($row = $result->fetch_assoc()) {
    print '<tr>';
    print '<td class="' . $stripe . '"><a href="/dashboard/users/edit.php?id=' . $row['user_id_public'] . '"><i class="fa fa-pencil"></i></a></td>';
    print '<td class="' . $stripe . '"><a href="/dashboard/users/delete.php?id=' . $row['user_id_public'] . '"><i class="fa fa-trash-o"></i></a></td>';
    print '<td class="' . $stripe . '">' . $row['user_first_name'] . '</td>';
    print '<td class="' . $stripe . '">' . $row['user_last_name'] . '</td>';
    print '<td class="' . $stripe . '">' . $row['user_username'] . '</td>';
    print '<td class="' . $stripe . '">' . $row['account_name'] . '</td>';
    print '<td class="' . $stripe . '">' . $row['date_added'] . '</td>';
    print '</tr>';

    $stripe = ($stripe == 'even') ? 'odd' : 'even';
  }
  print '</table>';
}
else {
  print '<div class="table-no-data">No users</div>';
}

print $main->getTheme()->htmlDashboardBottom();
