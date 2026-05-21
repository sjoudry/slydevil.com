<?php

namespace SlyDevil\Form;

use SlyDevil\Form\Element\Label;

abstract class LabelledElementBase extends ElementBase implements LabelledElementInterface {

  protected ?Label $label = NULL;

  protected string $labelAlign = self::FORM_ELEMENT_LABEL_ALIGN_LEFT;

  public function addLabel(string $text, string $label_align = self::FORM_ELEMENT_LABEL_ALIGN_LEFT): static {
    $this->label = Label::create($text);
    $this->labelAlign = $label_align;

    return $this;
  }

  public function getLabel(): ?Label {
    return $this->label;
  }

  public function getLabelAlign(): string {
    return $this->labelAlign;
  }

  public function getLabelText(): string {
    return $this->label->getText();
  }

  protected function renderElementBottom(): string {
    return $this->renderElementRequired() .
      $this->renderElementLabelRight() .
      $this->renderElementDescription() .
      $this->renderElementDivEnd() .
      $this->renderElementPostText() .
      $this->errorHandler->renderElementErrorsInline($this->id);
  }

  protected function renderElementLabelLeft(): string {
    $output = '';

    if (!empty($this->label) && $this->labelAlign == self::FORM_ELEMENT_LABEL_ALIGN_LEFT) {
      $this->label->setAttribute('for', $this->id);
      $output .= $this->label->render();
    }

    return $output;
  }

  protected function renderElementLabelRight(): string {
    $output = '';

    if (!empty($this->label) && $this->labelAlign == self::FORM_ELEMENT_LABEL_ALIGN_RIGHT) {
      $this->label->setAttribute('for', $this->id);
      $output .= $this->label->render();
    }

    return $output;
  }

  protected function renderElementTop(): string {
    return $this->renderElementPreText() .
      $this->renderElementDivBegin() .
      $this->renderElementLabelLeft();
  }

}