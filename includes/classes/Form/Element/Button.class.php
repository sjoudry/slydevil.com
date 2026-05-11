<?php

namespace SlyDevil\Form\Element;

class Button extends Base {

  protected array $availableAttributes = [
    'accesskey',
    'class',
    'dir',
    'disabled',
    'id',
    'lang',
    'name',
    'style',
    'tabindex',
    'title',
    'type',
    'value',
  ];

  protected array $availableEvents = [
    'blur',
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
  ];

  public function __construct() {
    $this->elementType = 'button';

    return $this;
  }

  public function returnHTML() {
    $output = '';

    $output .= $this->returnHTMLPreText();
    $output .= $this->returnHTMLDivBegin();
    $output .= $this->returnHTMLLabelLeft($this);
    $output .= '<button';
    $output .= $this->returnHTMLAttributes();
    $output .= '/>' . $this->attributes['value'] . '</button>';
    $output .= $this->returnHTMLLabelRight($this);
    $output .= $this->returnHTMLDescription();
    $output .= $this->returnHTMLDivEnd();
    $output .= $this->returnHTMLPostText();

    return $output;
  }

  protected function validateConfig() {
		if ($this->configValidated) {
			$this->configValidated = TRUE;

      $this->validateConfigAccesskey();
      $this->validateConfigDir();
      $this->validateConfigDisabled();
      $this->validateConfigTabindex();

      if ($this->getType() == NULL) {
          $this->setType(self::FORM_ELEMENT_TYPE_SUBMIT);
      }
      else {
        if (
          $this->getType() != self::FORM_ELEMENT_TYPE_BUTTON &&
          $this->getType() != self::FORM_ELEMENT_TYPE_RESET &&
          $this->getType() != self::FORM_ELEMENT_TYPE_SUBMIT
        ) {
          $this->setType(self::FORM_ELEMENT_TYPE_SUBMIT);
        }
      }

      if ($this->getName() == NULL) {
        $this->errors[] = "Config Error: Attribute 'name' is required";
      }

      if ($this->getValue() == NULL) {
        $this->errors[] = "Config Error: Attribute 'value' is missing and is required";
      }

      if ($this->getId() == NULL) {
        $this->setId($this->getName());
      }
    }
  }

}