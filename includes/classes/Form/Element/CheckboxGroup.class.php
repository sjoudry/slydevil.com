<?php

namespace SlyDevil\Form\Element;

class CheckboxGroup extends Base {
    
  protected array $availableAttributes = [
    'id',
    'name',
  ];

  protected ?Base $defaultElement = NULL;

  public function __construct() {
    $this->elementType = 'checkbox-group';

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

		$hidden = Hidden::create()
		  ->setName($this->getId())
		  ->setId($this->getId())
		  ->setValue(1);

		$output .= $hidden->returnHTML();

    foreach ($this->elements as $form_element) {
      if (isset($_REQUEST[$form_element->getName()])) {
				foreach ($_REQUEST[$form_element->getName()] as $value) {
					if ($value == $form_element->getValue()) {
						$form_element->setChecked(self::FORM_ELEMENT_CHECKED);
						break;
					}
				}
      }
      else {
        if ($form_element == $this->defaultElement) {
          $form_element->setChecked(self::FORM_ELEMENT_CHECKED);
        }
      }

			$form_element->setName($form_element->getName() . '[]');

      $output .= $form_element->returnHTML();
    }

    $output .= $this->returnHTMLRequired();
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
        $element->setId($element->getId() . '-' . $element->getValue());
      }
    }
  }

}