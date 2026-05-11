<?php

use SlyDevil\Database;
use Slydevil\Login;
use SlyDevil\Theme;

include_once(__DIR__ . '/../../../includes/init.inc.php');

Login::handleLogin('admin');

print Theme::htmlDashboardTop('Hosting :: Packages');
print "<div class='button'><a href='edit.php'><i class='fa fa-plus'></i> Add New Package</a></div>\n";

$result = Database::query(
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
  print "<table border='0' cellpadding='2' cellspacing='0' width='100%'>\n";
  print "<tr>\n";
  print "<th>&nbsp;</th>\n";
  print "<th>&nbsp;</th>\n";
  print "<th>Package Name</th>\n";
  print "<th>Accounts</th>\n";
  print "<th>Date Added</th>\n";
  print "</tr>\n";

  $stripe = 'even';
  while ($row = $result->fetch_assoc()) {
    print "<tr>\n";
    print "<td class='" . $stripe . "'><a href='edit.php?id=" . $row["package_id_public"] . "'><i class='fa fa-pencil'></i></a></td>\n";
    print "<td class='" . $stripe . "'><a href='delete.php?id=" . $row["package_id_public"] . "'><i class='fa fa-trash-o'></i></a></td>\n";
    print "<td class='" . $stripe . "'>" . $row["package_name"] . "</td>\n";
    print "<td class='" . $stripe . "'>" . $row["accounts"] . "</td>\n";
    print "<td class='" . $stripe . "'>" . $row["package_date_added"] . "</td>\n";
    print "</tr>\n";

    $stripe = ($stripe == 'even') ? 'odd' : 'even';
  }
  print "</table>\n";
}
else {
  print "<div class='table-no-data'>No packages</div>\n";
}

print Theme::htmlDashboardBottom();
