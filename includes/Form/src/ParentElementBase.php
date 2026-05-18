<?php

namespace SlyDevil\Form;

abstract class ParentElementBase extends ElementBase implements ParentElementInterface {

  protected array $elements = [];

  public function addElement(ElementInterface $element): static {
    $this->elements[$element->getId()] = $element;

    return $this;
  }

  public function deleteElement(string $id): static {
    if (isset($this->elements[$id])) {
      unset($this->elements[$id]);
    }

    return $this;
  }

  public function getElements(): array {
    return $this->elements;
  }

}