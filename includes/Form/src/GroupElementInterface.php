<?php

namespace SlyDevil\Form;

interface GroupElementInterface extends ElementInterface {

  public function addElement(ElementInterface $element): GroupElementInterface;

  public function deleteElement(string $id): GroupElementInterface;

  public function getElements(): array;

  public function getElementSubType(): ?string;

  public function setDefault(ElementInterface $element): GroupElementInterface;

}