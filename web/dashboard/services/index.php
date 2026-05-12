<?php

use SlyDevil\Database;
use Slydevil\Login;
use SlyDevil\Theme;

include_once(__DIR__ . '/../../../includes/init.inc.php');

Login::handleLogin('admin');

print Theme::htmlDashboardTop('Hosting :: Services');
print "<div class='button'><a href='edit.php'><i class='fa fa-plus'></i> Add New Service</a></div>\n";

$result = Database::query(
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
  print "<table border='0' cellpadding='2' cellspacing='0' width='100%'>\n";
  print "<tr>\n";
  print "<th>&nbsp;</th>\n";
  print "<th>&nbsp;</th>\n";
  print "<th>Service Name</th>\n";
  print "<th>Account Name</th>\n";
  print "<th>Date Added</th>\n";
  print "</tr>\n";

  $stripe = 'even';
  while ($row = $result->fetch_assoc()) {
    print "<tr>\n";
    print "<td class='" . $stripe . "'><a href='edit.php?id=" . $row["service_id_public"] . "'><i class='fa fa-pencil'></i></a></td>\n";
    print "<td class='" . $stripe . "'><a href='delete.php?id=" . $row["service_id_public"] . "'><i class='fa fa-trash-o'></i></a></td>\n";
    print "<td class='" . $stripe . "'>" . $row["service_name"] . "</td>\n";
    print "<td class='" . $stripe . "'>" . $row["account_name"] . "</td>\n";
    print "<td class='" . $stripe . "'>" . $row["service_date_added"] . "</td>\n";
    print "</tr>\n";

    $stripe = ($stripe == 'even') ? 'odd' : 'even';
  }
  print "</table>\n";
}
else {
  print "<div class='table-no-data'>No services</div>\n";
}

print Theme::htmlDashboardBottom();
