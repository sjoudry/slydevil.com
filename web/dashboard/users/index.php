<?php

use SlyDevil\Database;
use SlyDevil\Login;
use SlyDevil\Theme;

include_once(__DIR__ . '/../../../includes/init.inc.php');

Login::handleLogin("admin");

print Theme::htmlDashboardTop("Sly Devil :: Users");
print "<div class='button'><a href='edit.php'><i class='fa fa-plus'></i> Add New User</a></div>\n";

$result = Database::query(
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
  print "<table border='0' cellpadding='2' cellspacing='0' width='100%'>\n";
  print "<tr>\n";
  print "<th>&nbsp;</th>\n";
  print "<th>&nbsp;</th>\n";
  print "<th>First Name</th>\n";
  print "<th>Last Name</th>\n";
  print "<th>Username</th>\n";
  print "<th>Account</th>\n";
  print "<th>Date Added</th>\n";
  print "</tr>\n";

  $stripe = "even";
  while ($row = $result->fetch_assoc()) {
    print "<tr>\n";
    print "<td class='" . $stripe . "'><a href='/dashboard/users/edit.php?id=" . $row["user_id_public"] . "'><i class='fa fa-pencil'></i></a></td>\n";
    print "<td class='" . $stripe . "'><a href='/dashboard/users/delete.php?id=" . $row["user_id_public"] . "'><i class='fa fa-trash-o'></i></a></td>\n";
    print "<td class='" . $stripe . "'>" . $row["user_first_name"] . "</td>\n";
    print "<td class='" . $stripe . "'>" . $row["user_last_name"] . "</td>\n";
    print "<td class='" . $stripe . "'>" . $row["user_username"] . "</td>\n";
    print "<td class='" . $stripe . "'>" . $row["account_name"] . "</td>\n";
    print "<td class='" . $stripe . "'>" . $row["date_added"] . "</td>\n";
    print "</tr>\n";

    $stripe = ($stripe == 'even') ? 'odd' : 'even';
  }
  print "</table>\n";
}
else {
  print "<div class='table-no-data'>No users</div>\n";
}

print Theme::htmlDashboardBottom();
