<?php

namespace SlyDevil\Form\Utility;

class ErrorHandler {

  public const SESSION_KEY_ERRORS = 'errors';

  protected ?ValidatorManager $validatorManager = NULL;

  public function __construct() {
    $this->validatorManager = new ValidatorManager();

    if (empty($_SESSION[self::SESSION_KEY_ERRORS])) {
      $_SESSION[self::SESSION_KEY_ERRORS] = [];
    }
  }

  public function addError(string $error) {
    $_SESSION[self::SESSION_KEY_ERRORS][] = $error;
  }

  public function getErrors(): array {
    $errors = $_SESSION[self::SESSION_KEY_ERRORS];
    $_SESSION[self::SESSION_KEY_ERRORS] = [];

    return $errors;
  }

  public function renderErrors(string $form_id): string {
    $output = '';

    $errors = $this->getErrors();
    if (count($errors)) {
      $output .= '<div id="form-error-group-' . $form_id . '" class="form-error-group">';

      foreach ($errors as $error) {
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
      $output .= '<div id="form-error-group-' . $name . '" class="form-error-group form-error-group-inline" data-visible="0">';
      foreach ($validators as $validator => $error_groups) {
        foreach ($error_groups as $group => $error) {
          $output .= '<div id="form-individual-error-' . $validator . '-' . $group . '-' . $name . '" class="form-individual-error form-individual-error-inline" data-visible="0">';
          $output .= $error;
          $output .= '</div>';
        }
      }
      $output .= '</div>';
    }

    return $output;
  }

}