<?php

namespace SlyDevil\Form;

class Helper {

  public const INCLUDES_PATH = '/includes/';

  public static array $bindJsCode = [];

  public static array $fileIncludedJs = [];

  public static array $fileIncludedCss = [];

  public static array $jsCode = [];

  public static array $forms = [];

  public static function addForm(string $html_id) {
	  self::$forms[$html_id] = FALSE;
	}

	public static function addJavascript(string $file) {
    if (!isset(self::$fileIncludedJs[$file])) {
      self::$fileIncludedJs[$file] = FALSE;
    }
	}

	public static function addJavascriptCode(string $js) {
		self::$jsCode[] = [
			"code"  => $js,
			"bound" => FALSE,
    ];
	}

	public static function addStylesheet(string $file) {
    if (!isset(self::$fileIncludedCss[$file])) {
      self::$fileIncludedCss[$file] = FALSE;
    }
	}

  // bind a single event for an html element
  public static function bindEvent(string $html_id, string $event, string $js, bool $bind_submit = TRUE) {
    if (!empty($js)) {
      self::$bindJsCode['event'][$html_id][$event][] = [
        "code"  => $js,
        "bound"  => FALSE,
        "submit" => $bind_submit,
      ];
    }
  }

  // bind multiple events for an html element
  public static function bindEvents(string $html_id, array &$events, bool $bind_submit = TRUE) {
    foreach ($events as $event => $js) {
      self::bindEvent($html_id, $event, $js, $bind_submit);
    }
  }

  // bind javascript to window.onload event
  public static function bindJavascript(string $js) {
    if (!empty($js)) {
      self::$bindJsCode['window'][] = [
        "code"  => $js,
        "bound" => FALSE,
      ];
    }
  }

  public static function returnJavascript() {
		$output = self::returnJSIncludes();

		foreach (self::$forms as $html_id => $bound) {
			if (!$bound) {
				$output .= self::returnBindCode($html_id);
				self::$forms[$html_id] = TRUE;
			}
		}

		$output .= self::returnBindCode();
    $output .= self::returnJSCode();

    return $output;
  }

  public static function returnStylesheets() {
    $output = '';

		foreach (self::$fileIncludedCss as $file => $included) {
			if (!$included) {
				$output .= "<link href='" . self::INCLUDES_PATH . $file . "' media='screen' rel='stylesheet' type='text/css' />";
				self::$fileIncludedCss[$file] = TRUE;
			}
		}

		return $output;
	}

	protected static function returnBindCode($_button = NULL) {
		$output = '';

    if (sizeof(self::$bindJsCode)) {
      $all_bound = TRUE;
      foreach (self::$bindJsCode as $type => $fields) {
        if ($type == 'event') {
          foreach ($fields as $field => $events) {
            foreach ($events as $event) {
              foreach ($event as $data) {
                if (!$data['bound']) {
                  $all_bound = FALSE;
                }
              }
            }
          }
        }

        if ($type == 'window') {
          foreach ($fields as $data) {
            if (!$data['bound']) {
              $all_bound = FALSE;
            }
          }
        }
      }

      if (!$all_bound) {
        $output .= "<script type='text/javascript'>";

        foreach (self::$bindJsCode as $type => $fields) {
          if ($type == 'event') {
            foreach ($fields as $field => $events) {
              foreach ($events as $event_name => $event) {
                foreach ($event as $index => $data) {
                  if (!$data['bound']) {
                    if ($_button == NULL) {
											$output .= "Form_bind_event('" . $field . "', '" . $event_name . "', function(){return " . $data["code"] . "});";
											self::$bindJsCode[$type][$field][$event_name][$index]["bound"] = TRUE;
										}
										else if ($data['submit']) {
											$output .= "Form_bind_event('" . $_button . "', 'submit', function(){return " . $data["code"] . "});";
										}
                  }
                }
              }
            }
          }

          if ($type == 'window') {
            foreach ($fields as $index => $data) {
              if (!$data['bound']) {
                $output .= "Form_bind_event('window', 'load', function(){return " . $data["code"] . "});";
                self::$bindJsCode[$type][$index]["bound"] = TRUE;
              }
            }
          }
        }

        $output .= "</script>";
      }
    }

    return $output;
	}

	protected static function returnJSCode() {
		$output = '';

    if (count(self::$jsCode)) {
      $all_bound = TRUE;
      foreach (self::$jsCode as $code) {
        if (!$code['bound']) {
          $all_bound = FALSE;
        }
      }

      if (!$all_bound) {
        $output .= "<script type='text/javascript'>";
        foreach (self::$jsCode as $index => $code) {
					if (!$code['bound']) {
						$output .= $code["code"];
						self::$jsCode[$index]["bound"] = TRUE;
					}
        }
        $output .= "</script>";
      }
	  }

		return $output;
	}

	protected static function returnJSIncludes() {
		$output = '';

		foreach (self::$fileIncludedJs as $file => $included) {
			if (!$included) {
				$output .= "<script type='text/javascript' src='" . self::INCLUDES_PATH . $file . "'></script>";
				self::$fileIncludedJs[$file] = TRUE;
			}
		}

		return $output;
	}

}