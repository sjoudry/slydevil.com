<?php

namespace SlyDevil;

class Env {

  public static function filterVariable(string $value) {
    return filter_var(htmlspecialchars($value), FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
  }

  public static function loadEnv() {
    $env = file_get_contents(__DIR__ . '/../../.env');
    $env = explode("\n", $env);

    foreach ($env as $line) {
      $line = trim($line);
      if (!str_starts_with($line, '#')) {
        if (preg_match('/^([^=]*)=(.*)$/', $line, $matches)) {
          $variable_name = trim($matches[1]);
          $variable_value = trim(trim($matches[2]), "'\"");
          putenv(implode('=', [$variable_name, $variable_value]));
        }
      }
    }
  }

}