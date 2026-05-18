<?php

namespace SlyDevil\Form;

use SlyDevil\Form\Element\Input;

abstract class GroupElementBase extends LabelledElementBase implements GroupElementInterface {

  protected ?ElementInterface $defaultElement = NULL;

  /**
   * Elements in the group.
   *
   * @var \SlyDevil\Form\ElementInterface[]
   */
  protected array $elements = [];

  protected ?string $elementType = 'group';

  protected ?string $elementSubType = NULL;

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

  public function getElementSubType(): ?string {
    return $this->elementSubType;
  }

  public function render(): string {
    $output = '';

    $output .= $this->renderElementTop();

    if ($this->elementSubType == 'checkbox') {
      $output .= Input::create('hidden', $this->id)
        ->setAttribute('value', 1)
        ->render();
    }

    foreach ($this->elements as $element) {
      if (isset($_REQUEST[$element->getAttribute('name')])) {

        if ($this->elementSubType == 'checkbox') {
          foreach ($_REQUEST[$element->getAttribute('name')] as $value) {
            if ($value == $element->getAttribute('value')) {
              $element->setAttribute('checked');
              break;
            }
          }
        }
        else {
          if ($_REQUEST[$element->getAttribute('name')] == $element->getAttribute('value')) {
            $element->setAttribute('checked');
          }
        }
      }
      else {
        if ($element == $this->defaultElement) {
          $element->setAttribute('checked');
        }
      }

      if ($this->elementSubType == 'checkbox') {
			  $element->setAttribute('name', $element->getAttribute('name') . '[]');
      }

      $output .= $element->render();
    }

    $output .= $this->renderElementBottom();

    return $output;
  }

  public function setDefault(ElementInterface $element): static {
    $this->defaultElement = $element;

    return $this;
  }

}