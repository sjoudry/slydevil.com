<?php

use SlyDevil\Form\Element\Button;
use SlyDevil\Form\Element\Fieldset;
use SlyDevil\Form\Element\Form;
use SlyDevil\Form\Element\Group;
use SlyDevil\Form\Element\Input;
use SlyDevil\Form\Element\Select;
use SlyDevil\Form\Utility\ValidatorManager;
use SlyDevil\Site\Main;

include_once(__DIR__ . '/../../../includes/init.inc.php');

$main = new Main();
$main->getLogin()->handle('admin');

$PERMISSIONS = [
  'admin' => 'Adminstrator',
  'user' => 'Regular User',
];

$form = Form::create('users_edit');

$suffix = 'add';
$button_value = 'Add User';
$user_id = NULL;
$user_username = '';
$user_first_name = '';
$user_last_name = '';
$account_id = '';
$user_perms = [];
if (isset($_REQUEST['id'])) {
  $suffix = 'edit';
  $button_value = 'Save Changes';
  $user_id = $main->getSessionManager()->filterVariable($_REQUEST['id']);

  $result = $main->getDatabase()->query(
    "SELECT * FROM user JOIN account USING (account_id) WHERE user_id_public = '%s'",
    [
      $user_id
    ]
  );

  if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();

    $user_username = $user['user_username'];
    $user_first_name = $user['user_first_name'];
    $user_last_name = $user['user_last_name'];
    $account_id = $user['account_id_public'];
  }
  $result->close();

  $result = $main->getDatabase()->query(
    "SELECT * FROM user_perm WHERE user_id = (SELECT user_id FROM user WHERE user_id_public = '%s')",
    [
      $user_id
    ]
  );

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $user_perms[$row['user_perm_value']] = TRUE;
    }
  }

  $hidden = Input::create('hidden', 'id')
    ->setAttribute('value', $user_id);

  $form->addElement($hidden);
}

$username = Input::create('text', 'user_username')
  ->setAttribute('class', 'form-control')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $user_username)
  ->addLabel('User Email Address')
  ->addValidator('existance')
  ->addValidator('email');

$first_name = Input::create('text', 'user_first_name')
  ->setAttribute('class', 'form-control')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $user_first_name)
  ->addLabel('User First Name')
  ->addValidator('existance');

$last_name = Input::create('text', 'user_last_name')
  ->setAttribute('class', 'form-control')
  ->setAttribute('maxlength', 255)
  ->setAttribute('value', $user_last_name)
  ->addLabel('User Last Name')
  ->addValidator('existance');

$password = Input::create('password', 'user_password')
  ->setAttribute('class', 'form-control')
  ->setAttribute('maxlength', 16)
  ->addLabel('User Password')
  ->addValidator('contains_lowercase')
  ->addValidator('contains_number')
  ->addValidator('contains_uppercase')
  ->addValidator('length_long', NULL, 16)
  ->addValidator('length_short', NULL, 8)
  ->addValidator('match', NULL, $username, ValidatorManager::FIELD_NOT_MATCHES);

if ($user_id) {
  $password->setDescription('Only fill in this field to change the password.');
}
else {
  $password->addValidator('existance');
}

$result = $main->getDatabase()->query('SELECT account_id_public, account_name FROM account WHERE account_date_deleted IS NULL ORDER BY account_name');
$accounts = ['0' => '--- Select Account ---'];
while ($row = $result->fetch_assoc()) {
  $accounts[$row['account_id_public']] = $row['account_name'];
}

$account = Select::create('account_id')
  ->setAttribute('class', 'form-control')
  ->setOptions($accounts)
  ->setSelected($account_id)
  ->addLabel('Account')
  ->addValidator('existance');

$button = Button::create('user_' . $suffix . '_submit', $button_value);

$permissions = Group::create('checkbox', 'user_permission')
  ->setAttribute('class', 'hidden-title')
  ->addLabel('User permissions')
  ->addValidator('minimum_checked', NULL, 1);

foreach ($PERMISSIONS as $perm => $label) {
  $permission = Input::create('checkbox', 'user_permission')
    ->setAttribute('value', $perm)
    ->addLabel($label, Input::FORM_ELEMENT_LABEL_ALIGN_RIGHT);

  if (isset($user_perms[$perm])) {
    $permission->setAttribute('checked', TRUE);
  }

  $permissions->addElement($permission);
}

$perm_fieldset = Fieldset::create('user_permissions_' . $suffix . '_fieldset', ucfirst($suffix) . ' User Permissions')
  ->addElement($permissions);

$fieldset = Fieldset::create('user_' . $suffix . '_fieldset', ucfirst($suffix) . ' User')
  ->addElement($username)
  ->addElement($first_name)
  ->addElement($last_name)
  ->addElement($password)
  ->addElement($account)
  ->addElement($perm_fieldset)
  ->addElement($button);

$form->addElement($fieldset);

if ($form->submitted() && $form->validated()) {
  $duplicate_sql = "SELECT user_id_public FROM user WHERE user_username = '%s'";
  $duplicate_args = [
    $main->getSessionManager()->filterVariable($_REQUEST['user_username'])
  ];

  if ($user_id) {
    $duplicate_sql .= " AND user_id_public <> '%s'";
    $duplicate_args[] = $user_id;
  }

  $result = $main->getDatabase()->query($duplicate_sql, $duplicate_args);
  $count = $result->num_rows;
  $result->close();

  if ($count > 0) {
    $main->getErrorHandler()->addError('User Email exists already.');
  }
  else {
    $result = $main->getDatabase()->query(
      "SELECT account_id FROM account WHERE account_id_public = '%s'",
      [
        $main->getSessionManager()->filterVariable($_REQUEST['account_id'])
      ]
    );

    $account = $result->fetch_assoc();
    $account_id = $account['account_id'];
    $result->close();

    $user_id_db = 0;
    if ($user_id) {
      $args = [
        $main->getSessionManager()->filterVariable($_REQUEST['user_username']),
        $main->getSessionManager()->filterVariable($_REQUEST['user_first_name']),
        $main->getSessionManager()->filterVariable($_REQUEST['user_last_name']),
        $account_id,
      ];

      $sql = "UPDATE user SET user_username = '%s', user_first_name = '%s', user_last_name = '%s', account_id = %s";

      if (!empty($_REQUEST['user_password'])) {
        $sql .= ", user_password = '%s'";
        $args[] = $main->getSessionManager()->cryptPassword($main->getSessionManager()->filterVariable($_REQUEST['user_password']));
      }

      $sql .= " WHERE user_id_public = '%s'";
      $args[] = $user_id;

      $main->getDatabase()->query($sql, $args);

      $result = $main->getDatabase()->query(
        "SELECT user_id FROM user WHERE user_id_public = '%s'",
        [
          $user_id
        ]
      );

      $user = $result->fetch_assoc();
      $user_id_db = $user['user_id'];

      $main->getDatabase()->query(
        'DELETE FROM user_perm WHERE user_id = %d',
        [
          $user_id_db
        ]
      );

      $main->getSessionManager()->addMessage('User updated successfully');
    }
    else {
      $main->getDatabase()->query(
        "INSERT INTO user (user_id_public, user_username, user_first_name, user_last_name, account_id, user_password, user_date_added) VALUES
        ('%s', '%s', '%s', '%s', %s, '%s', NOW())",
        [
          $main->getSessionManager()->cryptPassword($main->getSessionManager()->filterVariable($_REQUEST['user_username']) . time()),
          $main->getSessionManager()->filterVariable($_REQUEST['user_username']),
          $main->getSessionManager()->filterVariable($_REQUEST['user_first_name']),
          $main->getSessionManager()->filterVariable($_REQUEST['user_last_name']),
          $account_id,
          $main->getSessionManager()->cryptPassword($main->getSessionManager()->filterVariable($_REQUEST['user_password'])),
        ]
      );

      $user_id_db = $main->getDatabase()->insert_id();

      $main->getSessionManager()->addMessage('New User added successfully');
    }

    foreach ($_REQUEST['user_permission'] as $index => $value) {
      $main->getDatabase()->query(
        "INSERT INTO user_perm (user_id, user_perm_value) VALUES (%d, '%s')",
        [
          $user_id_db,
          $main->getSessionManager()->filterVariable($value)
        ]
      );
    }

    header('Location: /dashboard/users/');
    exit;
  }
}

print $main->getTheme()->htmlDashboardTop('Sly Devil :: Clients :: ' . ucfirst($suffix));
print $form->render();
print $main->getTheme()->htmlDashboardBottom();
