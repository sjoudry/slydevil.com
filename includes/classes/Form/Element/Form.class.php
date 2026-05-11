<?php

namespace SlyDevil\Form\Element;

use SlyDevil\Env;
use SlyDevil\Form\Helper;
use SlyDevil\Session;

class Form extends Base {

  public const FORM_SUBMITTED_HIDDEN_NAME = 'FORM-SUBMITTED';

  public const FORM_TOKEN_HIDDEN_NAME = 'FORM-TOKEN';

  protected array $availableAttributes = [
    'accept-charset',
    'action',
    'autocomplete',
    'class',
    'dir',
    'enctype',
    'id',
    'lang',
    'method',
    'name',
    'style',
    'target',
    'title',
  ];

  protected array $availableEvents = [
    'click',
    'dblclick',
    'keydown',
    'keypress',
    'keyup',
    'mousedown',
    'mousemove',
    'mouseout',
    'mouseover',
    'mouseup',
    'reset',
    'submit',
  ];

  protected bool $useSessionToken = TRUE;

  public function __construct() {
    $this->elementType = 'form';

    Helper::addStylesheet('css/Form.css');
    Helper::addJavascript('js/Form.js');

    return $this;
  }

	public function disableToken() {
		$this->useSessionToken = FALSE;

		return $this;
	}

  public function returnHTML() {
    $this->validateConfig();
    $this->prepareElement();

    $output = Helper::returnStylesheets();
    $output .= Helper::returnJavascript();

    $hidden = Hidden::create()
      ->setName(self::FORM_SUBMITTED_HIDDEN_NAME)
      ->setId(self::FORM_SUBMITTED_HIDDEN_NAME)
      ->setValue($this->getId());

    $output .= $this->returnHTMLPreText();
    $output .= $this->returnHTMLDivBegin();
    $output .= $this->returnHTMLErrors();
    $output .= '<form';
    $output .= $this->returnHTMLAttributes();
    $output .= '/>';

    $output .= $hidden->returnHTML();

    if ($this->useSessionToken) {
      $hidden = Hidden::create()
        ->setName(self::FORM_TOKEN_HIDDEN_NAME)
        ->setId(self::FORM_TOKEN_HIDDEN_NAME);

      if (
        isset($_REQUEST[self::FORM_TOKEN_HIDDEN_NAME]) &&
        Session::checkFormToken($this->getId(), Env::filterVariable($_REQUEST[self::FORM_TOKEN_HIDDEN_NAME]))
      ) {
        $hidden->setValue(Env::filterVariable($_REQUEST[self::FORM_TOKEN_HIDDEN_NAME]));
      }
      else {
        $hidden->setValue(Session::setFormToken($this->getId()));
      }

      $output .= $hidden->returnHTML();
    }

    foreach ($this->elements as $form_element) {
      $output .= $form_element->returnHTML();
    }

    $output .= '</form>';
    $output .= $this->returnHTMLDivEnd();
    $output .= $this->returnHTMLPostText();
    $output .= $this->returnHTMLDescription();

    return $output;
  }

  public function submitted() {
    $this->validateConfig();

    if (isset($_REQUEST[self::FORM_SUBMITTED_HIDDEN_NAME]) && $_REQUEST[self::FORM_SUBMITTED_HIDDEN_NAME] == $this->getId()) {
      if (isset($_REQUEST[self::FORM_TOKEN_HIDDEN_NAME]) && !Session::checkFormToken($this->getId(), $_REQUEST[self::FORM_TOKEN_HIDDEN_NAME])) {
        $this->errors[] = 'Form Token is no longer valid. Please re-submit the form';
      }

      return TRUE;
    }

    return FALSE;
  }

  public function validated($elements = NULL) {
    $this->validateConfig();

    $form_elements = $elements ?? $this->elements;
    $valid = TRUE;
    foreach ($form_elements as $element) {
      if (count($element->getElements())) {
        if (!$this->validated($element->getElements())) {
          $valid = FALSE;
        }
      }

      if (!$element->validateField()) {
        $valid = FALSE;
      }
    }

    return $valid;
	}

  protected function validateConfig() {
    if (!$this->configValidated) {
      $this->configValidated = TRUE;

      if ($this->attributes['method'] == NULL) {
        $this->attributes['method'] = self::FORM_ELEMENT_METHOD_POST;
      }

      $this->validateConfigMethod();
      $this->validateConfigDir();

      if ($this->attributes['action'] == NULL) {
        $this->attributes['action'] = $_SERVER['PHP_SELF'];
      }

      if ($this->attributes['name'] == NULL) {
        $this->errors[] = "Config Error: Attribute 'name' is required";
      }

      if ($this->attributes['id'] == NULL) {
        $this->attributes['id'] = $this->attributes['name'];
      }

      Helper::addForm($this->attributes['id']);

      foreach ($this->elements as $form_element) {
        $form_element->validateConfig();
      }
    }
  }

}