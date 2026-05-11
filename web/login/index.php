<?php

use Slydevil\Form\Element\Form;
use Slydevil\Form\Element\Button;
use Slydevil\Form\Element\Fieldset;
use Slydevil\Form\Element\Html;
use Slydevil\Form\Element\Password;
use Slydevil\Form\Element\Text;
use SlyDevil\Login;
use SlyDevil\Theme;

include_once(__DIR__ . '/../../includes/init.inc.php');

$form = Form::create()
  ->setAction('/login/')
  ->setName('login');

$username = Text::create()
  ->setName('username')
  ->setMaxlength(255)
  ->addLabel('Email Address')
  ->setClass('form-control')
  ->addValidatorExistance()
  ->addValidatorEmail();

$password = Password::create()
  ->setName('password')
  ->setMaxlength(24)
  ->addLabel('Password')
  ->setClass('form-control')
  ->addValidatorExistance();

$reset_link = Html::create()
  ->setName('reset_link')
  ->setContent("<a href='forgot.php'>Forgot Password?</a>");

$button = Button::create()
  ->setName('login_submit')
  ->setValue('Login');

$fieldset = Fieldset::create()
  ->setId('login_fieldset')
  ->setLegend('Dashboard Login')
  ->addElement($username)
  ->addElement($password)
  ->addElement($button)
  ->addElement($reset_link);

$form->addElement($fieldset);

if ($form->submitted() && $form->validated()) {
  Login::setLogin($_REQUEST['username'], $_REQUEST['password']);
  Login::handleLogin('user', '/dashboard/', FALSE, $form);
}

print Theme::htmlLoginTop('Sly Devil :: Dashboard Login');
print $form->returnHTML();
print Theme::htmlLoginBottom();
