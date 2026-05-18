<?php

namespace SlyDevil\Form;

interface LabelledElementInterface extends ElementInterface {

  public const FORM_ELEMENT_LABEL_ALIGN_LEFT = 'left';

  public const FORM_ELEMENT_LABEL_ALIGN_RIGHT = 'right';

  public function addLabel(string $text, string $label_align = self::FORM_ELEMENT_LABEL_ALIGN_LEFT): ElementInterface;
 
  public function getLabel(): ?ElementInterface;

  public function getLabelAlign(): string;

}