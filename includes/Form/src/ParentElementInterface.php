<?php

namespace SlyDevil\Form;

interface ParentElementInterface extends ElementInterface {

  public function addElement(ElementInterface $element): ParentElementInterface;

  public function deleteElement(string $id): ParentElementInterface;

  public function getElements(): array;

}