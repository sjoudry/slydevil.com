<?php

namespace SlyDevil\Form\Element;

use SlyDevil\Form\LabelledElementBase;

class Textarea extends LabelledElementBase {

  protected ?string $content = NULL;

  public static function create(string $name, string $content): self {
    return new static($name, $content);
  }

  public function __construct(string $name, string $content) {
    parent::__construct($name);

    $this->elementType = 'textarea';
    $this->content = $content;

    return $this;
  }

  public function getContent(): ?string {
    return $this->content;
  }

  public function render(): string {
    $output = '';

    $name = $this->getAttribute('name');
    if (!empty($_REQUEST[$name])) {
      $this->content = $this->sessionManager->filterVariable($_REQUEST[$name]);
    }

    $output .= $this->renderElementTop();
    $output .= '<textarea';
    $output .= $this->renderElementAttributes();
    $output .= '/>' . $this->content . '</textarea>';
    $output .= $this->renderElementBottom();

    return $output;
  }

  public function setContent(string $content): self {
    $this->content = $content;

    return $this;
  }

}