<?php

namespace SlyDevil\Form\Element;

use SlyDevil\Env;
use SlyDevil\Form\Element\Label;
use SlyDevil\Form\Helper;

abstract class Base {

  public const FORM_ELEMENT_CHECKED = 'checked';

  public const FORM_ELEMENT_NOT_CHECKED = NULL;

  public const FORM_ELEMENT_DIR_LTR = 'ltr';

  public const FORM_ELEMENT_DIR_RTL = 'rtl';

  public const FORM_ELEMENT_DISABLED = 'disabled';

  public const FORM_ELEMENT_LABEL_ALIGN_LEFT = 'left';

  public const FORM_ELEMENT_LABEL_ALIGN_RIGHT = 'right';
 
  public const FORM_ELEMENT_METHOD_GET = 'get';

  public const FORM_ELEMENT_METHOD_POST = 'post';

  public const FORM_ELEMENT_MULTIPLE = 'multiple';

  public const FORM_ELEMENT_NOT_MULTIPLE = NULL;

  public const FORM_ELEMENT_NOT_DISABLED = NULL;

  public const FORM_ELEMENT_NOT_READONLY = NULL;

  public const FORM_ELEMENT_READONLY = 'readonly';

  public const FORM_ELEMENT_TYPE_BUTTON = 'button';

  public const FORM_ELEMENT_TYPE_RESET = 'reset';

  public const FORM_ELEMENT_TYPE_SUBMIT = 'submit';

  public const VALIDATES_IF_FIELD_MATCHES = TRUE;

  public const VALIDATES_IF_FIELD_NOT_MATCHES = FALSE;

  public const VALIDATES_IF_PATTERN_MATCHES = TRUE;

  public const VALIDATES_IF_PATTERN_NOT_MATCHES = FALSE;

  protected array $attributes = [
    'accept-charset' => NULL,
    'accesskey' => NULL,
    'action' => NULL,
    'autocomplete' => NULL,
    'checked' => NULL,
    'class' => NULL,
    'cols' => NULL,
    'dir' => NULL,
    'disabled' => NULL,
    'enctype' => NULL,
    'for' => NULL,
    'id' => NULL,
    'lang' => NULL,
    'maxlength' => NULL,
    'method' => NULL,
    'multiple' => NULL,
    'name' => NULL,
    'readonly' => NULL,
    'rows' => NULL,
    'size' => NULL,
    'style' => NULL,
    'tabindex' => NULL,
    'target' => NULL,
    'title' => NULL,
    'type' => NULL,
    'value' => NULL,
  ];

  protected array $availableAttributes = [];
  
  protected array $availableEvents = [];

  protected bool $collapsed = FALSE;
  
  protected bool $collapsible = FALSE;

  protected bool $configValidated = FALSE;

  protected ?string $content = NULL;

  protected ?string $description = NULL;

  protected bool $elementPrepared = FALSE;
  
  protected array $elements = [];

  protected ?string $elementType = NULL;

  protected array $errors = [];

  protected array $events = [
    'blur' => NULL,
    'change' => NULL,
    'click' => NULL,
    'dblclick' => NULL,
    'focus' => NULL,
    'keydown' => NULL,
    'keypress' => NULL,
    'keyup' => NULL,
    'mousedown' => NULL,
    'mousemove' => NULL,
    'mouseout' => NULL,
    'mouseover' => NULL,
    'mouseup' => NULL,
    'reset' => NULL,
    'select' => NULL,
    'submit' => NULL,
  ];

  protected array $fileTypes = [];

  protected array $functions = [];

  protected ?Label $label = NULL;

  protected ?string $labelAlign = NULL;

  protected ?string $labelText = NULL;

  protected ?string $legend = NULL;

  protected ?int $lengthLong = NULL;
  
  protected ?int $lengthShort = NULL;
  
  protected array $matches = [];

  protected ?int $maximumChecked = NULL;

  protected ?int $maximumSelected = NULL;

  protected ?int $minimumChecked = NULL;
  
  protected ?int $minimumSelected = NULL;
  
  protected array $notMatches = [];

  protected array $options = [];

  protected array $patternsMatch = [];

  protected array $patternsNotMatch = [];

  protected ?string $pretext = NULL;

  protected ?string $posttext = NULL;

  protected mixed $selected = NULL;

  protected array $validators = [
    'custom' => FALSE,
    'existance' => FALSE,
    'file_type' => FALSE,
    'length_long' => FALSE,
    'length_short' => FALSE,
    'match' => FALSE,
    'maximum_checked' => FALSE,
    'maximum_selected' => FALSE,
    'minimum_checked' => FALSE,
    'minimum_selected' => FALSE,
    'pattern' => FALSE,
  ];

  protected array $validatorErrors = [];

  abstract public function returnHTML();

  abstract protected function validateConfig();

  public function addElement(Base $element) {
    $this->elements[] = $element;

    return $this;
  }

  public function addError(string $error) {
    $this->errors[] = $error;

    return $this;
  }

  public function addLabel(string $label_text, string $label_align = self::FORM_ELEMENT_LABEL_ALIGN_LEFT) {
    $this->labelAlign = $label_align;
    $this->label = Label::create()
      ->setLabelText($label_text);

    return $this;
  }

  public function addValidatorContainsLowercase(?string $error_override = NULL) {
    $error = $error_override ?? "'%s' must contain at least one lowercase character";

    $this->addValidatorPattern("/[a-z]/", sprintf($error, $this->determineLabel()));

    return $this;
  }

  public function addValidatorContainsNumber(?string $error_override = NULL) {
    $error = $error_override ?? "'%s' must contain at least one number";

    $this->addValidatorPattern("/[0-9]/", sprintf($error, $this->determineLabel()));

    return $this;
  }

  public function addValidatorContainsUppercase(?string $error_override = NULL) {
    $error = $error_override ?? "'%s' must contain at least one uppercase character";

    $this->addValidatorPattern("/[A-Z]/", sprintf($error, $this->determineLabel()));

    return $this;
  }

  public function addValidatorCustom(string $function, string $error) {
    $this->validators["custom"] = TRUE;
    $this->functions[$function] = $error;

    return $this;
  }

  public function addValidatorEmail(?string $error_override = NULL) {
    $error = $error_override ?? "'%s' is an invalid email address";
    $this->addValidatorPattern("/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/", sprintf($error, $this->determineLabel()));

    return $this;
  }

  public function addValidatorExistance(?string $error_override = NULL) {
    $error = $error_override ?? "'%s' is missing and is required";

    $this->validators["existance"] = TRUE;
    $this->validatorErrors["existance"] = $error;

    return $this;
  }

  public function addValidatorFileType(string $file_type, ?string $error_override = NULL) {
    $error = $error_override ?? "'%s' does not have a valid file type. Valid file types = (%s)";
      
    $this->validators["file_type"] = TRUE;
    $this->fileTypes[$file_type] = TRUE;
    $this->validatorErrors["file_type"] = $error;

    return $this;
  }

  public function addValidatorLengthLong(int $length_long, ?string $error_override = NULL) {
    $error = $error_override ?? "'%s' is too long. Length must be less than (or equal to) %s";
      
    $this->validators["length_long"] = TRUE;
    $this->lengthLong = $length_long;
    $this->validatorErrors["length_long"] = $error;

    return $this;
  }

  public function addValidatorLengthShort(int $length_short, ?string $error_override = NULL) {
    $error = $error_override ?? "'%s' is too short. Length must be greater than (or equal to) %s";
     
    $this->validators["length_short"] = TRUE;
    $this->lengthShort = $length_short;
    $this->validatorErrors["length_short"] = $error;

    return $this;
  }

  public function addValidatorMatch(string $name, string $compare_label, bool $matches = self::VALIDATES_IF_FIELD_MATCHES, ?string $error_override = NULL) {     
    $this->validators["match"] = TRUE;

    if ($matches == self::VALIDATES_IF_FIELD_MATCHES) {
      $error = $error_override ?? "'%s' must be the same as Field '%s'";
      $this->matches[$name] = $compare_label;
      $this->validatorErrors["match"][$name] = $error;
    }
    else {
      $error = $error_override ?? "'%s' must not be the same as Field '%s'";
      $this->notMatches[$name] = $compare_label;
      $this->validatorErrors["not_match"][$name] = $error;
    }

    return $this;
  }

  public function addValidatorMaximumChecked(int $maxiumum = 1, ?string $error_override = NULL) {
    $error = $error_override ?? "'%s' has too many checked values. Maximum checked is %s";
      
    $this->validators["maximum_checked"] = TRUE;
    $this->maximumChecked = $maxiumum;
    $this->validatorErrors["maximum_checked"] = $error;

    return $this;
  }

  public function addValidatorMaximumSelected(int $maxiumum = 1, ?string $error_override = NULL) {
    $error = $error_override ?? "'%s' has too many selected values. Maximum selected is %s";
      
    $this->validators["maximum_selected"] = TRUE;
    $this->maximumSelected = $maxiumum;
    $this->validatorErrors["maximum_selected"] = $error;

    return $this;
  }

  public function addValidatorMinimumChecked(int $minumum = 1, ?string $error_override = NULL) {
    $error = $error_override ?? "'%s' does not have enough values checked. Minimum checked is %s";
      
    $this->validators["minimum_checked"] = TRUE;
    $this->minimumChecked = $minumum;
    $this->validatorErrors["minimum_checked"] = $error;

    return $this;
  }

  public function addValidatorMinimumSelected(int $minumum = 1, ?string $error_override = NULL) {
    $error = $error_override ?? "'%s' does not have enough values selected. Minimum selected is %s";

    $this->validators["minimum_selected"] = TRUE;
    $this->minimumSelected = $minumum;
    $this->validatorErrors["minimum_selected"] = $error;

    return $this;
  }

  public function addValidatorNumeric(?string $error_override = NULL) {
    $error = $error_override ?? "'%s' must be numeric (integer or decimal)";
     
    $this->addValidatorPattern("/[^\-0-9\.]/", sprintf($error, $this->determineLabel()), self::VALIDATES_IF_PATTERN_NOT_MATCHES);

    return $this;
  }

  public function addValidatorPattern(string $pattern, string $error, bool $matches = self::VALIDATES_IF_PATTERN_MATCHES) {
    $this->validators["pattern"] = TRUE;

    if ($matches == self::VALIDATES_IF_PATTERN_MATCHES) {
      $this->patternsMatch[$pattern] = $error;
    }
    else {
      $this->patternsNotMatch[$pattern] = $error;
    }

    return $this;
  }

  public function getAcceptCharset() {
    return $this->getAttribute('accept-charset');
  }

  public function getAccesskey() {
    return $this->getAttribute('accesskey');
  }

  public function getAction() {
    return $this->getAttribute('action');
  }

  public function getAutocomplete() {
    return $this->getAttribute('autocomplete');
  }

  public function getBlur() {
    return $this->getEvent('blur');
  }

  public function getChange() {
    return $this->getEvent('change');
  }

  public function getChecked() {
    return $this->getAttribute('checked');
  }

  public function getClass() {
    return $this->getAttribute('class');
  }

  public function getClick() {
    return $this->getEvent('click');
  }

  public function getCollapsed() {
    return $this->collapsed;
  }

  public function getCollapsible() {
    return $this->collapsible;
  }

  public function getCols() {
    return $this->getAttribute('cols');
  }

  public function getContent() {
    return $this->content;
  }

  public function getDblclick() {
    return $this->getEvent('dblclick');
  }

  public function getDescription() {
    return $this->description;
  }

  public function getDir() {
    return $this->getAttribute('dir');
  }

  public function getDisabled() {
    return $this->getAttribute('disabled');
  }

  public function getEnctype() {
    return $this->getAttribute('enctype');
  }

  public function getElements() {
    return $this->elements;
  }

  public function getElementType() {
		return $this->elementType;
	}

  public function getErrors() {
		return $this->errors;
	}

  public function getEvents() {
		$events = [];

		foreach ($this->events as $name => $value) {
			if ($value != NULL) {
				if (isset($this->availableEvents[$name])) {
					$events[$name] = $value;
				}
			}
		}

		return $events;
	}

  public function getFocus() {
    return $this->getEvent('focus');
  }

  public function getFor() {
    return $this->getAttribute('for');
  }

  public function getId() {
    return $this->getAttribute('id');
  }

  public function getKeydown() {
    return $this->getEvent('keydown');
  }

  public function getKeypress() {
    return $this->getEvent('keypress');
  }

  public function getKeyup() {
    return $this->getEvent('keyup');
  }

  public function getLabel() {
    return $this->label;
  }

  public function getLabelAlign() {
    return $this->labelAlign;
  }

  public function getLabelText() {
    return $this->labelText;
  }

  public function getLang() {
    return $this->getAttribute('lang');
  }

  public function getLegend() {
    return $this->legend;
  }

  public function getMaxlength() {
    return $this->getAttribute('maxlength');
  }

  public function getMethod() {
    return $this->getAttribute('method');
  }

  public function getMousedown() {
    return $this->getEvent('mousedown');
  }

  public function getMousemove() {
    return $this->getEvent('mousemove');
  }

  public function getMouseout() {
    return $this->getEvent('mouseout');
  }

  public function getMouseover() {
    return $this->getEvent('mouseover');
  }

  public function getMouseup() {
    return $this->getEvent('mouseup');
  }

  public function getMultiple() {
    return $this->getAttribute('multiple');
  }

  public function getName() {
    return $this->getAttribute('name');
  }

  public function getOptions() {
    return $this->options;
  }

  public function getPretext() {
    return $this->pretext;
  }

  public function getPosttext() {
    return $this->posttext;
  }

  public function getReadonly() {
    return $this->getAttribute('readonly');
  }

  public function getReset() {
    return $this->getEvent('reset');
  }

  public function getRows() {
    return $this->getAttribute('rows');
  }

  public function getSelect() {
    return $this->getEvent('select');
  }

  public function getSelected() {
    return $this->selected;
  }

  public function getSize() {
    return $this->getAttribute('size');
  }

  public function getStyle() {
    return $this->getAttribute('style');
  }

  public function getSubmit() {
    return $this->getEvent('submit');
  }

  public function getTabindex() {
    return $this->getAttribute('tabindex');
  }

  public function getTarget() {
    return $this->getAttribute('target');
  }

  public function getTitle() {
    return $this->getAttribute('title');
  }

  public function getType() {
    return $this->getAttribute('type');
  }

  public function getValidators() {
		return $this->validators;
	}

  public function getValue() {
    return $this->getAttribute('value');
  }

  public function removeValidatorContainsLowercase() {
    $this->validators["contains_lowercase"] = FALSE;

    return $this;
  }

  public function removeValidatorContainsNumber() {
    $this->validators["contains_number"] = FALSE;

    return $this;
  }

  public function removeValidatorContainsUppercase() {
    $this->validators["contains_uppercase"] = FALSE;

    return $this;
  }

  public function removeValidatorCustom(string $function) {
    if (isset($this->functions[$function])) {
      unset($this->functions[$function]);
    }

    if (empty($this->functions)) {
      $this->validators["custom"] = FALSE;
    }

    return $this;
  }

  public function removeValidatorEmail() {
    $this->validators["email"] = FALSE;

    return $this;
  }

  public function removeValidatorExistance() {
    $this->validators["existance"] = FALSE;

    return $this;
  }

  public function removeValidatorFileType(string $file_type) {
    if (isset($this->fileTypes[$file_type])) {
      unset($this->fileTypes[$file_type]);
    }

    if (empty($this->fileTypes)) {
      $this->validators["file_type"] = FALSE;
    }

    return $this;
  }

  public function removeValidatorLengthLong() {
    $this->validators["length_long"] = FALSE;
    $this->lengthLong = NULL;

    return $this;
  }

  public function removeValidatorLengthShort() {
    $this->validators["length_short"] = FALSE;
    $this->lengthShort = NULL;

    return $this;
  }

  public function removeValidatorMatch(string $name) {
    if (isset($this->matches[$name])) {
      unset($this->matches[$name]);
    }

    if (isset($this->notMatches[$name])) {
      unset($this->not_Matches[$name]);
    }

    if (empty($this->matches) && empty($this->notMatches)) {
      $this->validators["match"] = FALSE;
    }

    return $this;
  }

  public function removeValidatorMaximumChecked() {
    $this->validators["maximum_checked"] = FALSE;
    $this->maximumChecked = NULL;

    return $this;
  }

  public function removeValidatorMaximumSelected() {
    $this->validators["maximum_selected"] = FALSE;
    $this->maximumSelected = NULL;

    return $this;
  }

  public function removeValidatorMinimumChecked() {
    $this->validators["minimum_checked"] = FALSE;
    $this->minimumChecked = NULL;

    return $this;
  }

  public function removeValidatorMinimumSelected() {
    $this->validators["minimum_selected"] = FALSE;
    $this->minimumSelected = NULL;

    return $this;
  }

  public function removeValidatorNumeric() {
    $this->validators["numeric"] = FALSE;

    return $this;
  }

  public function removeValidatorPattern(string $pattern) {
    if (isset($this->patternsMatch[$pattern])) {
      unset($this->patternsMatch[$pattern]);
    }

    if (isset($this->patternsNotMatch[$pattern])) {
      unset($this->patternsNotMatch[$pattern]);
    }

    if (empty($this->patternsMatch) && empty($this->patternsNotMatch)) {
      $this->validators["pattern"] = FALSE;
    }

    return $this;
  }

  public function setAcceptCharset(string $value) {
    return $this->setAttribute('accept-charset', $value);
  }

  public function setAccesskey(string $value) {
    return $this->setAttribute('accesskey', $value);
  }
 
  public function setAction(string $value) {
    return $this->setAttribute('action', $value);
  }

  public function setAutocomplete(string $value) {
    return $this->setAttribute('autocomplete', $value);
  }

  public function setBlur(string $value) {
    return $this->setEvent('blur', $value);
  }

  public function setChecked(bool $value) {
    return $this->setAttribute('checked', $value ? self::FORM_ELEMENT_CHECKED : self::FORM_ELEMENT_NOT_CHECKED);
  }

  public function setChange(string $value) {
    return $this->setEvent('change', $value);
  }

  public function setClass(string $value) {
    return $this->setAttribute('class', $value);
  }

  public function setClick(string $value) {
    return $this->setEvent('click', $value);
  }

  public function setCols(string $value) {
    return $this->setAttribute('cols', $value);
  }

  public function setCollapsed(bool $value) {
    $this->collapsed = $value;
    return $this;
  }

  public function setCollapsible(bool $value) {
    $this->collapsible = $value;
    return $this;
  }

  public function setContent(string $value) {
    $this->content = $value;
    return $this;
  }

  public function setDblclick(string $value) {
    return $this->setEvent('dblclick', $value);
  }

  public function setDescription(string $value) {
    $this->description = $value;
    return $this;
  }

  public function setDir(string $value) {
    return $this->setAttribute('dir', $value);
  }

  public function setDisabled(string $value) {
    return $this->setAttribute('disabled', $value);
  }

  public function setElements(array $value) {
    $this->elements = $value;
    return $this;
  }

  public function setEnctype(string $value) {
    return $this->setAttribute('enctype', $value);
  }

  public function setFocus(string $value) {
    return $this->setEvent('focus', $value);
  }

  public function setFor(string $value) {
    return $this->setAttribute('for', $value);
  }

  public function setId(string $value) {
    return $this->setAttribute('id', $value);
  }

  public function setKeydown(string $value) {
    return $this->setEvent('keydown', $value);
  }

  public function setKeypress(string $value) {
    return $this->setEvent('keypress', $value);
  }

  public function setKeyup(string $value) {
    return $this->setEvent('keyup', $value);
  }

  public function setLabel(Label $value) {
    $this->label = $value;
    return $this;
  }

  public function setLabelAlign(string $value) {
    $this->labelAlign = $value;
    return $this;
  }

  public function setLabelText(string $value) {
    $this->labelText = $value;
    return $this;
  }

  public function setLang(string $value) {
    return $this->setAttribute('lang', $value);
  }

  public function setLegend(string $value) {
    $this->legend = $value;
    return $this;
  }

  public function setMaxlength(string $value) {
    return $this->setAttribute('maxlength', $value);
  }

  public function setMethod(string $value) {
    return $this->setAttribute('method', $value);
  }

  public function setMousedown(string $value) {
    return $this->setEvent('mousedown', $value);
  }

  public function setMousemove(string $value) {
    return $this->setEvent('mousemove', $value);
  }

  public function setMouseout(string $value) {
    return $this->setEvent('mouseout', $value);
  }

  public function setMouseover(string $value) {
    return $this->setEvent('mouseover', $value);
  }

  public function setMouseup(string $value) {
    return $this->setEvent('mouseup', $value);
  }

  public function setMultiple(string $value) {
    return $this->setAttribute('multiple', $value);
  }

  public function setName(string $value) {
    return $this->setAttribute('name', $value);
  }

  public function setOptions(array $value) {
    $this->options = $value;
    return $this;
  }

  public function setPretext(string $value) {
    $this->pretext = $value;
    return $this;
  }
  
  public function setPosttext(string $value) {
    $this->posttext = $value;
    return $this;
  }

  public function setReadonly(bool $value) {
    return $this->setAttribute('readonly', $value ? self::FORM_ELEMENT_READONLY : self::FORM_ELEMENT_NOT_READONLY);
  }

  public function setReset(string $value) {
    return $this->setEvent('reset', $value);
  }

  public function setRows(string $value) {
    return $this->setAttribute('rows', $value);
  }

  public function setSelect(string $value) {
    return $this->setEvent('select', $value);
  }

  public function setSelected(mixed $value) {
    $this->selected = $value;
    return $this;
  }

  public function setSize(string $value) {
    return $this->setAttribute('size', $value);
  }

  public function setStyle(string $value) {
    return $this->setAttribute('style', $value);
  }

  public function setSubmit(string $value) {
    return $this->setEvent('submit', $value);
  }

  public function setTabindex(string $value) {
    return $this->setAttribute('tabindex', $value);
  }

  public function setTarget(string $value) {
    return $this->setAttribute('target', $value);
  }

  public function setTitle(string $value) {
    return $this->setAttribute('title', $value);
  }

  public function setType(string $value) {
    return $this->setAttribute('type', $value);
  }

  public function setValue(mixed $value) {
    return $this->setAttribute('value', $value);
  }

  public function validateField() {
    $valid = TRUE;

    foreach ($this->validators as $validator => $run) {
      if ($run) {
        switch ($validator) {
          case "custom":
            if (!$this->validateValueCustom()) {
              $valid = FALSE;
            }
            break;
          
          case "existance":
            if (!$this->validateValueExistance()) {
              $valid = FALSE;
            }
            break;

          case "file_type":
            if (!$this->validateValueFileTypes()) {
              $valid = FALSE;
            }
            break;

          case "length_long":
            if (!$this->validateValueLengthLong()) {
              $valid = FALSE;
            }
            break;

          case "length_short":
            if (!$this->validateValueLengthShort()) {
              $valid = FALSE;
            }
            break;

          case "match":
            if (!$this->validateValueMatch()) {
              $valid = FALSE;
            }
            break;

          case "maximum_checked":
            if (!$this->validateValueMaximumChecked()) {
              $valid = FALSE;
            }
            break;

          case "maximum_selected":
            if (!$this->validateValueMaximumSelected()) {
              $valid = FALSE;
            }
            break;

          case "minimum_checked":
            if (!$this->validateValueMinimumChecked()) {
              $valid = FALSE;
            }
            break;

          case "minimum_selected":
            if (!$this->validateValueMinimumSelected()) {
              $valid = FALSE;
            }
            break;

          case "pattern":
            if (!$this->validateValuePattern()) {
              $valid = FALSE;
            }
            break;
        }
      }
    }

    return $valid;
  }

  public static function create() {
    return new static();
  }

  protected function determineLabel() {
    $label = $this->getName();

    if ($this->label != NULL && $this->label->getLabelText() != NULL) {
      $label = $this->label->getLabelText();
    }

    return $label;
  }

  protected function getAttribute(string $name) {
    return $this->attributes[$name];
  }

  protected function getEvent(string $name) {
    return $this->events[$name];
  }

  protected function prepareElement() {
    if (!$this->elementPrepared) {
      $this->elementPrepared = TRUE;

      if (count($this->elements) > 0) {
        foreach ($this->elements as $element) {
          $element->prepareElement();

          if (count($element->getErrors()) > 0) {
            foreach ($element->getErrors() as $error) {
              $this->errors[] = $error;
            }
          }

          if ($element->getId() != NULL) {
            foreach ($element->getValidators() as $type => $use) {
              if ($use) {
                $js = $element->validatorJs($type);
                if (is_array($js)) {
                  foreach ($js as $code) {
                    Helper::bindEvent($element->getId(), 'change', $code);
                  }
                }
                else {
                  Helper::bindEvent($element->getId(), 'change', $js);
                }
              }
            }

            if ($element->getElementType() == 'button' && $element->getType() == self::FORM_ELEMENT_TYPE_SUBMIT) {
              Helper::addJavascriptCode("FormSubmitButton.push('" . $element->getId() . "');");
            }

            // Bind events.
            $events = $element->getEvents();
            Helper::bindEvents($element->getId(), $events);
          }
        }
      }
    }
  }

  protected function returnHTMLAttributes() {
    $output = '';
  
    foreach ($this->attributes as $name => $value) {
      if ($value != NULL) {
        if (in_array($name, $this->availableAttributes)) {
          $output .= ' ' . $name . "='" . $value . "'";
        }
      }
    }

    return $output;
  }

  protected function returnHTMLDescription() {
    $output = '';

    if ($this->description != NULL) {
      $output .= "<div id='form-element-description-" . $this->getId() . "' class='form-element-description'>";
      $output .= $this->description;
      $output .= "</div>";
    }

    return $output;
  }

  protected function returnHTMLDivBegin() {
    return "<div id='form-element-" . $this->getId() . "' class='form-element form-element-" . $this->elementType . "'>";
  }

  protected function returnHTMLDivEnd() {
    return '</div>';
  }

  protected function returnHTMLErrors() {
    $output = '';

    if (count($this->errors)) {
      $output .= "<div id='form-error-group-" . $this->getId() . "' class='form-error-group'>";

      foreach ($this->errors as $error) {
        $output .= "<div class='form-individual-error'>" . $error . "</div>";
      }

      $output .= '</div>';
    }

    return $output;
  }

  protected function returnHTMLErrorsInline() {
    $output = '';

    $validate = FALSE;
    foreach ($this->validators as $validator => $run) {
      if ($run) {
        $validate = TRUE;
        break;
      }
    }

    if ($validate) {
      $output .= "<div id='form-error-group-" . $this->getId() . "' style='display: none;' class='form-error-group'>";

      foreach ($this->validators as $validator => $run) {
        if ($run) {
          switch ($validator) {
            case 'custom':
              $i = 1;
              foreach ($this->functions as $_function => $error) {
                $output .= "<div id='form-individual-error-" . $validator . "-" . $this->getId() . "' class='form-individual-error' style='display: none;'>";
                $output .= $error;
                $output .= '</div>';

                $i++;
              }
              break;

            case 'existance':
							$output .= "<div id='form-individual-error-" . $validator . "-" . $this->getId() . "' class='form-individual-error' style='display: none;'>";
              $output .= sprintf($this->validatorErrors['existance'], $this->determineLabel());
              $output .= '</div>';
              break;

            case 'file_type':
							$output .= "<div id='form-individual-error-" . $validator . "-" . $this->getId() . "' class='form-individual-error' style='display: none;'>";
              $output .= sprintf($this->validatorErrors["file_type"], $this->determineLabel(), implode(", ", array_keys($this->fileTypes)));
              $output .= '</div>';
              break;

            case 'length_long':
							$output .= "<div id='form-individual-error-" . $validator . "-" . $this->getId() . "' class='form-individual-error' style='display: none;'>";
              $output .= sprintf($this->validatorErrors["length_long"], $this->determineLabel(), $this->lengthLong);
              $output .= '</div>';
              break;
                           
            case "length_short":
							$output .= "<div id='form-individual-error-" . $validator . "-" . $this->getId() . "' class='form-individual-error' style='display: none;'>";
              $output .= sprintf($this->validatorErrors["length_short"], $this->determineLabel(), $this->lengthShort);
              $output .= '</div>';
              break;

            case "match":
							$i = 1;
              foreach ($this->matches as $match => $label) {
                $output .= "<div id='form-individual-error-" . $validator . $i . "-" . $this->getId() . "' class='form-individual-error' style='display: none;'>";
                $output .= sprintf($this->validatorErrors["match"][$match], $this->determineLabel(), $label);
                $output .= '</div>';

                $i++;
              }
              foreach ($this->notMatches as $match => $label) {
                  $output .= "<div id='form-individual-error-" . $validator . $i . "-" . $this->getId() . "' class='form-individual-error' style='display: none;'>";
                  $output .= sprintf($this->validatorErrors["not_match"][$match], $this->determineLabel(), $label);
                  $output .= '</div>';

                  $i++;
              }
              break;

            case "maximum_checked":
							$output .= "<div id='form-individual-error-" . $validator . "-" . $this->getId() . "' class='form-individual-error' style='display: none;'>";
              $output .= sprintf($this->validatorErrors["maximum_checked"], $this->determineLabel(), $this->maximumChecked);
              $output .= '</div>';
              break;

            case "maximum_selected":
							$output .= "<div id='form-individual-error-" . $validator . "-" . $this->getId() . "' class='form-individual-error' style='display: none;'>";
              $output .= sprintf($this->validatorErrors["maximum_selected"], $this->determineLabel(), $this->maximumSelected);
              $output .= '</div>';
              break;

            case "minimum_checked":
							$output .= "<div id='form-individual-error-" . $validator . "-" . $this->getId() . "' class='form-individual-error' style='display: none;'>";
              $output .= sprintf($this->validatorErrors["minimum_checked"], $this->determineLabel(), $this->minimumChecked);
              $output .= '</div>';
              break;

            case "minimum_selected":
							$output .= "<div id='form-individual-error-" . $validator . "-" . $this->getId() . "' class='form-individual-error' style='display: none;'>";
              $output .= sprintf($this->validatorErrors["minimum_selected"], $this->determineLabel(), $this->minimumSelected);
              $output .= '</div>';
              break;

            case "pattern":
              $i = 1;
              foreach ($this->patternsMatch as $_function => $error) {
                $output .= "<div id='form-individual-error-" . $validator . $i . "-" . $this->getId() . "' class='form-individual-error' style='display: none;'>";
                $output .= $error;
                $output .= '</div>';

                $i++;
              }
              foreach ($this->patternsNotMatch as $_function => $error) {
                  $output .= "<div id='form-individual-error-" . $validator . $i . "-" . $this->getId() . "' class='form-individual-error' style='display: none;'>";
                  $output .= $error;
                  $output .= '</div>';

                  $i++;
              }
              break;
          }
        }
      }

      $output .= "</div>";
    }

    return $output;
  }

  protected function returnHTMLLabelLeft(Base $parent) {
    $output = '';

    if ($this->label != NULL) {
      // set the "for" attribute here so the "id" attribute of the parent
      // has been validated by _validateConfig()
      $this->label->setFor($parent->getId());

      if ($this->labelAlign == self::FORM_ELEMENT_LABEL_ALIGN_LEFT) {
        $output .= $this->label->returnHTML();
      }
    }

    return $output;
  }

  protected function returnHTMLLabelRight(Base $parent) {
    $output = '';

    if ($this->label != NULL) {
      // set the "for" attribute here so the "id" attribute of the parent
      // has been validated by _validateConfig()
      $this->label->setFor($parent->getId());

      if ($this->labelAlign == self::FORM_ELEMENT_LABEL_ALIGN_RIGHT) {
        $output .= $this->label->returnHTML();
      }
    }

    return $output;
  }

  protected function returnHTMLOptions(array $options = []) {
    $output = '';

    $options = empty($options) ? $this->options : $options;
    foreach ($options as $value => $label) {
      if (is_array($label)) {
        $output .= "<optgroup label='" . $value . "'>";
        $output .= $this->returnHTMLOptions($label);
      }
      else {
        $output .= "<option value='" . $value . "'";

        if (gettype($this->selected) == "array") {
          foreach ($this->selected as $selected_value) {
            //quoting $value is required or 'all' == 0 becomes true (something to do with identity/equality)
            if ($selected_value == "$value") {
              $output .= " selected='selected'";
            }
          }
        }
        else {
          //quoting $value is required or 'all' == 0 becomes true (something to do with identity/equality)
          if ($this->selected == "$value") {
            $output .= " selected='selected'";
          }
        }

        $output .= ">" . $label . "</option>";
      }
    }

    return $output;
  }

  protected function returnHTMLPreText() {
    $output = '';

    if ($this->pretext != NULL) {
      $output .= $this->pretext;
    }

    return $output;
  }

  protected function returnHTMLPostText() {
    $output = '';

    if ($this->posttext != NULL) {
      $output .= $this->posttext;
    }

    return $output;
  }

  protected function returnHTMLRequired() {
    $output = '';

    if ($this->validators['existance']) {
      $output .= "<span class='form-element-required'>*</span>";
    }

    return $output;
  }

  protected function setAttribute(string $name, mixed $value) {
    $this->attributes[$name] = $value;
    return $this;
  }

  protected function setEvent(string $name, mixed $value) {
    $this->events[$name] = $value;
    return $this;
  }

  protected function validateConfigAccesskey() {
    if ($this->getAccesskey() != NULL && strlen($this->getAccesskey()) != 1) {
      $this->errors[] = "Config Error: Attribute 'accesskey' must only be 1 character in length";
    }

    return $this;
  }

  protected function validateConfigChecked() {
    if ($this->getChecked() != NULL && $this->getChecked() != self::FORM_ELEMENT_CHECKED) {
        $this->errors[] = "Config Error: Attribute 'checked' must be 'checked' or NULL";
    }

    return $this;
  }

  protected function validateConfigCols() {
    if ($this->getCols() != NULL && !is_numeric($this->getCols())) {
      $this->errors[] = "Config Error: Attribute 'cols' must be numeric";
    }

    return $this;
  }

  protected function validateConfigDir() {
    if ($this->getDir() != NULL && $this->getDir() != self::FORM_ELEMENT_DIR_LTR && $this->getDir() != self::FORM_ELEMENT_DIR_RTL) {
      $this->errors[] = "Config Error: Attribute 'dir' must be 'ltr' or 'rtl'";
    }

    return $this;
  }

  protected function validateConfigDisabled() {
    if ($this->getDisabled() != NULL && $this->getDisabled() != self::FORM_ELEMENT_DISABLED) {
      $this->errors[] = "Config Error: Attribute 'disabled' must be 'disabled' or NULL";
    }

    return $this;
  }

  protected function validateConfigMaxlength() {
    if ($this->getMaxlength() != NULL && !is_numeric($this->getMaxlength())) {
      $this->errors[] = "Config Error: Attribute 'maxlength' must be numeric";
    }

    return $this;
  }

  protected function validateConfigMethod() {
    if ($this->getMethod() != NULL && $this->getMethod() != self::FORM_ELEMENT_METHOD_GET && $this->getMethod() != self::FORM_ELEMENT_METHOD_POST) {
      $this->errors[] = "Config Error: Attribute 'method' must be either 'get' or 'post'";
    }

    return $this;
  }

  protected function validateConfigMultiple() {
    if ($this->getMultiple() != NULL && $this->getMultiple() != self::FORM_ELEMENT_MULTIPLE) {
      $this->errors[] = "Config Error: Attribute 'multiple' must be 'multiple' or NULL";
    }

    return $this;
  }

  protected function validateConfigReadonly() {
    if ($this->getReadonly() != NULL && $this->getReadonly() != self::FORM_ELEMENT_READONLY) {
      $this->errors[] = "Config Error: Attribute 'readonly' must be 'readonly' or NULL";
    }

    return $this;
  }

  protected function validateConfigRows() {
    if ($this->getRows() != NULL && !is_numeric($this->getRows())) {
      $this->errors[] = "Config Error: Attribute 'rows' must be numeric";
    }

    return $this;
  }

  protected function validateConfigSize() {
    if ($this->getSize() != NULL && !is_numeric($this->getSize())) {
      $this->errors[] = "Config Error: Attribute 'size' must be numeric";
    }

    return $this;
  }

  protected function validateConfigTabindex() {
    if ($this->getTabindex() != NULL && !is_numeric($this->getTabindex())) {
      $this->errors[] = "Config Error: Attribute 'tabindex' must be numeric";
    }

    return $this;
  }

  protected function validateValueCustom() {
    if ($this->validateValueExistance(FALSE)) {
      foreach ($this->functions as $function => $error) {
        if (!call_user_func($function, Env::filterVariable($_REQUEST[$this->getName()]))) {
          $this->errors[] = $error;

          return FALSE;
        }
      }
    }

    return TRUE;
  }

  protected function validateValueExistance(bool $set_error = TRUE) {
    if (
      (!isset($_REQUEST[$this->getName()]) && !isset($_FILES[$this->getName()])) ||
      (isset($_REQUEST[$this->getName()]) && $_REQUEST[$this->getName()] == '') ||
      (isset($_FILES[$this->getName()]) && $_FILES[$this->getName()]['name'] == '')
    ) {
      if ($set_error) {
        $this->errors[] = sprintf($this->validatorErrors["existance"], $this->determineLabel());
      }
      else {
        return FALSE;
      }
    }

    return TRUE;
  }

  protected function validateValueFileTypes() {
    if ($this->validateValueExistance(FALSE)) {
      $file_parts = explode(".", basename($_FILES[$this->getName()]['name']));
      $file_type = strtolower($file_parts[count($file_parts) - 1]);

      foreach ($this->fileTypes as $type => $test) {
        if ($file_type == $type) {
          return TRUE;
        }
      }
      $this->errors[] = sprintf($this->validatorErrors['file_type'], $this->determineLabel(), implode(', ', array_keys($this->fileTypes)));

      return FALSE;
    }

    return TRUE;
  }

  protected function validateValueLengthLong() {
    if ($this->validateValueExistance(FALSE)) {
      if (strlen($_REQUEST[$this->getName()]) > $this->lengthLong) {
        $this->errors[] = sprintf($this->validatorErrors['length_long'], $this->determineLabel(), $this->lengthLong);

        return FALSE;
      }
    }

		return TRUE;
  }

  protected function validateValueLengthShort() {
    if ($this->validateValueExistance(FALSE)) {
      if (strlen($_REQUEST[$this->getName()]) < $this->lengthShort) {
        $this->errors[] = sprintf($this->validatorErrors['length_short'], $this->determineLabel(), $this->lengthShort);

        return FALSE;
      }
    }

    return TRUE;
  }

  protected function validateValueMatch() {
    if ($this->validateValueExistance(FALSE)) {
      foreach ($this->matches as $name => $label) {
        if ($_REQUEST[$this->getName()] != $_REQUEST[$name]) {
          $this->errors[] = sprintf($this->validatorErrors['match'][$name], $this->determineLabel(), $label);

          return FALSE;
        }
      }

      foreach ($this->notMatches as $name => $label) {
        if ($_REQUEST[$this->getName()] == $_REQUEST[$name]) {
          $this->errors[] = sprintf($this->validatorErrors['not_match'][$name], $this->determineLabel(), $label);

          return FALSE;
        }
      }
    }

    return TRUE;
  }

  protected function validateValueMaximumChecked() {
    if ($this->validateValueExistance(FALSE)) {
      if (count($_REQUEST[$this->getName()]) > $this->maximumChecked) {
        $this->errors[] = sprintf($this->validatorErrors['maximum_checked'], $this->determineLabel(), $this->maximumChecked);

        return FALSE;
      }
    }

    return TRUE;
  }

  protected function validateValueMaximumSelected() {
    if ($this->validateValueExistance(FALSE)) {
      if (count($_REQUEST[$this->getName()]) > $this->maximumSelected) {
        $this->errors[] = sprintf($this->validatorErrors['maximum_selected'], $this->determineLabel(), $this->maximumSelected);

        return FALSE;
      }
    }

    return TRUE;
  }

  protected function validateValueMinimumChecked() {
    if ($this->validateValueExistance(FALSE)) {
      if (count($_REQUEST[$this->getName()]) < $this->minimumChecked) {
        $this->errors[] = sprintf($this->validatorErrors['minimum_checked'], $this->determineLabel(), $this->minimumChecked);

        return FALSE;
      }
    }

    return TRUE;
  }

  protected function validateValueMinimumSelected() {
    if ($this->validateValueExistance(FALSE)) {
      if (count($_REQUEST[$this->getName()]) < $this->minimumSelected) {
        $this->errors[] = sprintf($this->validatorErrors['minimum_selected'], $this->determineLabel(), $this->minimumSelected);

        return FALSE;
      }
    }

    return TRUE;
  }

  protected function validateValuePattern() {
    if ($this->validateValueExistance(FALSE)) {
      foreach ($this->patternsMatch as $pattern => $error) {
        if (preg_match($pattern, $_REQUEST[$this->getName()]) == 0) {
          $this->errors[] = $error;

          return FALSE;
        }
      }
      foreach ($this->patternsNotMatch as $pattern => $error) {
        if (preg_match($pattern, $_REQUEST[$this->getName()]) > 0) {
          $this->errors[] = $error;

          return FALSE;
        }
      }
    }

    return TRUE;
  }

  protected function validatorJs(string $type) {
		switch ($type) {
			case 'custom':
				break;

			case 'existance':
        return $this->validatorJsSingle($type, 'true');

			case 'file_type':
        return $this->validatorJsSingle($type, "['" . implode("', '", array_keys($this->fileTypes)) . "']");

			case 'length_long':
        return $this->validatorJsSingle($type, "'" . $this->lengthLong . "'");

			case 'length_short':
        return $this->validatorJsSingle($type, "'" . $this->lengthShort . "'");

      case "match":
				$js = [];
				$i = 1;
				foreach ($this->matches as $match => $label) {
          $js_single = $this->validatorJsSingle($type, "'" . $match . "', " . $i . ", true");
					$js[] = $js_single;
					Helper::bindEvent($match, 'blur', $js_single);
					$i++;
				}
				foreach ($this->notMatches as $match => $label) {
          $js_single = $this->validatorJsSingle($type, "'" . $match . "', " . $i . ", false");
					$js[] = $js_single;
					Helper::bindEvent($match, 'blur', $js_single);
					$i++;
				}
				return $js;

			case 'maximum_checked':
				foreach ($this->elements as $element) {
					Helper::bindEvent($element->getId(), 'change', $this->validatorJsSingle($type, "'" . $this->maximumChecked . "'"));
				}
				return "";

			case "maximum_selected":
        return $this->validatorJsSingle($type, "'" . $this->maximumSelected . "'");

			case "minimum_checked":
				foreach ($this->elements as $element) {
					Helper::bindEvent($element->getId(), "change", $this->validatorJsSingle($type, "'" . $this->maximumChecked . "'"));
				}
				return "";

			case "minimum_selected":
        return $this->validatorJsSingle($type, "'" . $this->maximumSelected . "'");

			case "pattern":
				$js = array();
				$i  = 1;
				foreach ($this->patternsMatch as $pattern => $error) {
					$js[] = $this->validatorJsSingle($type, $i . ', ' . $pattern . ', true');
					$i++;
				}
				foreach ($this->patternsNotMatch as $pattern => $error) {
					$js[] = $this->validatorJsSingle($type, $i . ', ' . $pattern . ', false');
					$i++;
				}
				return $js;
		}
	}

  protected function validatorJsSingle(string $type, string $value) {
    return 'Form_validate_' . $type . "('" . $this->getId() . "', " . $value . ');';
  }

}