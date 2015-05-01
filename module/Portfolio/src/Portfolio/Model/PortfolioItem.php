<?php

namespace Portfolio\Model;

class PortfolioItem {
	public $id;
	public $title;
	public $start;
	public $end;
	public $link;
	public $source;
	public $desc;
	public $img_filename;

	private $monthNames = array(
		1 => 'January',
		2 => 'February',
		3 => 'March', 
		4 => 'April', 
		5 => 'May',
		6 => 'June',
		7 => 'July',
		8 => 'August',
		9 => 'September',
		10 => 'October',
		11 => 'November',
		12 => 'December'
	);

	public function __construct($data = null) {
		if ($data) {
			foreach ($data as $key => $value) {
				// if the column is one of the date types, call the parser
				if ($key == 'start' || $key == 'end') {
					$value = $this->parseDate($value);
				}

				$this->$key = (!empty($data[$key]) || $key == 'end') ? $value : null;
			}
		}
	}

	// parse date types to a more appropriate string
	private function parseDate($date) {
		// if blank, means this is an 'ongoing' end date
		if ($date == null) {
			return 'n/a (Present)';
		}

		$parsed_date = date_parse($date);
		// if the day is 0, return only the month and year
		if ($parsed_date['day'] == 0) {
			return $this->getMonth($parsed_date['month']) . ' ' . $parsed_date['year'];
		} else {
			return $this->getMonth($parsed_date['month']) . ' ' 
			. $parsed_date['day'] . ', ' . $parsed_date['year'];
		}
	}

	private function getMonth($monthInt) {
		return $this->monthNames[$monthInt];
	}

	public function exchangeArray($data) {
		foreach ($data as $key => $value) {
			$this->$key = (!empty($data[$key])) ? $value : null;
		}
	}
}