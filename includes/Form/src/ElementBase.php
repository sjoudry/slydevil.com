<?php

namespace SlyDevil\Form;

use SlyDevil\Form\Utility\AssetManager;
use SlyDevil\Form\Utility\ErrorHandler;
use SlyDevil\Form\Utility\SessionManager;
use SlyDevil\Form\Utility\ValidatorManager;

abstract class ElementBase implements ElementInterface {

  protected ?AssetManager $assetManager = NULL;

  protected array $attributes = [];

  protected ?string $description = NULL;

  protected ?string $elementType = NULL;

  protected ?ErrorHandler $errorHandler = NULL;

  protected ?string $id = NULL;

  protected ?string $postText = NULL;

  protected ?string $preText = NULL;

  protected bool $required = FALSE;

  protected ?SessionManager $sessionManager = NULL;

  protected ?ValidatorManager $validatorManager = NULL;

  public function __construct(string $name) {
    $this->setAttribute('name', $name);
    $this->id = $name;

    $this->assetManager = new AssetManager();
    $this->errorHandler = new ErrorHandler();
    $this->sessionManager = new SessionManager();
    $this->validatorManager = new ValidatorManager();
  }

  public abstract function render();

  public function addValidator(string $type, ?string $error_override = NULL, mixed ...$args): static {
    $this->validatorManager->addValidator($this, $type, $error_override, ...$args);

    return $this;
  }

  public function deleteAttribute(string $name): static {
    if (isset($this->attributes[$name])) {
      unset($this->attributes[$name]);
    }

    return $this;
  }

  public function getAttribute(string $name): mixed {
    return $this->attributes[$name] ?? NULL;
  }

  public function getAttributes(): array {
    return $this->attributes;
  }

  public function getDescription(): ?string {
    return $this->description;
  }

  public function getElementType(): ?string {
    return $this->elementType;
  }

  public function getId() {
    return $this->id;
  }

  public function getLabelText(): string {
    return $this->attributes['name'];
  }

  public function getPostText(): ?string {
    return $this->postText;
  }

  public function getPreText(): ?string {
    return $this->preText;
  }

  public function getRequired(): bool {
    return $this->required;
  }

  public function hasAttribute(string $name): bool {
    return isset($this->attributes[$name]);
  }

  public function removeValidator(string $type, mixed ...$args): static {
    $this->validatorManager->removeValidator($this, $type, ...$args);

    return $this;
  }

  public function setAttribute(string $name, mixed $value = NULL): static {
    $this->attributes[$name] = $value;
    return $this;
  }

  public function setAttributes(array $value): static {
    $this->attributes = $value;

    return $this;
  }

  public function setDescription(string $value): static {
    $this->description = $value;

    return $this;
  }

  public function setPostText(string $value): static {
    $this->postText = $value;

    return $this;
  }

  public function setPreText(string $value): static {
    $this->preText = $value;

    return $this;
  }

  public function setRequired(bool $value): static {
    $this->required = $value;
    $this->setAttribute('required');
    $this->addValidator('existance');

    return $this;
  }

  protected function renderElementAttributes(array $exclude = []): string {
    $output = '';

    if (empty($this->attributes['id'])) {
      $this->attributes['id'] = $this->id;
    }

    foreach ($this->attributes as $name => $value) {
      if (!in_array($name, $exclude)) {
        $output .= ' ' . $name;
        if ($value !== NULL) {
          $output .= '="' . trim($value) . '"';
        }
      }
    }

    return $output;
  }

  protected function renderElementBottom(): string {
    return $this->renderElementRequired() .
      $this->renderElementDescription() .
      $this->renderElementDivEnd() .
      $this->renderElementPostText() .
      $this->errorHandler->renderElementErrorsInline($this->id);
  }

  protected function renderElementDescription(): string {
    $output = '';

    if (!empty($this->description)) {
      $output .= '<div id="form-element-description-' . $this->id . '" class="form-element-description">';
      $output .= $this->description;
      $output .= '</div>';
    }

    return $output;
  }

  protected function renderElementDivBegin(): string {
    $classes = [
      'form-element',
      'form-element-' . $this->elementType,
    ];
    return '<div id="form-element-' . $this->id . '" class="' . implode(' ', $classes) . '">';
  }

  protected function renderElementDivEnd(): string {
    return '</div>';
  }

  protected function renderElementPostText(): string {
    return $this->postText ?? '';
  }

  protected function renderElementPreText(): string {
    return $this->preText ?? '';
  }

  protected function renderElementRequired(): string {
    return $this->required ? '<span class="form-element-required">*</span>' : '';
  }
 
  protected function renderElementTop(): string {
    return $this->renderElementPreText() . 
      $this->renderElementDivBegin();
  }

}