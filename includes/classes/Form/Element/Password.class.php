<?php

namespace SlyDevil\Form\Element;

use SlyDevil\Env;

class Password extends Base {

  protected array $availableAttributes = [
    'accesskey',
    'class',
    'dir',
    'disabled',
    'id',
    'lang',
    'maxlength',
    'name',
    'readonly',
    'size',
    'style',
    'tabindex',
    'title',
    'value',
  ];

  protected array $availableEvents = [
    'blur',
    'change',
    'click',
    'dblclick',
    'focus',
    'keydown',
    'keypress',
    'keyup',
    'mousedown',
    'mousemove',
    'mouseout',
    'mouseover',
    'mouseup',
    'select',
  ];

  public function __construct() {
    $this->elementType = 'password';

    return $this;
  }

  public function returnHTML() {
    $output = '';

    $output .= $this->returnHTMLPreText();
    $output .= $this->returnHTMLDivBegin();
    $output .= $this->returnHTMLLabelLeft($this);
    $output .= "<input type='password'";
    $output .= $this->returnHTMLAttributes();
    $output .= '/>';
    $output .= $this->returnHTMLRequired();
    $output .= $this->returnHTMLLabelRight($this);
    $output .= $this->returnHTMLDescription();
    $output .= $this->returnHTMLDivEnd();
    $output .= $this->returnHTMLPostText();
    $output .= $this->returnHTMLErrorsInline();

    return $output;
  }

  protected function validateConfig() {
		if (!$this->configValidated) {
			$this->configValidated = TRUE;

      $this->validateConfigAccesskey();
      $this->validateConfigDir();
      $this->validateConfigDisabled();
      $this->validateConfigMaxlength();
      $this->validateConfigReadonly();
      $this->validateConfigSize();
      $this->validateConfigTabindex();

		  $name_valid = TRUE;
      if ($this->getName() == NULL) {
        $this->errors[] = "Config Error: Attribute 'name' is required";
        $name_valid = FALSE;
      }

      if ($name_valid) {
        if (isset($_REQUEST[$this->getName()])) {
          $this->setValue(Env::filterVariable($_REQUEST[$this->getName()]));
        }
      }

      if ($this->getId() == NULL) {
        $this->setId($this->getName());
      }
    }
  }

}