<?php

namespace Portfolio\Model;

class PortfolioItem {
	protected $_purifier;

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
		$this->_purifier = new \HTMLPurifier();

		if ($data) {
			foreach ($data as $key => $value) {
				// if the column is one of the date types, call the parser
				switch($key) {
					case 'start_str':
					case 'end_str':
					case 'updated_str':
						continue;
					case 'start':
						$value = (empty($data['start_str']) ? $this->parseDate($value) : $data['start_str']);
						break;
					case 'end':
						$value = (empty($data['end_str']) ? $this->parseDate($value) : $data['end_str']);
						break;
					case 'updated':
						$value = (empty($data['updated_str']) ? $this->parseDate($value) : $data['updated_str']);
						break;
				}
				if ($key == 'description') {
					$value = $this->_purifier->purify(nl2br($value));
				}

				$this->$key = $value;
			}
		}
	}

	// parse date types to a more appropriate string
	private function parseDate($date) {
		if ($date == null) {
			return '';
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