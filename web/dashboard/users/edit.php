<?php

use SlyDevil\Database;
use SlyDevil\Env;
use SlyDevil\Form\Element\Base;
use SlyDevil\Form\Element\Button;
use SlyDevil\Form\Element\Checkbox;
use SlyDevil\Form\Element\CheckboxGroup;
use SlyDevil\Form\Element\Fieldset;
use SlyDevil\Form\Element\Form;
use SlyDevil\Form\Element\Hidden;
use SlyDevil\Form\Element\Password;
use SlyDevil\Form\Element\Select;
use SlyDevil\Form\Element\Text;
use SlyDevil\Login;
use SlyDevil\Session;
use SlyDevil\Theme;

include_once(__DIR__ . '/../../../includes/init.inc.php');

Login::handleLogin('admin');

$PERMISSIONS = [
  "admin" => "Adminstrator",
  "user"  => "Regular User",
];

$form = Form::create()
  ->setAction('/dashboard/users/edit.php')
  ->setName('users_edit');

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
  $user_id = Env::filterVariable($_REQUEST['id']);
  
  $result = Database::query(
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
    
  $result = Database::query(
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

  $hidden = Hidden::create()
    ->setName('id')
    ->setValue($user_id);

  $form->addElement($hidden);
}

$username = Text::create()
  ->setName('user_username')
  ->setMaxlength(255)
  ->setValue($user_username)
  ->addLabel('User Email Address')
  ->setClass('form-control')
  ->addValidatorExistance()
  ->addValidatorEmail();

$first_name = Text::create()
  ->setName('user_first_name')
  ->setMaxlength(255)
  ->setValue($user_first_name)
  ->setClass('form-control')
  ->addLabel('User First Name')
  ->addValidatorExistance();
    
$last_name = Text::create()
  ->setName('user_last_name')
  ->setMaxlength(255)
  ->setValue($user_last_name)
  ->setClass('form-control')
  ->addLabel('User Last Name')
  ->addValidatorExistance();

$password = Password::create()
  ->setName('user_password')
  ->setMaxlength(16)
  ->addLabel('User Password')
  ->setClass('form-control')
  ->addValidatorContainsLowercase()
  ->addValidatorContainsNumber()
  ->addValidatorContainsUppercase()
  ->addValidatorLengthLong(16)
  ->addValidatorLengthShort(8)
  ->addValidatorMatch('user_username', 'User Email Address', Base::VALIDATES_IF_FIELD_NOT_MATCHES);

if ($user_id) {
  $password->setDescription('Only fill in this field to change the password.');
}
else {
  $password->addValidatorExistance();
}

$result = Database::query('SELECT account_id_public, account_name FROM account WHERE account_date_deleted IS NULL ORDER BY account_name');
$accounts = ['0' => '--- Select Account ---'];
while ($row = $result->fetch_assoc()) {
  $accounts[$row['account_id_public']] = $row['account_name'];
}

$account = Select::create()
  ->setName('account_id')
  ->setOptions($accounts)
  ->setSelected($account_id)
  ->addLabel('Account')
  ->setClass('form-control')
  ->addValidatorExistance();
    
$button = Button::create()
  ->setName('user_' . $suffix . '_submit')
  ->setValue($button_value);

$permissions = CheckboxGroup::create()
  ->setName('user_permission')
  ->addValidatorMinimumChecked(1);
    
foreach ($PERMISSIONS as $perm => $label) {
  $permission = Checkbox::create()
    ->setName('user_permission_' . $perm)
    ->setValue($perm)
    ->addLabel($label, Base::FORM_ELEMENT_LABEL_ALIGN_RIGHT);
        
  if (isset($user_perms[$perm])) {
    $permission->setChecked(TRUE);
  }
    
  $permissions->addElement($permission);
}

$perm_fieldset = Fieldset::create()
  ->setId('user_permissions_' . $suffix . '_fieldset')
  ->setLegend(ucfirst($suffix) . ' User Permissions')
  ->addElement($permissions);

$fieldset = Fieldset::create()
  ->setId('user_' . $suffix . '_fieldset')
  ->setLegend(ucfirst($suffix) . ' User')
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
    $_REQUEST['user_username']
  ];

  if ($user_id) {
    $duplicate_sql .= " AND user_id_public <> '%s'";
    $duplicate_args[] = $user_id;
  }

  $result = Database::query($duplicate_sql, $duplicate_args);
  $count = $result->num_rows;
  $result->close();

  if ($count > 0) {
    $form->addError('User Email exists already.');
  }
  else {
    $result = Database::query(
      "SELECT account_id FROM account WHERE account_id_public = '%s'",
      [
        $_REQUEST['account_id']
      ]
    );

    $account = $result->fetch_assoc();
    $account_id = $account['account_id'];
    $result->close();
    
    $user_id_db = 0;
    if ($user_id) {
      $args = [
        $_REQUEST['user_username'],
        $_REQUEST['user_first_name'],
        $_REQUEST['user_last_name'],
        $account_id,
      ];

      $sql = "UPDATE user SET user_username = '%s', user_first_name = '%s', user_last_name = '%s', account_id = %s";
            
      if (!empty($_REQUEST['user_password'])) {
        $sql .= ", user_password = '%s'";
        $args[] = Session::cryptPassword($_REQUEST['user_password']);
      }
            
      $sql .= " WHERE user_id_public = '%s'";
      $args[] = $user_id;

      Database::query($sql, $args);

      $result = Database::query(
        "SELECT user_id FROM user WHERE user_id_public = '%s'",
        [
          $user_id
        ]
      );
            
      $user = $result->fetch_assoc();
      $user_id_db = $user['user_id'];
            
      Database::query(
        'DELETE FROM user_perm WHERE user_id = %d',
        [
          $user_id_db
        ]
      );
            
      $_SESSION['messages']['info'][] = 'User updated successfully';
    }
    else {
      Database::query(
        "INSERT INTO user (user_id_public, user_username, user_first_name, user_last_name, account_id, user_password, user_date_added) VALUES
        ('%s', '%s', '%s', '%s', %s, '%s', NOW())",
        [
          Session::cryptPassword($_REQUEST['user_username'] . time()),
          $_REQUEST['user_username'],
          $_REQUEST['user_first_name'],
          $_REQUEST['user_last_name'],
          $account_id,
          Session::cryptPassword($_REQUEST['user_password']),
        ]
      );

      $user_id_db = Database::insert_id();

      $_SESSION['messages']['info'][] = 'New User added successfully';
    }
        
    foreach ($_REQUEST['user_permission'] as $index => $value) {
      Database::query(
        "INSERT INTO user_perm (user_id, user_perm_value) VALUES (%d, '%s')",
        [
          $user_id_db,
          $value
        ]
      );
    }
        
    header('Location: /dashboard/users/');
    exit;
  }
}

print Theme::htmlDashboardTop('Sly Devil :: Clients :: ' . ucfirst($suffix));
print $form->returnHTML();
print Theme::htmlDashboardBottom();
