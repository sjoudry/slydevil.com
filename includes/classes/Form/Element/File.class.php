<?php

namespace SlyDevil\Form\Element;

class File extends Base {

  protected array $availableAttributes = [
    'accesskey',
    'class',
    'dir',
    'disabled',
    'id',
    'lang',
    'name',
    'readonly',
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
    $this->elementType = 'file';

    return $this;
  }

  public function returnHTML() {
    $output  = '';

    $output .= $this->returnHTMLPreText();
    $output .= $this->returnHTMLDivBegin();
    $output .= $this->returnHTMLLabelLeft($this);
    $output .= "<input type='file'";
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
      $this->validateConfigReadonly();
      $this->validateConfigTabindex();

      if ($this->getName() == NULL) {
          $this->errors[] = "Config Error: Attribute 'name' is required";
      }

      if ($this->getId() == NULL) {
          $this->setId($this->getName());
      }
    }
  }
}