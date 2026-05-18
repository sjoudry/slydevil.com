<?php

namespace SlyDevil\Form\Element;

use SlyDevil\Form\ParentElementBase;

class Fieldset extends ParentElementBase {

  protected bool $collapsed = FALSE;

  protected bool $collapsible = FALSE;

  protected ?string $legend = NULL;

  public static function create(string $name, string $legend): self {
    return new static($name, $legend);
  }

  public function __construct(string $name, string $legend) {
    parent::__construct($name);

    $this->elementType = 'fieldset';
    $this->legend = $legend;
  }

  public function getCollapsed(): bool {
    return $this->collapsed;
  }

  public function getCollapsible(): bool {
    return $this->collapsible;
  }

  public function getLegend(): ?string {
    return $this->legend;
  }

  public function setCollapsed(bool $value): self {
    $this->collapsed = $value;

    return $this;
  }

  public function setCollapsible(bool $value): self {
    $this->collapsible = $value;

    return $this;
  }

  public function setLegend(string $value): self {
    $this->legend = $value;

    return $this;
  }

  public function render(): string {
    $output = '';

    $this->setAttribute('data-collapsible', (int)$this->collapsible);
    $this->setAttribute('data-collapsed', (int)$this->collapsed);
    if ($this->collapsible) {
      $class = $this->getAttribute('class') ?? '';
      $hidden_name = 'FIELDSET-' . $this->id . '-COLLAPSED';
      $hidden = Input::create('hidden', $hidden_name);
      if (isset($_REQUEST[$hidden_name])) {
        if ($_REQUEST[$hidden_name] == '1') {
          $hidden->setAttribute('value', 1);
          $this->collapsed = TRUE;
        }
        else {
          $hidden->setAttribute('value', 0);
          $this->collapsed = FALSE;
        }
      }
      else {
        $hidden->setAttribute('value', $this->collapsed ? 1 : 0);
      }

      $class .= $this->collapsed ? ' fieldset_collapsed' : ' fieldset_expanded';
      $this->setAttribute('class', $class);

      $output = $hidden->render();
    }

    $output .= $this->renderElementTop();
    $output .= '<fieldset' . $this->renderElementAttributes() . '/>';
    $output .= '<legend>';
    if ($this->collapsible) {
      $output .= "<a href='' id='legend-" . $this->id . "'>";
    }
    $output .= $this->legend;
    if ($this->collapsible) {
      $output .= '</a>';
    }
    $output .= '</legend>';

    foreach ($this->elements as $element) {
      $output .= $element->render();
    }

    $output .= '</fieldset>';
    $output .= $this->renderElementBottom();

    return $output;
  }

}