<?php

namespace SlyDevil\Form;

interface ElementInterface {

  public function addValidator(string $type, ?string $error_override = NULL, mixed ...$args): ElementInterface;

  public function getAttribute(string $name): mixed;

  public function getAttributes(): array;

  public function getDescription(): ?string;

  public function getElementType(): ?string;

  public function getId();

  public function getLabelText(): string;

  public function getPostText(): ?string;

  public function getPreText(): ?string;

  public function getRequired(): bool;

  public function render();

  public function setAttribute(string $name, mixed $value = NULL): ElementInterface;

  public function setAttributes(array $value): ElementInterface;

  public function setDescription(string $value): ElementInterface;

  public function setPostText(string $value): ElementInterface;

  public function setPreText(string $value): ElementInterface;

  public function setRequired(bool $value): ElementInterface;

}