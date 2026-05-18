<?php

namespace SlyDevil\Form\Element;

use SlyDevil\Form\ElementBase;

class Html extends ElementBase {

  protected ?string $content = NULL;

  public static function create(string $name, string $content): self {
    return new static($name, $content);
  }

  public function __construct(string $name, string $content) {
    parent::__construct($name);

    $this->elementType = 'html';
    $this->content = $content;

    return $this;
  }

  public function getContent(): ?string {
    return $this->content;
  }

  public function render() {
    $output = '';

    $output .= $this->renderElementDivBegin();
    $output .= $this->content;
    $output .= $this->renderElementDivEnd();

    return $output;
  }

  public function setContent(string $content): self {
    $this->content = $content;

    return $this;
  }

}