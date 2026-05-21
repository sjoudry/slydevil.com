<?php

namespace SlyDevil\Form\Element;

use SlyDevil\Form\ParentElementBase;
use SlyDevil\Form\ParentElementInterface;
use SlyDevil\Form\Utility\SessionManager;

class Form extends ParentElementBase {

  public const SUBMITTED_HIDDEN_NAME = 'FORM-SUBMITTED';

  public const TOKEN_HIDDEN_NAME = 'FORM-TOKEN';

  public const METHOD_GET = 'get';

  public const METHOD_POST = 'post';

  protected ?SessionManager $sessionManager = NULL;

  protected bool $useSessionToken = TRUE;

  public static function create(string $name, string $method = self::METHOD_POST, ?string $action = NULL): self {
    return new static($name, $method, $action);
  }

  public function __construct(string $name, string $method = self::METHOD_POST, ?string $action = NULL) {
    parent::__construct($name);

    $this->elementType = 'form';
    $this->setMethod($method);
    $this->setAction($action);

    $this->sessionManager = new SessionManager();

    $this->assetManager->addStylesheet(__DIR__ . '/../../assets/dist/css/Form.min.css');
    $this->assetManager->addJavascript(__DIR__ . '/../../assets/dist/js/Form.min.js');
  }

	public function disableToken(): self {
		$this->useSessionToken = FALSE;

		return $this;
	}

  public function render(): string {
    $this->assetManager->addForm($this->id);

    $output = $this->assetManager->returnStylesheets();

    $hidden = Input::create('hidden', self::SUBMITTED_HIDDEN_NAME)
      ->setAttribute('value', $this->id);

    $output .= $this->renderElementTop() .
      $this->errorHandler->renderErrors($this->id) .
      '<form' . $this->renderElementAttributes() . ' novalidate="novalidate">' .
      $hidden->render();

    if ($this->useSessionToken) {
      $hidden = Input::create('hidden', self::TOKEN_HIDDEN_NAME);
      if (isset($_REQUEST[self::TOKEN_HIDDEN_NAME])) {
        if ($this->sessionManager->checkFormToken($this->id, $_REQUEST[self::TOKEN_HIDDEN_NAME])) {
          $hidden->setAttribute('value', $_REQUEST[self::TOKEN_HIDDEN_NAME]);
        }
        else {
          $hidden->setAttribute('value', $this->sessionManager->setFormToken($this->id));
        }
      }
      else {
        $hidden->setAttribute('value', $this->sessionManager->setFormToken($this->id));
      }

      $output .= $hidden->render();
    }

    foreach ($this->elements as $element) {
      $output .= $element->render();
    }

    $output .= '</form>';
    $output .= $this->renderElementBottom();

    $output .= $this->validatorManager->returnSettings($this);
    $output .= $this->assetManager->returnJavascript();

    return $output;
  }

  public function setAction(?string $action = NULL): self {
    if (empty($action)) {
      $action = $_SERVER['PHP_SELF'];
    }

    $this->setAttribute('action', $action);

    return $this;
  }

  public function setMethod(string $method = self::METHOD_POST): self {
    if ($method != self::METHOD_GET && $method != self::METHOD_POST) {
      $method = self::METHOD_POST;
    }
    $this->setAttribute('method', $method);

    return $this;
  }

  public function submitted() {
    if (isset($_REQUEST[self::SUBMITTED_HIDDEN_NAME]) && $_REQUEST[self::SUBMITTED_HIDDEN_NAME] == $this->id) {
      if (isset($_REQUEST[self::TOKEN_HIDDEN_NAME]) && !$this->sessionManager->checkFormToken($this->id, $_REQUEST[self::TOKEN_HIDDEN_NAME])) {
        $this->errorHandler->addError('Form Token is no longer valid. Please re-submit the form');
      }

      return TRUE;
    }

    return FALSE;
  }

  public function validated(array $elements = []) {
    $form_elements = empty($elements) ? $this->elements : $elements;

    $valid = TRUE;
    foreach ($form_elements as $element) {
      if (
        $element instanceof ParentElementInterface &&
        count($element->getElements()) &&
        !$this->validated($element->getElements())
      ) {
        $valid = FALSE;
      }

      if (!$element->validateField()) {
        $valid = FALSE;
      }
    }

    return $valid;
	}

}