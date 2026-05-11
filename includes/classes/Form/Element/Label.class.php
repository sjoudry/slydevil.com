<?php

namespace SlyDevil\Form\Element;

class Label extends Base {
    
  protected array $availableAttributes = [
    'accesskey',
    'class',
    'dir',
    'for',
    'id',
    'lang',
    'style',
    'title',
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
    $this->elementType = 'label';

    return $this;
  }

  public function returnHTML() {
    $output = '';

    $output .= '<label';
    $output .= $this->returnHTMLAttributes();
    $output .= '>' . $this->labelText . '</label>';

    return $output;
  }

  protected function validateConfig() {
		if (!$this->configValidated) {
			$this->configValidated = TRUE;

      $this->validateConfigAccesskey();
      $this->validateConfigDir();
    }
  }
 
}