<?php

namespace SlyDevil\Form\Element;

use SlyDevil\Env;

class Textarea extends Base {

  protected array $availableAttributes = [
    'class',
    'cols',
    'dir',
    'disabled',
    'id',
    'lang',
    'name',
    'readonly',
    'rows',
    'style',
    'tabindex',
    'title',
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
    $this->elementType = 'textarea';

    return $this;
  }

  public function returnHTML() {
    $output = '';

    $output .= $this->returnHTMLPreText();
    $output .= $this->returnHTMLDivBegin();
    $output .= $this->returnHTMLLabelLeft($this);
    $output .= '<textarea';
    $output .= $this->returnHTMLAttributes();
    $output .= '/>' . $this->content . '</textarea>';
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

      $this->validateConfigCols();
      $this->validateConfigDir();
      $this->validateConfigDisabled();
      $this->validateConfigReadonly();
      $this->validateConfigRows();
      $this->validateConfigTabindex();

		  $name_valid = TRUE;
      if ($this->getName() == NULL) {
        $this->errors[] = "Config Error: Attribute 'name' is required";
        $name_valid = FALSE;
      }

      if ($name_valid) {
        if (isset($_REQUEST[$this->getName()])) {
          $this->content = Env::filterVariable($_REQUEST[$this->getName()]);
        }
      }

      if ($this->getId() == NULL) {
          $this->setId($this->getName());
      }
    }
  }

}