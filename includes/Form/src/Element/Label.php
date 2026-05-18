<?php

namespace SlyDevil\Form\Element;

use SlyDevil\Form\ElementBase;

class Label extends ElementBase {

  protected ?string $text = NULL;

  public static function create(string $text): self {
    return new static($text);
  }

  public function __construct(string $text) {
    parent::__construct('label');
    $this->text = $text;

    $this->elementType = 'label';
  }

  public function getText(): ?string {
    return $this->text;
  }

  public function render() {
    return '<label' . $this->renderElementAttributes() . '>' . ($this->text ?? '') . '</label>';
  }

  public function setText(string $text): self {
    $this->text = $text;

    return $this;
  }
 
}