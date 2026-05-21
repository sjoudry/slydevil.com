<?php

namespace SlyDevil\Form\Element;

use SlyDevil\Form\LabelledElementBase;

class Input extends LabelledElementBase {

  protected ?string $elementSubType = NULL;

  public static function create(string $type, string $name): self {
    return new static($type, $name);
  }

  public function __construct(string $type, string $name) {
    parent::__construct($name);

    $this->elementType = 'input';
    $this->elementSubType = $type;
  }

  public function getElementSubType(): ?string {
    return $this->elementSubType;
  }

  public function render(): string {
    $name = $this->getAttribute('name');

    if (!empty($_REQUEST[$name])) {
      switch ($this->elementSubType) {
        case 'checkbox':
        case 'radio':
          if ($_REQUEST[$name] == ($this->getAttribute('value') ?? 'on')) {
            $this->setAttribute('checked', TRUE);
          }
          break;

        case 'password':
          break;

        default:
          $this->setAttribute('value', $this->sessionManager->filterVariable($_REQUEST[$name]));
      }
    }

    return $this->renderElementTop() .
      '<input type="' . $this->elementSubType . '"' . $this->renderElementAttributes() . '/>' .
      $this->renderElementBottom();
  }

  protected function renderElementDivBegin(): string {
    $classes = [
      'form-element',
      'form-element-' . $this->elementType,
      'form-element-' . $this->elementType . '-' . $this->elementSubType,
    ];
    return '<div id="form-element-' . $this->id . '" class="' . implode(' ', $classes) . '">';
  }

}