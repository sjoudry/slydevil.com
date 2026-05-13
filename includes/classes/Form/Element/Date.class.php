<?php

namespace SlyDevil\Form\Element;

class Date extends Base {

	protected array $availableAttributes = [
		'id',
		'name',
		'value',
	];

	protected ?Select $day = NULL;

	protected ?int $date = NULL;

	protected int $delta = 10;

	protected ?Select $month = NULL;

	protected ?Select $year = NULL;

  public function __construct() {
		$this->day = new Select();
		$this->day->setOptions(array_combine(range(1, 31), range(1, 31)));

		$this->month = new Select();
		$this->month->setOptions(
			array_combine(
				range(1, 12),
				[
					'January',
					'February',
					'March',
					'April',
					'May',
					'June',
					'July',
					'August',
					'September',
					'October',
					'November',
					'December',
				]
			)
		);

		$this->year = new Select();

		$this->date = time();

    $this->elementType = 'date';

    return $this;
  }

	public function getDay() {
		return $this->day;
	}

	public function getDelta() {
		return $this->delta;
	}

	public function getMonth() {
		return $this->month;
	}

	public function getYear() {
		return $this->year;
	}

	public function returnHTML() {
		$output  = '';

		$output .= $this->returnHTMLPreText();
		$output .= $this->returnHTMLDivBegin();
		$output .= $this->returnHTMLLabelLeft($this);

		$output .= $this->day->returnHTML();
		$output .= $this->month->returnHTML();
		$output .= $this->year->returnHTML();

		$output .= $this->returnHTMLRequired();
		$output .= $this->returnHTMLLabelRight($this);
		$output .= $this->returnHTMLDescription();
		$output .= $this->returnHTMLDivEnd();
		$output .= $this->returnHTMLPostText();

		return $output;
	}

  public function setDay(Select $value) {
		$this->day = $value;
		return $this;
	}

	public function setDelta(int $value) {
		$this->delta = $value;
		return $this;
	}

	public function setMonth(Select $value) {
		$this->month = $value;
		return $this;
	}

	public function setYear(Select $value) {
		$this->year = $value;
		return $this;
	}

  protected function validateConfig() {
		if (!$this->configValidated) {
			$this->configValidated = TRUE;

      if ($this->getName() == NULL) {
        $this->errors[] = "Config Error: Attribute 'name' is required";
      }
      else {
				if ($this->getValue() == NULL) {
					$this->setvalue(
						[
							'day'   => date('j', $this->date),
							'month' => date('n', $this->date),
							'year'  => date('Y', $this->date),
						]
					);
				}

				if ($this->getId() == NULL) {
					$this->setId($this->getName());
				}

				if ($this->day->getName() == NULL) {
					$this->day->setName($this->getName() . '-day');
				}
				if ($this->day->getSelected() == NULL) {
					$this->day->setSelected($this->getValue()['day']);
				}
				$this->day->validateConfig();

				if ($this->month->getName() == NULL) {
					$this->month->setName($this->getName() . '-month');
				}
				if ($this->month->getSelected() == NULL) {
					$this->month->setSelected($this->getValue()['month']);
				}
				$this->month->validateConfig();

				if ($this->year->getName() == NULL) {
					$this->year->setName($this->getName() . '-year');
				}
				if ($this->year->getOptions() == NULL) {
					$options = [];
					for ($i = ($this->getValue()['year'] - $this->delta); $i <= ($this->getValue()['year'] + $this->delta); $i++) {
						$options[$i] = $i;
					}
					$this->year->setOptions($options);
				}
				if ($this->year->getSelected() == NULL) {
					$this->year->setSelected($this->getValue()['year']);
				}
				$this->year->validateConfig();
			}
    }
	}

}