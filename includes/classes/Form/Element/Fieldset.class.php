<?php

namespace SlyDevil\Form\Element;

use SlyDevil\Form\Helper;

class Fieldset extends Base {

  protected array $available_attributes = [
    'accesskey',
    'class',
    'dir',
    'id',
    'lang',
    'style',
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
    $this->elementType = 'fieldset';

    return $this;
  }

  public function returnHTML() {
    $output = '';

    if ($this->collapsible) {
      $hidden = Hidden::create()
        ->setName('FIELDSET-' . $this->getId() . '-COLLAPSED')
        ->setId('FIELDSET-' . $this->getId() . '-COLLAPSED');

      if (isset($_REQUEST['FIELDSET-' . $this->getId() . '-COLLAPSED'])) {
        if ($_REQUEST['FIELDSET-' . $this->getId() . '-COLLAPSED'] == '1') {
          $hidden->setValue('1');
          $this->collapsed = TRUE;
        }
        else {
          $hidden->setValue('0');
          $this->collapsed = FALSE;
        }
      }
      else {
        $hidden->setValue($this->collapsed ? '1' : '0');
      }

      $output .= $hidden->returnHTML();
    }

    $output .= $this->returnHTMLPreText();
    $output .= $this->returnHTMLDivBegin();
    $output .= '<fieldset';
    $output .= $this->returnHTMLAttributes();
    $output .= '/>';

    $output .= '<legend>';
    if ($this->collapsible) {
      $output .= "<a href='' id='legend-" . $this->getId() . "'>";
    }
    $output .= $this->legend;
    if ($this->collapsible) {
      $output .= '</a>';
    }
    $output .= '</legend>';

    foreach ($this->elements as $form_element) {
      $output .= $form_element->returnHTML();
    }

    $output .= '</fieldset>';
    $output .= $this->returnHTMLDivEnd();
    $output .= $this->returnHTMLPostText();
    $output .= $this->returnHTMLDescription();

    return $output;
  }

  protected function validateConfig() {
		if (!$this->configValidated) {
			$this->configValidated = TRUE;

      $this->validateConfigAccesskey();
      $this->validateConfigDir();

      if ($this->collapsible) {
        if ($this->getClass() == NULL) {
          $this->setClass('');
        }

        if (strlen($this->getClass()) > 0) {
          $this->setClass($this->getClass() . ' ');
        }

			  if (isset($_REQUEST['FIELDSET-' . $this->getId() . '-COLLAPSED'])) {
          $this->collapsed = ($_REQUEST['FIELDSET-' . $this->getId() . '-COLLAPSED'] == '1');
			  }

        $this->setClass($this->getClass() . ($this->collapsed ? 'fieldset_collapsed' : 'fieldset_expanded'));

        //bind legend event
        $legend_events = array('click' => "FormFieldset_collapse('" . $this->getId() . "');");
        Helper::bindEvents('legend-' . $this->getId(), $legend_events, FALSE);

        //bind collapse function
        if ($this->collapsible && $this->collapsed) {
          Helper::bindJavascript("FormFieldset_collapse('" . $this->getId() . "');");
        }
      }

      if ($this->getId() == NULL) {
        $this->errors[] = "Config Error: Attribute 'id' is missing and is required";
      }

      foreach ($this->elements as $element) {
        $element->validateConfig();
      }
    }
  }

}