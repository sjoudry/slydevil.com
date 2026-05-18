<?php

namespace SlyDevil\Form\Element;

use SlyDevil\Form\GroupElementBase;

class Group extends GroupElementBase {

  public static function create(string $type, string $name): self {
    return new static($type, $name);
  }

  public function __construct(string $type, string $name) {
    parent::__construct($name);
    if ($type != 'checkbox' && $type != 'radio') {
      $type = 'checkbox';
    }
    $this->elementSubType = $type;
  }

}