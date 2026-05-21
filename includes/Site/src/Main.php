<?php

namespace SlyDevil\Site;

use SlyDevil\Form\Utility\ErrorHandler;
use SlyDevil\Form\Utility\SessionManager;

class Main {

  public const PASSWORD_SEED = 'SLY-DEVIL-HOST-IS-THE-BEST';

  protected ?Login $login = NULL;

  protected ?Theme $theme = NULL;

  public function __construct() {
    $this->loadEnv();

    $this->login = new Login();
    $this->login->setPasswordSeed(self::PASSWORD_SEED);
    $this->theme = new Theme($this->login);
  }

  public function getErrorHandler(): ErrorHandler {
    return $this->login->getErrorHandler();
  }

  public function getDatabase(): Database {
    return $this->login->getDatabase();
  }

  public function getLogin(): Login {
    return $this->login;
  }

  public function getSessionManager(): SessionManager {
    return $this->login->getSessionManager();
  }

  public function getTheme(): Theme {
    return $this->theme;
  }

  protected function loadEnv() {
    $env = file_get_contents(__DIR__ . '/../../../.env');
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