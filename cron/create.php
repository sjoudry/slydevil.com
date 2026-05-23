<?php

use SlyDevil\Site\Main;

include_once(__DIR__ . '/../includes/init.inc.php');

// Variables.
$packages = [];
$accounts = [];
$domains = [];
$services = [];
$invoices = [];
$invoice_number = 0;
$date_start = mktime(0, 0, 0, date('m'), 1, date('Y'));
$gst = 0.05;
$pst = 0.09;
$main = new Main();

// Load Packages.
$result = $main->getDatabase()->query('SELECT * FROM package');
while ($row = $result->fetch_assoc()) {
  $packages[$row['package_id']] = $row;
}

// Load Accounts.
$result = $main->getDatabase()->query('SELECT * FROM account JOIN user USING (account_id) ORDER BY account_name DESC');
while ($row = $result->fetch_assoc()) {
  $accounts[$row['account_id']] = $row;
}

// Load Domains.
$result = $main->getDatabase()->query('SELECT * FROM domain ORDER BY domain_date_added, domain_name');
while ($row = $result->fetch_assoc()) {
  $domains[$row['account_id']][] = $row;
}

// Load Services.
$result = $main->getDatabase()->query('SELECT * FROM service');
while ($row = $result->fetch_assoc()) {
  $services[$row['account_id']][] = $row;
}

// Load Invoices.
$result = $main->getDatabase()->query('SELECT * FROM invoice');
while ($row = $result->fetch_assoc()) {
  $invoices[$row['account_id']][] = $row;
  if ($row['invoice_number'] > $invoice_number) {
    $invoice_number = $row['invoice_number'];
  }
}

// loop accounts, create invoices.
foreach ($accounts as $account_id => $account) {
  $invoice_start_date = $date_start;
  $invoice_end_date = $date_start;
  $create_invoice = FALSE;
  $invoice_data = [];

  // Calculate start date.
  if (count($invoices[$account_id])) {
    foreach ($invoices[$account_id] as $invoice) {
      $end_date = strtotime($invoice['invoice_date_end']);
      if ($end_date > $invoice_start_date) {
        $invoice_start_date = $end_date;
      }
    }

    if ($invoice_start_date != $date_start) {
      $invoice_start_date = mktime(0, 0, 0, date('m', $invoice_start_date) + 1, 1, date('Y', $invoice_start_date));
    }

    if ($invoice_start_date <= $date_start) {
      if (empty($account['account_date_deleted']) || $invoice_start_date < strtotime($account['account_date_deleted'])) {
        $create_invoice = TRUE;
      }
    }
  }
  else {
    $invoice_start_date = strtotime($account['account_date_added']);

    if ($invoice_start_date <= $date_start) {
      $create_invoice = TRUE;
    }
  }

  // Calculate end date and create invoice.
  if ($create_invoice) {
    $invoice_number++;

    $out_invoice_number = sprintf('SDWH-%05d', $invoice_number);
    $out_invoice_date = date('M d, Y', $invoice_start_date);
    $out_invoice_due = date('M d, Y', mktime(0, 0, 0, date('m', $invoice_start_date) + 1, 1, date('Y', $invoice_start_date)));

    $out_address = $account['user_first_name'] . ' ' . $account['user_last_name'];
    if (!empty($account['account_company'])) {
      $out_address .= '<br />' . $account['account_company'];
    }
    if (!empty($account['account_street_address1'])) {
      $out_address .= '<br />' . $account['account_street_address1'];
    }
    if (!empty($account['account_street_address2'])) {
      $out_address .= '<br />' . $account['account_street_address2'];
    }
    if (!empty($account['account_city']) || !empty($account['account_province']) || !empty($account['account_country'])) {
      $out_address .= '<br />';

      if (!empty($account['account_city'])) {
        $out_address .= $account['account_city'];

        if (!empty($account['account_province'])) {
          $out_address .= ', ' . $account['account_province'];

          if (!empty($account['account_country'])) {
            $out_address .= ', ' . $account['account_country'];
          }
        }
        else {
          if (!empty($account['account_country'])) {
            $out_address .= ', ' . $account['account_country'];
          }
        }
      }
      else {
        if (!empty($account['account_province'])) {
          $out_address .= $account['account_province'];

          if (!empty($account['account_country'])) {
            $out_address .= ', ' . $account['account_country'];
          }
        }
      }
    }
    if (!empty($account['account_postal'])) {
      $out_address .= '<br />' . $account['account_postal'];
    }

    $output = <<<EOS
<html>
  <body>
    <br /><br />
    <table border='0' cellpadding='2' cellspacing='1' width='600' align='center' style='border-bottom: 1px solid #E5E5E5;'>
      <tr>
        <td valign='top' width='600' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'>
          <span>Dear {$account['user_first_name']},</span>
          <br /><br />
          <span>Below is your newest invoice. To view this and all previous invoices, please login to your Billing Dashboard using the credentials below:</span>
          <br /><br />
          <span>URL: <a href='http://www.slydevil.com/'>http://www.slydevil.com/</a></span>
          <br />
          <span>Username: <b>{$account['user_username']}</b></span>
          <br />
          <span>Password: <b>********</b></span>
          <br /><br />
          <span>If you have forgotten your password, please use the <a href='http://slydevil.com/login/forgot.php'>Forgot Password</a> feature or reply to this email and I can reset it for you.</span>
          <br /><br />
          <span>Thanks for your Business!</span>
          <br /><br />
          <span>Scott</span>
          <br /><br /><br />
        </td>
      </tr>
    </table>

    <br /><br /><br />

    <table border='0' cellpadding='2' cellspacing='1' width='600' align='center'>
      <tr>
        <td valign='top' width='300'><img src='http://slydevil.com/includes/images/logo.png' height='42' width='200' /></td>
        <td valign='top' width='300' align='right'>
          <table border='0' cellpadding='0' cellspacing='0'>
            <tr>
              <td align='right' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'>Invoice:</td>
              <td style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'>&nbsp;{$out_invoice_number}</td>
            </tr>
            <tr>
              <td align='right' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'>Invoice Date:</td>
              <td style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'>&nbsp;{$out_invoice_date}</td>
            </tr>
            <tr>
              <td align='right' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'>Due Date:</td>
              <td style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'>&nbsp;{$out_invoice_due}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <br />

    <table border='0' cellpadding='2' cellspacing='1' width='600' align='center'>
      <tr>
        <td valign='top' width='50'>&nbsp;</td>
        <td valign='top' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'>{$out_address}</td>
      </tr>
    </table>

    <br /><br />

    <table border='0' cellpadding='2' cellspacing='1' width='600' align='center'>
      <tr>
        <th width='75' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'>Qty</th>
        <th width='375' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'>Service Description</th>
        <th width='75' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'>Fee</th>
        <th width='75' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'>Amt</th>
      </tr>
EOS;

    // calculate end date.
    $number_of_months = 1;

    switch ($account['account_billing']) {
      case 4:
        $number_of_months = 12;
        break;

      case 3:
        $number_of_months = 6;
        break;

      case 2:
        $number_of_months = 3;
        break;

      default:
        $number_of_months = 1;
        break;
    }

    $invoice_end_date = mktime(0, 0, 0, date('m', $invoice_start_date) + $number_of_months, 1, date('Y', $invoice_start_date)) - 1;

    if (!empty($account['account_date_deleted']) && $invoice_end_date >= strtotime($account['account_date_deleted'])) {
      $invoice_end_date = strtotime($account['account_date_deleted']);
    }

    // Determine domains.
    $account_domains = [];
    foreach ($domains[$account_id] as $domain) {
      if (strtotime($domain['domain_date_added']) <= $invoice_end_date) {
        if (empty($domain['domain_date_deleted'])) {
          $account_domains[] = $domain;
        }
        else {
          if (strtotime($domain['domain_date_deleted']) >= $invoice_start_date) {
            $account_domains[] = $domain;
          }
        }
      }
    }

    // Determine services.
    $account_services = [];
    if (!empty($services[$account_id])) {
      foreach ($services[$account_id] as $service) {
        if (strtotime($service['service_date_added']) <= $invoice_end_date) {
          if (empty($service['service_date_deleted'])) {
            $account_services[] = $service;
          }
          else {
            if (strtotime($service['service_date_deleted']) >= $invoice_start_date) {
              $account_services[] = $service;
            }
          }
        }
      }
    }

    // Create invoice rows.
    $invoice_data[] = [
      'quantity' => '',
      'description' => 'Charges for Current Invoice (' . date('M d, Y', $invoice_start_date) . ' - ' . date('M d, Y', $invoice_end_date) . ')',
      'fee' => '',
      'indent' => 0,
    ];

    $invoice_data[] = [
      'quantity' => '',
      'description' => 'Account: ' . $account['account_name'],
      'fee' => '',
      'indent' => 1,
    ];

    $invoice_data[] = [
      'quantity' => '',
      'description' => '',
      'fee' => '',
      'indent' => 0,
    ];

    $invoice_data[] = [
      'quantity' => $number_of_months,
      'description' => $packages[$account['package_id']]['package_name'] . ' (' . date('M d, Y', $invoice_start_date) . ' - ' . date('M d, Y', $invoice_end_date) . ')',
      'fee' => $packages[$account['package_id']]['package_fee'],
      'indent' => 2,
    ];

    $included_domains = $packages[$account['package_id']]['package_domains'];

    for ($i = 0; $i < $included_domains; $i++) {
      $invoice_data[] = [
        'quantity' => '',
        'description' => $account_domains[$i]['domain_name'],
        'fee' => '',
        'indent' => 3,
      ];

      unset($account_domains[$i]);
    }

    if (count($account_domains)) {
      $invoice_data[] = [
        'quantity' => '',
        'description' => '',
        'fee' => '',
        'indent' => 0,
      ];

      $invoice_data[] = [
        'quantity' => '',
        'description' => 'Extra Domains',
        'fee' => '',
        'indent' => 2,
      ];

      foreach ($account_domains as $index => $domain) {
        $invoice_data[] = [
          'quantity' => $number_of_months,
          'description' => $domain['domain_name'],
          'fee' => $packages[$account['package_id']]['package_domains_fee'],
          'indent' => 3,
        ];
      }
    }

    if (count($account_services)) {
      $invoice_data[] = [
        'quantity' => '',
        'description' => '',
        'fee' => '',
        'indent' => 0,
      ];

      $invoice_data[] = [
        'quantity' => '',
        'description' => 'Extra Services',
        'fee' => '',
        'indent' => 2,
      ];

      foreach ($account_services as $index => $service) {
        $invoice_data[] = [
          'quantity' => $number_of_months,
          'description' => $service['service_name'],
          'fee' => $service['service_fee'],
          'indent' => 3,
        ];
      }
    }

    // Determine tax.
    $invoice_gst = 0;
    $invoice_pst = 0;
    if (strtolower($account['account_country']) == 'canada') {
      $invoice_gst = $gst;

      if (strtolower($account['account_province']) == 'nova scotia') {
        $invoice_pst = $pst;
      }
    }

    // Insert into database.
    $main->getDatabase()->query(
      "INSERT INTO invoice (invoice_id_public, invoice_number, account_id, invoice_date_start, invoice_date_end, invoice_gst_rate, invoice_pst_rate, invoice_date_added)
      VALUES ('%s', %s, %s, '%s', '%s', %s, %s, NOW())",
      [
        $main->getSessionManager()->cryptPassword($invoice_number . time()),
        $invoice_number,
        $account_id,
        date('Y-m-d', $invoice_start_date),
        date('Y-m-d', $invoice_end_date),
        $invoice_gst,
        $invoice_pst
      ]
    );

    $invoice_id = $main->getDatabase()->insert_id();

    $total = 0;
    $row_style = ' background-color: #C4E2FF;';
    foreach ($invoice_data as $index => $row) {
      $main->getDatabase()->query(
        "INSERT INTO invoice_data (invoice_id, invoice_data_sequence, invoice_data_description, invoice_data_quantity, invoice_data_fee, invoice_data_indent)
        VALUES (%s, %s, '%s', %f, %f, %d)",
        [
          $invoice_id,
          ($index + 1),
          $row['description'],
          isset($row['quantity']) ? $row['quantity'] : 0,
          isset($row['fee']) ? $row['fee'] : 0,
          $row['indent']
        ]
      );

      $subtotal = round((int) $row['quantity'] * (float) $row['fee'], 2);
      $total += $subtotal;
      $indent = str_repeat('&nbsp;&nbsp;', $row['indent']);

      $output .= '<tr>';

      if ($row['quantity'] > 0) {
        $output .= '<td align="right" style="font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;';
        $output .= $row_style;
        $output .= '" valign="top">';
        $output .= sprintf('%.2f', round($row['quantity'], 2));
        $output .= '</td>';
      }
      else {
        if ($row_style) {
          $output .= '<td style="' . $row_style . '">&nbsp;</td>';
        }
        else {
          $output .= '<td>&nbsp;</td>';
        }
      }

      $output .= '<td align="left" style="font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;';
      $output .= $row_style;
      $output .= '" valign="top">';
      $output .= $indent . $row['description'];
      $output .= '</td>';

      if ($row['fee'] > 0) {
        $output .= '<td align="right" style="font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;';
        $output .= $row_style;
        $output .= '" valign="top">';
        $output .= sprintf('$%.2f', round($row['fee'], 2));
        $output .= '</td>';
      }
      else {
        if ($row_style) {
          $output .= '<td style="' . $row_style . '">&nbsp;</td>';
        }
        else {
          $output .= '<td>&nbsp;</td>';
        }
      }

      if ($row['quantity'] > 0 && $row['fee'] > 0) {
        $output .= '<td align="right" style="font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;';
        $output .= $row_style;
        $output .= '" valign="top">';
        $output .= sprintf('$%.2f', $subtotal);
        $output .= '</td>';
      }
      else {
        if ($row_style) {
          $output .= '<td style="' . $row_style . '">&nbsp;</td>';
        }
        else {
          $output .= '<td>&nbsp;</td>';
        }
      }

      $output .= '</tr>';

      if ($row_style) {
        $row_style = '';
      }
      else {
        $row_style = ' background-color: #C4E2FF;';
      }
    }

    if ($row_style) {
      $output .= <<<EOS
      <tr>
        <td style='{$row_style}'></td>
        <td style='{$row_style}'>&nbsp;</td>
        <td style='{$row_style}'></td>
        <td style='{$row_style}'></td>
      </tr>
EOS;

      $row_style = '';
    }
    else {
      $output .= <<<EOS
      <tr>
        <td></td>
        <td>&nbsp;</td>
        <td></td>
        <td></td>
      </tr>
EOS;

      $row_style = ' background-color: #C4E2FF;';
    }

    $calculated_gst = round(($total * $invoice_gst), 2);
    $calculated_pst = round(($total * $invoice_pst), 2);

    $total += $calculated_gst + $calculated_pst;

    if ($invoice_gst > 0) {
      $out_rate = $invoice_gst * 100;
      $out_amount = sprintf('$%.2f', $calculated_gst);

      if ($row_style) {
        $output .= <<<EOS
      <tr>
        <td style='{$row_style}'></td>
        <td align='right' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;{$row_style}'>GST ({$out_rate}%)</td>
        <td style='{$row_style}'></td>
        <td align='right' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;{$row_style}'>{$out_amount}</td>
      </tr>
EOS;

        $row_style = '';
      }
      else {
        $output .= <<<EOS
      <tr>
        <td></td>
        <td align='right' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'>GST ({$out_rate}%)</td>
        <td></td>
        <td align='right' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'>{$out_amount}</td>
      </tr>
EOS;

        $row_style = ' background-color: #C4E2FF;';
      }
    }

    if ($invoice_pst > 0) {
      $out_rate = $invoice_pst * 100;
      $out_amount = sprintf('$%.2f', $calculated_pst);

      if ($row_style) {
        $output .= <<<EOS
      <tr>
        <td style='{$row_style}'></td>
        <td align='right' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;{$row_style}'>PST ({$out_rate}%)</td>
        <td style='{$row_style}'></td>
        <td align='right' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;{$row_style}'>{$out_amount}</td>
      </tr>
EOS;

        $row_style = '';
      }
      else {
        $output .= <<<EOS
      <tr>
        <td></td>
        <td align='right' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'>PST ({$out_rate}%)</td>
        <td></td>
        <td align='right' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'>{$out_amount}</td>
      </tr>
EOS;

        $row_style = ' background-color: #C4E2FF;';
      }
    }

    $total = sprintf('$%.2f', $total);

    if ($row_style) {
      $output .= <<<EOS
      <tr>
        <td style='{$row_style}'></td>
        <td align='right' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;{$row_style}'>Total</td>
        <td style='{$row_style}'></td>
        <td align='right' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;{$row_style}'>{$total}</td>
      </tr>
EOS;

      $row_style = '';
    }
    else {
      $output .= <<<EOS
      <tr>
        <td></td>
        <td align='right' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'>Total</td>
        <td></td>
        <td align='right' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'>{$total}</td>
      </tr>
EOS;

      $row_style = ' background-color: #C4E2FF;';
    }

    $output .= <<<EOS
    </table>

    <br />

    <table border='0' cellpadding='2' cellspacing='1' width='600' align='center'>
      <tr>
        <td align='right' style='font-family: verdana, geneva, sans-serif; font-size: 14px; color: #C91111; line-height: 20px;'>All prices in Canadian Dollars</td>
      </tr>
    </table>

    <br />

    <table border='0' cellpadding='2' cellspacing='1' width='600' align='center'>
      <tr>
        <td valign='top' width='50%' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'><u>Make Payment To:</u><br />&nbsp;&nbsp;Scott Joudry<br />&nbsp;&nbsp;174 Acres Road<br />&nbsp;&nbsp;Williamswood, N.S., Canada<br />&nbsp;&nbsp;B3V 1E3</td>
        <td valign='top' width='50%' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'><u>Contact:</u><br/>&nbsp;&nbsp;Email: sj@slydevil.com<br/>&nbsp;&nbsp;Web: https://www.slydevilhost.com/<br/>&nbsp;&nbsp;Phone: 1-(902)-441-6516</td>
      </tr>
    </table>

    <br /><br />

  </body>
</html>
EOS;

    // 2017-07-10 - replaced SMTP method with API.
    $data = [
      'from' => 'Sly Devil Web Hosting <invoice@billing.slydevil.com>',
      'to' => $account['user_first_name'] . ' ' . $account['user_last_name'] . '<' . $account['user_username'] . '>',
      'subject' => 'New Web Hosting Invoice - ' . sprintf('SDWH-%05d', $invoice_number),
      'text' => 'To view the message, please use an HTML compatible email viewer!',
      'html' => $output,
      'bcc' => 'sj@slydevil.com',
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_USERPWD, 'api:key-5amydh9fpw70io8tu2hdrbtaknnkbw09');
    curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v3/billing.slydevil.com/messages');
    $result = curl_exec($ch);

    if (!$result) {
      print 'Email could not be sent - contact ' . $account['user_username'] . '<br>';
    }
  }
}
