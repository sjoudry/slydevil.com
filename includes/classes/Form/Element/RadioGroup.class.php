<?php

namespace SlyDevil\Form\Element;

class RadioGroup extends Base {

  protected array $availableAttributes = [
    'id',
    'name',
  ];

  protected ?Base $defaultElement = NULL;

  public function __construct() {
    $this->elementType = 'radio-group';

    return $this;
  }

  public function addElement(Base $element) {
		$element->setName($this->getName());
    $this->elements[] = $element;

    return $this;
  }

  public function returnHTML() {
    $output = '';

    $output .= $this->returnHTMLPreText();
    $output .= $this->returnHTMLDivBegin();
    $output .= $this->returnHTMLLabelLeft($this);

    foreach ($this->elements as $form_element) {
      if (isset($_REQUEST[$form_element->getName()])) {
        if ($_REQUEST[$form_element->getName()] == $form_element->getValue()) {
          $form_element->setChecked(self::FORM_ELEMENT_CHECKED);
        }
      }
      else {
        if ($form_element == $this->defaultElement) {
          $form_element->setChecked(self::FORM_ELEMENT_CHECKED);
        }
      }

      $output .= $form_element->returnHTML();
    }

    $output .= $this->returnHTMLLabelRight($this);
    $output .= $this->returnHTMLDescription();
    $output .= $this->returnHTMLDivEnd();
    $output .= $this->returnHTMLPostText();
    $output .= $this->returnHTMLErrorsInline();

    return $output;
  }

  public function setDefault(Base $element) {
    $this->defaultElement = $element;

    return $this;
  }

  protected function validateConfig() {
		if (!$this->configValidated) {
			$this->configValidated = TRUE;

      if ($this->getName() == NULL) {
        $this->errors[] = "Config Error: Attribute 'name' is required";
      }

      if ($this->getId() == NULL) {
        $this->setId($this->getName());
      }

      foreach ($this->elements as $element) {
        $element->validateConfig();
      }
    }
  }

}