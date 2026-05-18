<?php

namespace SlyDevil\Form\Utility;

class ErrorHandler {

  protected array $errors = [];

  protected ?ValidatorManager $validatorManager = NULL;

  public function __construct() {
    $this->validatorManager = new ValidatorManager();
  }

  public function addError(string $error) {
    $this->errors[] = $error;
  }

  public function getErrors(): array {
    return $this->errors;
  }

  public function renderErrors(string $form_id): string {
    $output = '';

    if (count($this->errors)) {
      $output .= '<div id="form-error-group-' . $form_id . '" class="form-error-group">';

      foreach ($this->errors as $error) {
        $output .= '<div class="form-individual-error">' . $error . '</div>';
      }

      $output .= '</div>';
    }

    return $output;
  }

  public function renderElementErrorsInline(string $name): string {
    $output = '';

    $validators = $this->validatorManager->getValidatorErrors($name);
    if (!empty($validators)) {
      $output .= '<div id="form-error-group-' . $name . '" class="form-error-group" data-visible="0">';
      foreach ($validators as $validator => $error_groups) {
        foreach ($error_groups as $group => $error) {
          $output .= '<div id="form-individual-error-' . $validator . '-' . $group . '-' . $name . '" class="form-individual-error" data-visible="0">';
          $output .= $error;
          $output .= '</div>';
        }
      }
      $output .= '</div>';
    }

    return $output;
  }

}