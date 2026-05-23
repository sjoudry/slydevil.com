<?php

use SlyDevil\Form\Element\Button;
use SlyDevil\Form\Element\Fieldset;
use SlyDevil\Form\Element\Form;
use SlyDevil\Form\Element\Html;
use SlyDevil\Form\Element\Input;
use SlyDevil\Site\Main;

include_once(__DIR__ . '/../../includes/init.inc.php');

$main = new Main();

$form = Form::create('forgot');

$username = Input::create('text', 'username')
  ->setAttribute('maxlength', 255)
  ->setAttribute('class', 'form-control')
  ->addLabel('Email Address')
  ->addValidator('existance')
  ->addValidator('email');

$login_link = HTML::create('login_link', '<a href="index.php">Back to Login</a>');

$button = Button::create('reset_submit', 'Reset Password');

$fieldset = Fieldset::create('reset_fieldset', 'Forgot/Reset Password')
  ->addElement($username)
  ->addElement($button)
  ->addElement($login_link);

$form->addElement($fieldset);

if ($form->submitted() && $form->validated()) {
  $result = $main->getDatabase()->query(
    "SELECT user_first_name, user_id, user_username FROM user WHERE user_username = '%s'",
    [
      strtolower($main->getSessionManager()->filterVariable($_REQUEST['username']))
    ]
  );

  if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    $password = $main->getSessionManager()->generateRandomString(5);

    $main->getDatabase()->query(
      "UPDATE user SET user_password = '%s' WHERE user_id = %s",
      [
        $main->getSessionManager()->cryptPassword($password),
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

    $main->getSessionManager()->addMessage('Password Reset email has been sent!');
  }
  else {
    $main->getErrorHandler()->addError('Email Address does not exist.');
  }
}

print $main->getTheme()->htmlLoginTop('Sly Devil :: Forgot/Reset Password');
print $form->render();
print $main->getTheme()->htmlLoginBottom();
