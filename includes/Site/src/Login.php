<?php

namespace Slydevil\Site;

use SlyDevil\Site\Database;
use SlyDevil\Form\Utility\ErrorHandler;
use SlyDevil\Form\Utility\SessionManager;

class Login {

  public const LOGIN_PUBLIC_ID = 'login_public_id';

  public const LOGIN_PASSWORD = 'login_password';

  protected ?Database $database = NULL;

  protected ?ErrorHandler $errorHandler = NULL;

  protected ?SessionManager $sessionManager = NULL;

  protected int $userId;

  protected string $userPublicId;

  protected string $userUsername;

  protected string $userFirstName;

  protected string $userLastName;

  protected string $userPassword;

  protected string $userDateAdded;

  protected ?string $userDateDeleted;

  protected array $userPermissions;

  public function __construct() {
    $this->database = new Database();
    $this->errorHandler = new ErrorHandler();
    $this->sessionManager = new SessionManager();
  }

  public function checkPermissions(string $operation) {
    return ($this->userId == 1 || !empty($this->userPermissions[$operation]));
  }

  public function clearLogin() {
    session_destroy();

    header('Location: /login/');
    exit;
  }

  public function getDatabase(): Database {
    return $this->database;
  }

  public function getErrorHandler(): ErrorHandler {
    return $this->errorHandler;
  }

  public function getSessionManager(): SessionManager {
    return $this->sessionManager;
  }

  public function getUserId() {
    return $this->userId;
  }

  public function handle(string $action, ?string $redirect_path = NULL, bool $redirect = TRUE) {
    if ($this->checkSession()) {
      if ($this->checkPermissions($action)) {
        if (!empty($redirect_path)) {
          header('Location: ' . $redirect_path);
          exit;
        }
      }
      else {
        if ($redirect) {
          header('Location: /login/');
          exit;
        }
        else {
          $this->errorHandler->addError('You do not have permissions to access this page.');
        }
      }
    }
    else {
      if ($redirect) {
        header('Location: /login/');
        exit;
      }
      else {
        $this->errorHandler->addError('Your username/password combination is incorrect.');
      }
    }
  }

	public function setPasswordSeed(string $seed) {
		$this->sessionManager->setPasswordSeed($seed);
	}

  public function setSession(string $username, string $password) {
    $username = $this->sessionManager->filterVariable($username);
    $password = $this->sessionManager->filterVariable($password);

    if (!empty($username)) {
      $result = $this->database->query(
        "SELECT user_id_public FROM user WHERE user_username = '%s' AND user_date_deleted IS NULL",
        [
          $username
        ]
      );

      if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $result->close();

        $_SESSION[self::LOGIN_PUBLIC_ID] = $user["user_id_public"];
      }

      $_SESSION[self::LOGIN_PASSWORD] = $this->sessionManager->cryptPassword($password);
    }
  }

  protected function checkSession() {
    if (!empty($_SESSION[self::LOGIN_PUBLIC_ID]) && !empty($_SESSION[self::LOGIN_PASSWORD])) {
      $result = $this->database->query(
        "SELECT * FROM user WHERE user_id_public = '%s' AND user_date_deleted IS NULL",
        [
          $_SESSION[self::LOGIN_PUBLIC_ID]
        ]
      );

      if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $result->close();

        $this->userId = $user["user_id"];
        $this->userPublicId = $user["user_id_public"];
        $this->userUsername = $user["user_username"];
        $this->userFirstName = $user["user_first_name"];
        $this->userLastName = $user["user_last_name"];
        $this->userPassword = $user["user_password"];
        $this->userDateAdded = $user["user_date_added"];
        $this->userDateDeleted = $user["user_date_deleted"];
        $this->userPermissions = [];

        $result = $this->database->query('SELECT * FROM user_perm WHERE user_id = %d', [$this->userId]);

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $this->userPermissions[$row["user_perm_value"]] = TRUE;
          }
        }
        $result->close();

        return ($this->userPassword == $_SESSION[self::LOGIN_PASSWORD]);
      }
    }

    return FALSE;
  }
}
