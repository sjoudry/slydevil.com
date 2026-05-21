<?php

namespace SlyDevil\Form\Element;

use SlyDevil\Form\LabelledElementBase;

class Select extends LabelledElementBase {

  protected array $options = [];

  public static function create(string $name): self {
    return new static($name);
  }

  public function __construct(string $name) {
    parent::__construct($name);

    $this->elementType = 'select';

    return $this;
  }

  public function getOptions(): array {
    return $this->options;
  }

  public function render(): string {
    if ($this->hasAttribute('multiple')) {
      $this->setAttribute('name', $this->getAttribute('name') . '[]');
    }

    $output = '';

    $output .= $this->renderElementTop();
    $output .= '<select';
    $output .= $this->renderElementAttributes();
    $output .= '/>';

    $output .= $this->renderOptions();

    $output .= '</select>';
    $output .= $this->renderElementBottom();

    return $output;
  }

  public function setOptions(array $options): self {
    $this->options = $options;
    return $this;
  }

  protected function renderOptions(array $options = []): string {
    $output = '';

    $name = $this->getAttribute('name');
    $selected = NULL;
    if (!empty($_REQUEST[$name])) {
      if (is_array($_REQUEST[$name])) {
        $selected = [];
        foreach ($_REQUEST[$name] as $value) {
          $selected[] = $this->sessionManager->filterVariable($value);
        }
      }
      else {
        $selected = $this->sessionManager->filterVariable($_REQUEST[$name]);
      }
    }

    $options = empty($options) ? $this->options : $options;
    foreach ($options as $value => $label) {
      if (is_array($label)) {
        $output .= "<optgroup label='" . $value . "'>";
        $output .= $this->renderOptions($label);
      }
      else {
        $output .= "<option value='" . $value . "'";

        if (!empty($selected)) {
          if (is_array($selected)) {
            foreach ($selected as $selected_value) {
              if ($selected_value == $value) {
                $output .= ' selected="selected"';
              }
            }
          }
          else {
            if ($selected == $value) {
              $output .= ' selected="selected"';
            }
          }
        }

        $output .= ">" . $label . "</option>";
      }
    }

    return $output;
  }

}