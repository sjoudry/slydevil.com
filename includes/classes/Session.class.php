<?php

namespace SlyDevil;
		
class Session {

	public const PASSWORD_SEED = 'SLYDEVILHOST';

	public const SESSION_MAX_AGE = 3600;

	public const SESSION_NAME = 'SLYDEVILHOST';

	public const SESSION_KEY_CREATED_NAME = 'created';

	public const SESSION_KEY_IP_ADDRESS_NAME = 'ip_address';

	public const SESSION_KEY_UPDATED_NAME = 'updated';

	public const SESSION_KEY_USER_AGENT_NAME = 'user_agent';

	public const SESSION_FORM_TOKEN_LIMIT = 100;

	public const SESSION_FORM_TOKEN_MAX_AGE = 3600;

	public const SESSION_FORM_TOKEN_NAME = 'form-tokens';

	public static function checkFormToken(string $form_id, string $form_token) {
		$token = Env::filterVariable($form_token);

		if (isset($_SESSION[self::SESSION_FORM_TOKEN_NAME][$form_id][$token])) {
			if ((time() - $_SESSION[self::SESSION_FORM_TOKEN_NAME][$form_id][$token]) > self::SESSION_FORM_TOKEN_MAX_AGE) {
				unset($_SESSION[self::SESSION_FORM_TOKEN_NAME][$form_id][$token]);
				return FALSE;
			}
			else {
				return TRUE;
			}
		}

		return FALSE;
	}

	public static function continueSession() {
		$time = time();

		if (session_status() === PHP_SESSION_NONE) {
			session_name(self::SESSION_NAME);
			session_start();
		}

		// Destroy session if needed.
		if (
			(isset($_SESSION[self::SESSION_KEY_UPDATED_NAME]) && ($time - $_SESSION[self::SESSION_KEY_UPDATED_NAME]) > self::SESSION_MAX_AGE) ||
			(isset($_SESSION[self::SESSION_KEY_IP_ADDRESS_NAME]) && $_SESSION[self::SESSION_KEY_IP_ADDRESS_NAME] != $_SERVER['REMOTE_ADDR']) ||
			(isset($_SESSION[self::SESSION_KEY_USER_AGENT_NAME]) && $_SESSION[self::SESSION_KEY_USER_AGENT_NAME] != $_SERVER['HTTP_USER_AGENT'])
		) {
			session_destroy();
		}

		// Keeps track of the last time a session was updated and is used above
		// to determine if the session has lapsed. Lapsed sessions cannot be
		// used and must be destroyed.
		$_SESSION[self::SESSION_KEY_UPDATED_NAME] = $time;
		
		// Keeps track of the ip address of the user and is used above to 
		// determine if the session is being used by more than one ip address.
		// Multiple ip addresses are not allowed and the session must be destroyed.
		$_SESSION[self::SESSION_KEY_IP_ADDRESS_NAME] = $_SERVER["REMOTE_ADDR"];
		
		// Keeps track of the browser of the user and is used above to determine
		// if the session is being used by more than one browser. Multiple browsers
		// are not alloed and the session must be destroyed.
		$_SESSION[self::SESSION_KEY_USER_AGENT_NAME] = $_SERVER["HTTP_USER_AGENT"];
		
		// Keep track of the creation time of a session and use it to
		// determine if the session has lapsed. Lapsed sessions cannot be used
		// and must be destroyed.
		if (empty($_SESSION[self::SESSION_KEY_CREATED_NAME])) {
			$_SESSION[self::SESSION_KEY_CREATED_NAME] = $time;
		}

		if (($time - $_SESSION[self::SESSION_KEY_CREATED_NAME]) > self::SESSION_MAX_AGE) {
			session_regenerate_id(TRUE);
			$_SESSION[self::SESSION_KEY_CREATED_NAME] = $time;
		}
	}

	public static function cryptPassword(string $value) {
		return md5(self::PASSWORD_SEED . $value);
	}
    
	public static function generateRandomString(int $length = 64, string $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789') {
		$random_string = '';
		$charset_length = strlen($charset);
		$string_length = $length;
		
		while ($string_length > 0) {
			$random_string .= $charset[mt_rand(0, $charset_length - 1)];
			$string_length--;
		}
		
		return $random_string;
	}

	public static function setFormToken(string $form_id) {
		self::continueSession();
		
		// Make sure the user cannot fill the session with tokens.
		if (isset($_SESSION[self::SESSION_FORM_TOKEN_NAME][$form_id])) {
			if (count($_SESSION[self::SESSION_FORM_TOKEN_NAME][$form_id]) > self::SESSION_FORM_TOKEN_LIMIT) {
				array_shift($_SESSION[self::SESSION_FORM_TOKEN_NAME][$form_id]);
			}
		}
		
		$token = self::generateRandomString();

		$_SESSION[self::SESSION_FORM_TOKEN_NAME][$form_id][$token] = time();
		
		return $token;
	}

}
