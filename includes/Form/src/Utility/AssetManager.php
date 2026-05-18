<?php

namespace SlyDevil\Form\Utility;

class AssetManager {

  protected static array $forms = [];

  protected static array $includedScriptCode = [];

  protected static array $includedScriptFiles = [];

  protected static array $includedStyleFiles = [];

  public function addForm(string $html_id) {
	  self::$forms[$html_id] = FALSE;
	}

	public function addJavascript(string $file) {
    if (!isset(self::$includedScriptFiles[$file])) {
      self::$includedScriptFiles[$file] = FALSE;
    }
	}

	public function addJavascriptCode(string $js) {
		self::$includedScriptCode[] = [
			'code' => $js,
			'bound' => FALSE,
    ];
	}

	public function addStylesheet(string $file) {
    if (!isset(self::$includedStyleFiles[$file])) {
      self::$includedStyleFiles[$file] = FALSE;
    }
	}

  public function returnJavascript() {
		return $this->returnScriptIncludes() . $this->returnScriptCode();
  }

  public function returnStylesheets() {
    $output = '';

    $all_included = TRUE;
		foreach (self::$includedStyleFiles as $file => $included) {
			if (!$included) {
        $all_included = FALSE;
        break;
      }
    }

    if (!$all_included) {
      $output .= '<style>';
      foreach (self::$includedStyleFiles as $file => $included) {
        if (!$included) {
          $output .= file_get_contents($file);
          self::$includedStyleFiles[$file] = TRUE;
        }
      }
      $output .= '</style>';
    }

		return $output;
	}

  protected function returnScriptCode() {
		$output = '';

    if (count(self::$includedScriptCode)) {
      $all_bound = TRUE;
      foreach (self::$includedScriptCode as $code) {
        if (!$code['bound']) {
          $all_bound = FALSE;
        }
      }

      if (!$all_bound) {
        $output .= '<script>';
        foreach (self::$includedScriptCode as $index => $code) {
					if (!$code['bound']) {
						$output .= $code['code'];
						self::$includedScriptCode[$index]['bound'] = TRUE;
					}
        }
        $output .= '</script>';
      }
	  }

		return $output;
	}

	protected function returnScriptIncludes() {
		$output = '';

    $all_included = TRUE;
		foreach (self::$includedScriptFiles as $file => $included) {
			if (!$included) {
        $all_included = FALSE;
        break;
      }
    }

    if (!$all_included) {
      $output .= '<script>';
      foreach (self::$includedScriptFiles as $file => $included) {
        if (!$included) {
          $output .= file_get_contents($file);
          self::$includedScriptFiles[$file] = TRUE;
        }
      }
      $output .= '</script>';
    }

    return $output;
	}

}