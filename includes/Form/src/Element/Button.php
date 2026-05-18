<?php

namespace SlyDevil\Form\Element;

use SlyDevil\Form\ElementBase;

class Button extends ElementBase {

  public const TYPE_BUTTON = 'button';

  public const TYPE_RESET = 'reset';

  public const TYPE_SUBMIT = 'submit';

  protected string $buttonType = self::TYPE_BUTTON;

  protected ?string $buttonValue = NULL;

  public static function create(string $name, string $value, string $type = self::TYPE_BUTTON): self {
    return new static($name, $value, $type);
  }

  public function __construct(string $name, string $value, string $type = self::TYPE_BUTTON) {
    parent::__construct($name);

    $this->elementType = 'button';
    $this->buttonValue = $value;
    $this->setButtonType($type);
  }

  public function getButtonType(): string {
    return $this->buttonType;
  }

  public function getButtonValue(): string {
    return $this->buttonValue;
  }

  public function setButtonType(string $type = self::TYPE_BUTTON): self {
    if ($type != self::TYPE_BUTTON && $type != self::TYPE_RESET && $type != self::TYPE_SUBMIT) {
      $type = self::TYPE_BUTTON;
    }
    $this->buttonType = $type;

    return $this;
  }

  public function setButtonValue(string $value): self {
    $this->buttonValue = $value;

    return $this;
  }

  public function render(): string {
    return $this->renderElementTop() .
      '<button type="' . $this->buttonType . '"' . $this->renderElementAttributes(['type']) . '/>' . $this->buttonValue . '</button>' .
      $this->renderElementBottom();
  }

}