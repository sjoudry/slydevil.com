<?php

use SlyDevil\Form\Element\Form;
use SlyDevil\Form\Element\Button;
use SlyDevil\Form\Element\Fieldset;
use SlyDevil\Form\Element\Html;
use SlyDevil\Form\Element\Input;
use SlyDevil\Site\Main;

include_once(__DIR__ . '/../../includes/init.inc.php');

$main = new Main();

$form = Form::create('login');

$username = Input::create('text', 'username')
  ->setAttribute('maxlength', 255)
  ->setAttribute('class', 'form-control')
  ->addLabel('Email Address')
  ->addValidator('existance')
  ->addValidator('email');

$password = Input::create('password', 'password')
  ->setAttribute('maxlength', 24)
  ->setAttribute('class', 'form-control')
  ->addLabel('Password')
  ->addValidator('existance');

$reset_link = Html::create('reset_link', "<a href='/login/forgot.php'>Forgot Password?</a>");

$button = Button::create('login_submit', 'Login', Button::TYPE_SUBMIT);

$fieldset = Fieldset::create('login_fieldset', 'Dashboard Login')
  ->addElement($username)
  ->addElement($password)
  ->addElement($button)
  ->addElement($reset_link);

$form->addElement($fieldset);

if ($form->submitted() && $form->validated()) {
  $main->getLogin()->setSession($_REQUEST['username'], $_REQUEST['password']);
  $main->getLogin()->handle('user', '/dashboard/', FALSE);
}

print $main->getTheme()->htmlLoginTop('Sly Devil :: Dashboard Login');
print $form->render();
print $main->getTheme()->htmlLoginBottom();
