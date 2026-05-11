<?php

use SlyDevil\Database;
use Slydevil\Login;
use SlyDevil\Theme;

include_once(__DIR__ . '/../../../includes/init.inc.php');

Login::handleLogin('admin');

print Theme::htmlDashboardTop('Hosting :: Accounts');
print "<div class='button'><a href='edit.php'><i class='fa fa-plus'></i> Add New Account</a></div>\n";

$result = Database::query(
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
  print "<table border='0' cellpadding='2' cellspacing='0' width='100%'>\n";
  print "<tr>\n";
  print "<th>&nbsp;</th>\n";
  print "<th>&nbsp;</th>\n";
  print "<th>Acount</th>\n";
  print "<th>Package</th>\n";
  print "<th>Dom</th>\n";
  print "<th>Ser</th>\n";
  print "<th>Inv</th>\n";
  print "<th>Date Added</th>\n";
  print "</tr>\n";

  $stripe = 'even';
  while ($row = $result->fetch_assoc()) {
    print "<tr>\n";
    print "<td class='" . $stripe . "'><a href='edit.php?id=" . $row["account_id_public"] . "'><i class='fa fa-pencil'></i></a></td>\n";
    print "<td class='" . $stripe . "'><a href='delete.php?id=" . $row["account_id_public"] . "'><i class='fa fa-trash-o'></i></a></td>\n";
    print "<td class='" . $stripe . "'>" . $row["account_name"] . "</td>\n";
    print "<td class='" . $stripe . "'>" . $row["package_name"] . "</td>\n";
    print "<td class='" . $stripe . "'>" . $row["accounts"] . "</td>\n";
    print "<td class='" . $stripe . "'>" . $row["services"] . "</td>\n";
    print "<td class='" . $stripe . "'>" . $row["invoices"] . "</td>\n";
    print "<td class='" . $stripe . "'>" . $row["account_date_added"] . "</td>\n";
    print "</tr>\n";

    $stripe = ($stripe == 'even') ? 'odd' : 'even';
  }
  print "</table>\n";
}
else {
  print "<div class='table-no-data'>No accounts</div>\n";
}

print Theme::htmlDashboardBottom();
