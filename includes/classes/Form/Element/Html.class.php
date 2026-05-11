<?php

namespace SlyDevil\Form\Element;

class Html extends Base {

  protected array $availableAttributes = [
    'name',
  ];

  public function __construct() {
    $this->elementType = 'html';

    return $this;
  }

  public function returnHTML() {
    $output = '';

    $output .= $this->returnHTMLDivBegin();
    $output .= $this->content;
    $output .= $this->returnHTMLDivEnd();

    return $output;
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
    }
  }

}