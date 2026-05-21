<?php

namespace SlyDevil\Form;

abstract class ParentElementBase extends ElementBase implements ParentElementInterface {

  protected array $elements = [];

  public function addElement(ElementInterface $element): static {
    $this->elements[] = $element;

    return $this;
  }

  public function deleteElement(string $id): static {
    $new_elements = [];
    foreach ($this->elements as $element) {
      if ($element->getId() != $id) {
        $new_elements[] = $element;
      }
    }
    $this->elements = $new_elements;

    return $this;
  }

  public function getElements(): array {
    return $this->elements;
  }

}