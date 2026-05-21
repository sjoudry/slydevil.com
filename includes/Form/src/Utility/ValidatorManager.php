<?php

namespace SlyDevil\Form\Utility;

use SlyDevil\Form\ElementInterface;

class ValidatorManager {

  public const CONTAINS_LOWERCASE = 'contains_lowercase';

  public const CONTAINS_LOWERCASE_PATTERN = '/[a-z]/';

  public const CONTAINS_NUMBER = 'contains_number';

  public const CONTAINS_NUMBER_PATTERN = '/[0-9]/';

  public const CONTAINS_UPPERCASE = 'contains_uppercase';

  public const CONTAINS_UPPERCASE_PATTERN = '/[A-Z]/';

  public const CUSTOM = 'custom';

  public const EMAIL = 'email';

  public const EMAIL_PATTERN = '/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/';

  public const EXISTANCE = 'existance';

  public const FIELD_MATCHES = TRUE;

  public const FIELD_NOT_MATCHES = FALSE;

  public const FILE_TYPE = 'file_type';

  public const LENGTH_LONG = 'length_long';

  public const LENGTH_SHORT = 'length_short';

  public const MATCH = 'match';

  public const MAXIMUM_CHECKED = 'maximum_checked';

  public const MAXIMUM_SELECTED = 'maximum_selected';

  public const MINIMUM_CHECKED = 'minimum_checked';

  public const MINIMUM_SELECTED = 'minimum_selected';

  public const NUMERIC = 'numeric';

  public const NUMERIC_PATTERN = '/[^\-0-9\.]/';

  public const PATTERN = 'pattern';

  public const PATTERN_MATCHES = TRUE;

  public const PATTERN_NOT_MATCHES = FALSE;

  protected ?AssetManager $assetManager = NULL;

  protected ?SessionManager $sessionManager = NULL;

  protected static array $validators = [];

  public function __construct() {
    $this->assetManager = new AssetManager();
    $this->sessionManager = new SessionManager();
  }

  public function addValidator(ElementInterface $element, string $type = self::EXISTANCE, ?string $error_override = NULL, mixed ...$args) {
    match($type) {
      self::CONTAINS_LOWERCASE => $this->addValidatorContainsLowercase($element, $error_override),
      self::CONTAINS_NUMBER => $this->addValidatorContainsNumber($element, $error_override),
      self::CONTAINS_UPPERCASE => $this->addValidatorContainsUppercase($element, $error_override),
      self::CUSTOM => $this->addValidatorCustom($element, $args[0], $args[1]),
      self::EMAIL => $this->addValidatorEmail($element, $error_override),
      self::EXISTANCE => $this->addValidatorExistance($element, $error_override),
      self::FILE_TYPE => $this->addValidatorFileTypes($element, $args[0], $error_override),
      self::LENGTH_LONG => $this->addValidatorLengthLong($element, $args[0], $error_override),
      self::LENGTH_SHORT => $this->addValidatorLengthShort($element, $args[0], $error_override),
      self::MATCH => $this->addValidatorMatch($element, $args[0], $error_override, $args[1] ?? self::FIELD_MATCHES),
      self::MAXIMUM_CHECKED => $this->addValidatorMaximumChecked($element, $args[0] ?? 1, $error_override),
      self::MAXIMUM_SELECTED => $this->addValidatorMaximumSelected($element, $args[0] ?? 1, $error_override),
      self::MINIMUM_CHECKED => $this->addValidatorMinimumChecked($element, $args[0] ?? 1, $error_override),
      self::MINIMUM_SELECTED => $this->addValidatorMinimumSelected($element, $args[0] ?? 1, $error_override),
      self::NUMERIC => $this->addValidatorNumeric($element, $error_override),
      self::PATTERN => $this->addValidatorPattern($element, $args[0], $args[1] ?? 'The field value is not correct.'),
      default => $this->addValidatorExistance($element, $error_override),
    };
  }

  public function getValidatorErrors(string $element_name): array {
    $errors = [];

    if (!empty(self::$validators)) {
      foreach (self::$validators as $type => $names) {
        foreach ($names as $name => $error) {
          if ($element_name == $name) {
            if (is_array($error)) {

              // Array - file_types, lengths, checked, selected.
              if (isset($error['error'])) {
                $errors[$type][] = $error['error'];
              }

              // Array - custom, match, pattern.
              else {
                foreach ($error as $config) {

                  // Array - match, pattern.
                  if (is_array($config)) {
                    $errors[$type][] = $config['error'];
                  }
                }
              }
            }

            // String - existance.
            else {
              $errors[$type][] = $error;
            }
          }
        }
      }
    }

    return $errors;
  }

  public function removeValidator(ElementInterface $element, string $type = self::EXISTANCE, mixed ...$args) {
    match($type) {
      self::CONTAINS_LOWERCASE => $this->removeValidatorContainsLowercase($element),
      self::CONTAINS_NUMBER => $this->removeValidatorContainsNumber($element),
      self::CONTAINS_UPPERCASE => $this->removeValidatorContainsUppercase($element),
      self::CUSTOM => $this->removeValidatorCustom($element, $args[0]),
      self::EMAIL => $this->removeValidatorEmail($element),
      self::EXISTANCE => $this->removeValidatorExistance($element),
      self::FILE_TYPE => $this->removeValidatorFileTypes($element),
      self::LENGTH_LONG => $this->removeValidatorLengthLong($element),
      self::LENGTH_SHORT => $this->removeValidatorLengthShort($element),
      self::MATCH => $this->removeValidatorMatch($element, $args[0]),
      self::MAXIMUM_CHECKED => $this->removeValidatorMaximumChecked($element),
      self::MAXIMUM_SELECTED => $this->removeValidatorMaximumSelected($element),
      self::MINIMUM_CHECKED => $this->removeValidatorMinimumChecked($element),
      self::MINIMUM_SELECTED => $this->removeValidatorMinimumSelected($element),
      self::NUMERIC => $this->removeValidatorNumeric($element),
      self::PATTERN => $this->removeValidatorPattern($element, $args[0]),
      default => $this->removeValidatorExistance($element),
    };
  }

  public function returnSettings(ElementInterface $form): string {
    $output = '';

    if (!empty(self::$validators)) {
      $settings = [];
      foreach (self::$validators as $type => $names) {
        foreach ($names as $name => $configs) {

          // String - existance.
          if ($type == self::EXISTANCE) {
            $settings[$form->getId()][$type][$name] = 1;
          }

          // Array - file_types, lengths, checked, selected.
          if (is_array($configs)) {
            if (!empty($configs['error'])) {
              $settings[$form->getId()][$type][$name] = match($type) {
                self::FILE_TYPE => $configs['file_types'],
                self::LENGTH_LONG => $configs['length'],
                self::LENGTH_SHORT => $configs['length'],
                self::MAXIMUM_CHECKED => $configs['maximum'],
                self::MAXIMUM_SELECTED => $configs['maximum'],
                self::MINIMUM_CHECKED => $configs['minimum'],
                self::MINIMUM_SELECTED => $configs['minimum'],
                default => '',
              };
            }

            // Array - custom, match, pattern.
            else {
              foreach ($configs as $index => $config) {

                // Array - match, pattern.
                if (is_array($config)) {
                  if ($type == 'pattern') {
                    $index = trim($index, '/');
                  }
                  $settings[$form->getId()][$type][$name][$index] = $config['matches'];
                }
              }
            }
          }
        }
      }

      $output = '<script type="application/json" data-form-settings="1">' . json_encode($settings) . '</script>';
    }

    return $output;
  }

  public function validate(ElementInterface $element): array {
    $errors = [];

    foreach (self::$validators as $type => $elements) {
      foreach (array_keys($elements) as $name) {
        if ($name == $element->getAttribute('name')) {
          $validator_errors = match($type) {
            self::CUSTOM => $this->validateValueCustom($name),
            self::EXISTANCE => $this->validateValueExistance($name),
            self::FILE_TYPE => $this->validateValueFileTypes($name),
            self::LENGTH_LONG => $this->validateValueLengthLong($name),
            self::LENGTH_SHORT => $this->validateValueLengthShort($name),
            self::MATCH => $this->validateValueMatch($name),
            self::MAXIMUM_CHECKED => $this->validateValueMaximumChecked($name),
            self::MAXIMUM_SELECTED => $this->validateValueMaximumSelected($name),
            self::MINIMUM_CHECKED => $this->validateValueMinimumChecked($name),
            self::MINIMUM_SELECTED => $this->validateValueMinimumSelected($name),
            self::PATTERN => $this->validateValuePattern($name),
            default => $this->validateValueExistance($name),
          };

          foreach ($validator_errors as $error) {
            $errors[] = $error;
          }
        }
      }
    }

    return $errors;
  }

  protected function addValidatorContainsLowercase(ElementInterface $element, ?string $error_override) {
    $error = $error_override ?? '"%s" must contain at least one lowercase character';
    $this->addValidatorPattern($element, self::CONTAINS_LOWERCASE_PATTERN, sprintf($error, $element->getLabelText()));
  }

  protected function addValidatorContainsNumber(ElementInterface $element, ?string $error_override) {
    $error = $error_override ?? '"%s" must contain at least one number';
    $this->addValidatorPattern($element, self::CONTAINS_NUMBER_PATTERN, sprintf($error, $element->getLabelText()));
  }

  protected function addValidatorContainsUppercase(ElementInterface $element, ?string $error_override) {
    $error = $error_override ?? '"%s" must contain at least one uppercase character';
    $this->addValidatorPattern($element, self::CONTAINS_UPPERCASE_PATTERN, sprintf($error, $element->getLabelText()));
  }

  protected function addValidatorCustom(ElementInterface $element, string $function, string $error) {
    self::$validators[self::CUSTOM][$element->getAttribute('name')][$function] = $error;
  }

  protected function addValidatorEmail(ElementInterface $element, ?string $error_override) {
    $error = $error_override ?? '"%s" is an invalid email address';
    $this->addValidatorPattern($element, self::EMAIL_PATTERN, sprintf($error, $element->getLabelText()));
  }

  protected function addValidatorExistance(ElementInterface $element, ?string $error_override) {
    $error = $error_override ?? '"%s" is missing and is required';
    self::$validators[self::EXISTANCE][$element->getAttribute('name')] = sprintf($error, $element->getLabelText());
  }

  protected function addValidatorFileTypes(ElementInterface $element, array $file_types, ?string $error_override) {
    $error = $error_override ?? '"%s" does not have a valid file type (%s)';
    self::$validators[self::FILE_TYPE][$element->getAttribute('name')] = [
      'file_types' => $file_types,
      'error' => sprintf($error, $element->getLabelText(), implode(', ', $file_types)),
    ];
  }

  protected function addValidatorLengthLong(ElementInterface $element, int $length_long, ?string $error_override) {
    $error = $error_override ?? '"%s" is too long. Length must be less than (or equal to) %s';
    self::$validators[self::LENGTH_LONG][$element->getAttribute('name')] = [
      'length' => $length_long,
      'error' => sprintf($error, $element->getLabelText(), $length_long),
    ];
  }

  protected function addValidatorLengthShort(ElementInterface $element, int $length_short, ?string $error_override) {
    $error = $error_override ?? '"%s" is too short. Length must be greater than (or equal to) %s';
    self::$validators[self::LENGTH_SHORT][$element->getAttribute('name')] = [
      'length' => $length_short,
      'error' => sprintf($error, $element->getLabelText(), $length_short),
    ];
  }

  protected function addValidatorMatch(ElementInterface $element, ElementInterface $compare, ?string $error_override, bool $matches) {
    if ($matches == self::FIELD_MATCHES) {
      $error = $error_override ?? '"%s" must be the same as "%s"';
    }
    else {
      $error = $error_override ?? '"%s" must not be the same as "%s"';
    }
    self::$validators[self::MATCH][$element->getAttribute('name')][$compare->getAttribute('name')] = [
      'matches' => $matches,
      'error' => sprintf($error, $element->getLabelText(), $compare->getLabelText()),
    ];
  }

  protected function addValidatorMaximumChecked(ElementInterface $element, int $maximum, ?string $error_override) {
    $error = $error_override ?? '"%s" has too many checked values. Maximum checked is %s';
    self::$validators[self::MAXIMUM_CHECKED][$element->getAttribute('name')] = [
      'maximum' => $maximum,
      'error' => sprintf($error, $element->getLabelText(), $maximum),
    ];
  }

  protected function addValidatorMaximumSelected(ElementInterface $element, int $maximum, ?string $error_override) {
    $error = $error_override ?? '"%s" has too many selected values. Maximum selected is %s';
    self::$validators[self::MAXIMUM_SELECTED][$element->getAttribute('name')] = [
      'maximum' => $maximum,
      'error' => sprintf($error, $element->getLabelText(), $maximum),
    ];
  }

  protected function addValidatorMinimumChecked(ElementInterface $element, int $minimum, ?string $error_override) {
    $error = $error_override ?? '"%s" does not have enough values checked. Minimum checked is %s';
    self::$validators[self::MINIMUM_CHECKED][$element->getAttribute('name')] = [
      'minimum' => $minimum,
      'error' => sprintf($error, $element->getLabelText(), $minimum),
    ];
  }

  protected function addValidatorMinimumSelected(ElementInterface $element, int $minimum, ?string $error_override) {
    $error = $error_override ?? '"%s" does not have enough values selected. Minimum selected is %s';
    self::$validators[self::MINIMUM_SELECTED][$element->getAttribute('name')] = [
      'minimum' => $minimum,
      'error' => sprintf($error, $element->getLabelText(), $minimum),
    ];
  }

  protected function addValidatorNumeric(ElementInterface $element, ?string $error_override) {
    $error = $error_override ?? '"%s" must be numeric (integer or decimal)';
    $this->addValidatorPattern($element, self::NUMERIC_PATTERN, sprintf($error, $element->getLabelText()), self::PATTERN_NOT_MATCHES);
  }

  protected function addValidatorPattern(ElementInterface $element, string $pattern, string $error, bool $matches = self::PATTERN_MATCHES) {
    if ($matches == self::PATTERN_MATCHES) {
      $bind_match = 'true';
    }
    else {
      $bind_match = 'false';
    }
    self::$validators[self::PATTERN][$element->getAttribute('name')][$pattern] = [
      'error' => $error,
      'matches' => $matches,
    ];
  }

  protected function removeValidatorContainsLowercase(ElementInterface $element) {
    $this->removeValidatorPattern($element, self::CONTAINS_LOWERCASE_PATTERN);
  }

  protected function removeValidatorContainsNumber(ElementInterface $element) {
    $this->removeValidatorPattern($element, self::CONTAINS_NUMBER_PATTERN);
  }

  protected function removeValidatorContainsUppercase(ElementInterface $element) {
    $this->removeValidatorPattern($element, self::CONTAINS_UPPERCASE_PATTERN);
  }

  protected function removeValidatorCustom(ElementInterface $element, string $function) {
    if (!empty(self::$validators[self::CUSTOM][$element->getAttribute('name')][$function])) {
      unset(self::$validators[self::CUSTOM][$element->getAttribute('name')][$function]);
    }
  }

  protected function removeValidatorEmail(ElementInterface $element) {
    $this->removeValidatorPattern($element, self::EMAIL_PATTERN);
  }

  protected function removeValidatorExistance(ElementInterface $element) {
    if (!empty(self::$validators[self::EXISTANCE][$element->getAttribute('name')])) {
      unset(self::$validators[self::EXISTANCE][$element->getAttribute('name')]);
    }
  }

  protected function removeValidatorFileTypes(ElementInterface $element) {
    if (!empty(self::$validators[self::FILE_TYPE][$element->getAttribute('name')])) {
      unset(self::$validators[self::FILE_TYPE][$element->getAttribute('name')]);
    }
  }

  protected function removeValidatorLengthLong(ElementInterface $element) {
    if (!empty(self::$validators[self::LENGTH_LONG][$element->getAttribute('name')])) {
      unset(self::$validators[self::LENGTH_LONG][$element->getAttribute('name')]);
    }
  }

  protected function removeValidatorLengthShort(ElementInterface $element) {
    if (!empty(self::$validators[self::LENGTH_SHORT][$element->getAttribute('name')])) {
      unset(self::$validators[self::LENGTH_SHORT][$element->getAttribute('name')]);
    }
  }

  protected function removeValidatorMatch(ElementInterface $element, string $name) {
    if (!empty(self::$validators[self::MATCH][$element->getAttribute('name')][$name])) {
      unset(self::$validators[self::MATCH][$element->getAttribute('name')][$name]);
    }
  }

  protected function removeValidatorMaximumChecked(ElementInterface $element) {
    if (!empty(self::$validators[self::MAXIMUM_CHECKED][$element->getAttribute('name')])) {
      unset(self::$validators[self::MAXIMUM_CHECKED][$element->getAttribute('name')]);
    }
  }

  protected function removeValidatorMaximumSelected(ElementInterface $element) {
    if (!empty(self::$validators[self::MAXIMUM_SELECTED][$element->getAttribute('name')])) {
      unset(self::$validators[self::MAXIMUM_SELECTED][$element->getAttribute('name')]);
    }
  }

  protected function removeValidatorMinimumChecked(ElementInterface $element) {
    if (!empty(self::$validators[self::MINIMUM_CHECKED][$element->getAttribute('name')])) {
      unset(self::$validators[self::MINIMUM_CHECKED][$element->getAttribute('name')]);
    }
  }

  protected function removeValidatorMinimumSelected(ElementInterface $element) {
    if (!empty(self::$validators[self::MINIMUM_SELECTED][$element->getAttribute('name')])) {
      unset(self::$validators[self::MINIMUM_SELECTED][$element->getAttribute('name')]);
    }
  }

  protected function removeValidatorNumeric(ElementInterface $element) {
    $this->removeValidatorPattern($element, self::NUMERIC_PATTERN);
  }

  protected function removeValidatorPattern(ElementInterface $element, string $pattern) {
    if (!empty(self::$validators[self::PATTERN][$element->getAttribute('name')][$pattern])) {
      unset(self::$validators[self::PATTERN][$element->getAttribute('name')][$pattern]);
    }
  }

  protected function validateValueCustom(string $name): array {
    $errors = [];
    if (!empty(self::$validators[self::CUSTOM][$name]) && $this->validateValueExistance($name, FALSE)) {
      foreach (self::$validators[self::CUSTOM][$name] as $function => $error) {
        if (isset($_REQUEST[$name]) && !call_user_func($function, $this->sessionManager->filterVariable($_REQUEST[$name]))) {
          $errors[] = $error;
        }
      }
    }

    return $errors;
  }

  protected function validateValueExistance(string $name, bool $set_error = TRUE): array|bool {
    if (
      (
        (empty($_REQUEST[$name]) && empty($_FILES[$name])) ||
        (isset($_REQUEST[$name]) && empty($_REQUEST[$name])) ||
        (isset($_FILES[$name]) && empty($_FILES[$name]['name']))
      )
    ) {
      if ($set_error) {
        return [self::$validators[self::EXISTANCE][$name]];
      }

      return FALSE;
    }

    return ($set_error) ? [] : TRUE;
  }

  protected function validateValueFileTypes(string $name): array {
    $errors = [];
    if (!empty(self::$validators[self::FILE_TYPE][$name]) && $this->validateValueExistance($name, FALSE)) {
      foreach (self::$validators[self::FILE_TYPE][$name] as $config) {
        $file_parts = explode('.', basename($_FILES[$name]['name']));
        $file_type = strtolower($file_parts[count($file_parts) - 1]);
        if (!in_array($file_type, $config['file_types'])) {
          $errors[] = $config['error'];
        }
      }
    }

    return $errors;
  }

  protected function validateValueLengthLong(string $name): array {
    $errors = [];
    if (!empty(self::$validators[self::LENGTH_LONG][$name]) && $this->validateValueExistance($name, FALSE)) {
      $config = self::$validators[self::LENGTH_LONG][$name];
      if (strlen($_REQUEST[$name]) > $config['length']) {
        $errors[] = $config['error'];
      }
    }

		return $errors;
  }

  protected function validateValueLengthShort(string $name): array {
    $errors = [];
    if (!empty(self::$validators[self::LENGTH_SHORT][$name]) && $this->validateValueExistance($name, FALSE)) {
      $config = self::$validators[self::LENGTH_SHORT][$name];
      if (strlen($_REQUEST[$name]) < $config['length']) {
        $errors[] = $config['error'];
      }
    }

		return $errors;
  }

  protected function validateValueMatch(string $name): array {
    $errors = [];
    if (!empty(self::$validators[self::MATCH][$name]) && $this->validateValueExistance($name, FALSE)) {
      foreach (self::$validators[self::MATCH][$name] as $compare_name => $config) {
        if ($this->validateValueExistance($compare_name, FALSE)) {
          if ($config['matches'] == self::FIELD_MATCHES) {
            if ($_REQUEST[$name] != $_REQUEST[$compare_name]) {
              $errors[] = $config['error'];
            }
          }
          else {
            if ($_REQUEST[$name] == $_REQUEST[$compare_name]) {
              $errors[] = $config['error'];
            }
          }
        }
      }
    }

    return $errors;
  }

  protected function validateValueMaximumChecked(string $name): array {
    $errors = [];
    if (!empty(self::$validators[self::MAXIMUM_CHECKED][$name]) && $this->validateValueExistance($name, FALSE)) {
      $config = self::$validators[self::MAXIMUM_CHECKED][$name];
      if (count($_REQUEST[$name]) > $config['maximum']) {
        $errors[] = $config['error'];
      }
    }

    return $errors;
  }

  protected function validateValueMaximumSelected(string $name): array {
    $errors = [];
    if (!empty(self::$validators[self::MAXIMUM_SELECTED][$name]) && $this->validateValueExistance($name, FALSE)) {
      $config = self::$validators[self::MAXIMUM_SELECTED][$name];
      if (count($_REQUEST[$name]) > $config['maximum']) {
        $errors[] = $config['error'];
      }
    }

    return $errors;
  }

  protected function validateValueMinimumChecked(string $name): array {
    $errors = [];
    if (!empty(self::$validators[self::MINIMUM_CHECKED][$name]) && $this->validateValueExistance($name, FALSE)) {
      $config = self::$validators[self::MINIMUM_CHECKED][$name];
print $name . "\n";
print_r($_REQUEST);
      if (count($_REQUEST[$name]) < $config['minimum']) {
        $errors[] = $config['error'];
      }
    }

    return $errors;
  }

  protected function validateValueMinimumSelected(string $name): array {
    $errors = [];
    if (!empty(self::$validators[self::MINIMUM_SELECTED][$name]) && $this->validateValueExistance($name, FALSE)) {
      foreach (self::$validators[self::MINIMUM_SELECTED][$name] as $config) {
        if (count($_REQUEST[$name]) < $config['minimum']) {
          $errors[] = $config['error'];
        }
      }
    }

    return $errors;
  }

  protected function validateValuePattern(string $name): array {
    $errors = [];
    if (!empty(self::$validators[self::PATTERN][$name]) && $this->validateValueExistance($name, FALSE)) {
      foreach (self::$validators[self::PATTERN][$name] as $pattern => $config) {
        if ($config['matches'] == self::PATTERN_MATCHES) {
          if (!preg_match($pattern, $_REQUEST[$name])) {
            $errors[] = $config['error'];
          }
        }
        else {
          if (preg_match($pattern, $_REQUEST[$name])) {
            $errors[] = $config['error'];
          }
        }
      }
    }

    return $errors;
  }

}