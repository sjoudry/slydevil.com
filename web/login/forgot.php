<?php

use SlyDevil\Database\Query;
use SlyDevil\Form\Element\Button;
use SlyDevil\Form\Element\Fieldset;
use SlyDevil\Form\Element\Form;
use SlyDevil\Form\Element\Html;
use SlyDevil\Form\Element\Text;
use SlyDevil\Session;
use SlyDevil\Theme;

include_once(__DIR__ . '/../../includes/init.inc.php');

$form = Form::create()
  ->setAction('forgot.php')
  ->setName('forgot');

$username = Text::create()
  ->setName('username')
  ->setMaxlength(255)
  ->addLabel('Email Address')
  ->setClass('form-control')
  ->addValidatorExistance()
  ->addValidatorEmail();

$login_link = HTML::create()
  ->setName('login_link')
  ->setContent("<a href='index.php'>Back to Login</a>");

$button = Button::create()
  ->setName('reset_submit')
  ->setValue('Reset Password');

$fieldset = Fieldset::create()
  ->setId('reset_fieldset')
  ->setLegend('Forgot/Reset Password')
  ->addElement($username)
  ->addElement($button)
  ->addElement($login_link);

$form->addElement($fieldset);
 
if ($form->submitted() && $form->validated()) {
  $result = Query::create(
    "SELECT user_first_name, user_id, user_username FROM user WHERE user_username = '%s'",
    [
      strtolower($_REQUEST['username'])
    ]
  )->result();
    
  if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    $password = Session::generateRandomString(5);
        
    Query::create(
      "UPDATE user SET user_password = '%s' WHERE user_id = %s",
      [
        Session::cryptPassword($password),
        $user['user_id']
      ]
    );

    $message = <<<EOS
<html>
    <body>
        
        <br /><br />
        
        <table border='0' cellpadding='2' cellspacing='1' width='600' align='center'>
            <tr>
                <td valign='top' width='600' style='font-family: verdana, geneva, sans-serif; font-size: 14px; line-height: 20px;'>
                    <span>Dear {$user['user_first_name']},</span><br /><br />
                    <span>Your password has been reset. Please use the following credentials to login:</span><br /><br />
                    <span>URL: <a href='http://www.slydevil.com/'>http://www.slydevil.com/</a></span><br />
                    <span>Username: <b>{$user['user_username']}</b></span><br />
                    <span>Password: <b>{$password}</b></span><br /><br />
                    <span>Thanks for your Business!</span><br /><br />
                    <span>Scott</span><br /><br /><br />
                </td>
            </tr>
        </table>
        
        <br /><br />
    
    </body>
</html>
EOS;

    // 2017-07-10 - replaced SMTP method with API.
    $data = [
      'from'       => 'Sly Devil Web Hosting <invoice@billing.slydevil.com>',
      'to'         => $user['user_first_name'] . ' ' . ($user['user_last_name'] ?? '') . '<' . $user['user_username'] . '>',
      'subject'    => 'Password Reset',
      'text'       => 'To view the message, please use an HTML compatible email viewer!',
      'html'       => $message,
      'bcc'        => 'sj@slydevil.com',
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
      print 'Email could not be sent - contact ' . $user['user_username'] . "<br>\n";
      exit;
    }

    $_SESSION['messages']['info'][] = 'Password Reset email has been sent!';
  }
  else {
    $form->addError('Email Address does not exist.');
  }
}

print Theme::htmlLoginTop('Sly Devil :: Forgot/Reset Password');
print $form->returnHTML();
print Theme::htmlLoginBottom();
