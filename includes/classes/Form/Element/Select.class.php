<?php

namespace SlyDevil\Form\Element;

use SlyDevil\Env;

class Select extends Base {

  protected array $availableAttributes = [
    'class',
    'dir',
    'disabled',
    'id',
    'lang',
    'multiple',
    'name',
    'size',
    'style',
    'tabindex',
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
  ];

  public function __construct() {
    $this->elementType = 'select';

    return $this;
  }

  public function returnHTML() {
    $output = '';

    $output .= $this->returnHTMLPreText();
    $output .= $this->returnHTMLDivBegin();
    $output .= $this->returnHTMLLabelLeft($this);
    $output .= '<select';
    $output .= $this->returnHTMLAttributes();
    $output .= '/>';

    $output .= $this->returnHTMLOptions();

    $output .= '</select>';
    $output .= $this->returnHTMLRequired();
    $output .= $this->returnHTMLLabelRight($this);
    $output .= $this->returnHTMLDivEnd();
    $output .= $this->returnHTMLPostText();
    $output .= $this->returnHTMLDescription();
    $output .= $this->returnHTMLErrorsInline();

    return $output;
  }

  protected function validateConfig() {
		if (!$this->configValidated) {
			$this->configValidated = TRUE;

      $this->validateConfigDir();
      $this->validateConfigDisabled();
      $this->validateConfigMultiple();
      $this->validateConfigSize();
      $this->validateConfigTabindex();

      if ($this->getName() == NULL) {
        $this->errors[] = "Config Error: Attribute 'name' is required";
      }
      elseif (isset($_REQUEST[$this->getName()])) {
        if (gettype($_REQUEST[$this->getName()]) == 'array') {
					$select_options = [];
          foreach ($_REQUEST[$this->getName()] as $selected_value) {
						$select_options[] = Env::filterVariable($selected_value);
          }
          $this->selected = $select_options;
        }
        else {
					$this->selected = Env::filterVariable($_REQUEST[$this->getName()]);
				}
      }

			if ($this->getId() == NULL) {
				$this->setId($this->getName());
			}

			if ($this->getMultiple() == self::FORM_ELEMENT_MULTIPLE) {
        $this->setName($this->getName() . '[]');
			}
    }
  }

}