<?php

namespace SlyDevil\Form\Element;

class Checkbox extends Base {

  protected array $availableAttributes = [
    'accesskey',
    'checked',
    'class',
    'dir',
    'disabled',
    'id',
    'lang',
    'name',
    'readonly',
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
    $this->elementType = 'checkbox';

    return $this;
  }

  public function returnHTML() {
    $output = '';

    $output .= $this->returnHTMLPreText();
    $output .= $this->returnHTMLDivBegin();
    $output .= $this->returnHTMLLabelLeft($this);
    $output .= "<input type='checkbox'";
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
      $this->validateConfigChecked();
      $this->validateConfigDir();
      $this->validateConfigDisabled();
      $this->validateConfigReadonly();
      $this->validateConfigTabindex();

      if ($this->getName() == NULL) {
        $this->errors[] = "Config Error: Attribute 'name' is required";
      }
      else if (isset($_REQUEST[$this->getName()]) && $_REQUEST[$this->getName()] == $this->getValue()) {
        $this->setChecked(self::FORM_ELEMENT_CHECKED);
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