<?php

namespace Slydevil;

use SlyDevil\Database;
use SlyDevil\Env;
use SlyDevil\Form\Element\Form;

class Login {

  public const LOGIN_PUBLIC_ID = 'login_public_id';

  public const LOGIN_PASSWORD = 'login_password';

  public static int $userId;

  public static string $userPublicId;

  public static string $userUsername;

  public static string $userFirstName;

  public static string $userLastName;

  public static string $userPassword;
 
  public static string $userDateAdded;

  public static ?string $userDateDeleted;

  public static array $userPermissions;
    
  public static function checkPermissions(string $operation) {
    if (self::$userId == 1 || isset(self::$userPermissions[$operation])) {
      return TRUE;
    }
        
    return FALSE;
  }
    
  public static function checkLogin() {
    if (isset($_SESSION[self::LOGIN_PUBLIC_ID]) && isset($_SESSION[self::LOGIN_PASSWORD])) {
      $db = new Database();
      $result = $db->query(
        "SELECT * FROM user WHERE user_id_public = '%s' AND user_date_deleted IS NULL",
        [
          $_SESSION[self::LOGIN_PUBLIC_ID]
        ]
      );

      if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $result->close();

        self::$userId = $user["user_id"];
        self::$userPublicId = $user["user_id_public"];
        self::$userUsername = $user["user_username"];
        self::$userFirstName = $user["user_first_name"];
        self::$userLastName = $user["user_last_name"];
        self::$userPassword = $user["user_password"];
        self::$userDateAdded = $user["user_date_added"];
        self::$userDateDeleted = $user["user_date_deleted"];
        self::$userPermissions = [];
                
        $result = $db->query(
          "SELECT * FROM user_perm WHERE user_id = %d",
          [
            self::$userId
          ]
        );
                
        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            self::$userPermissions[$row["user_perm_value"]] = TRUE;
          }
        }
                
        $result->close();

        return (self::$userPassword == $_SESSION[self::LOGIN_PASSWORD]);
      }
    }

    return FALSE;
  }
    
  public static function clearLogin() {
    session_destroy();
  }
    
  public static function handleLogin(string $action, ?string $redirect_path = NULL, bool $redirect = TRUE, ?Form $form = NULL) {
    if (self::checkLogin()) {
      if (self::checkPermissions($action)) {
        if ($redirect_path != NULL) {
          header("Location: " . $redirect_path);
          exit;
        }
      }
      else {
        if ($redirect) {
          header("Location: /login/");
          exit;
        }
        else {
          $form->addError("You do not have permissions to access this page.");
        }
      }
    }
    else {
      if ($redirect) {
        header("Location: /login/");
        exit;
      }
      else {
        $form->addError("Your username/password combination is incorrect.");
      }
    }
  }
    
  public static function setLogin(string $username, string $password) {
    if (!empty($username)) {
      $db = new Database();
      $result = $db->query(
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

      $_SESSION[self::LOGIN_PASSWORD] = Session::cryptPassword(Env::filterVariable($password));
    }
  }
}

?>
