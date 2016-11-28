<?php

namespace Portfolio\Model;
use \Zend\Db\ResultSet\ResultSet;

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

	public function __construct($adapter, $data = null) {
		$this->adapter = $adapter;

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
				if ($key == 'portfolio_type') {
					$value = $this->getPortfolioTypeName($value);
				}

				$this->$key = $value;
			}
		}
	}

	public function exchangeArray($data) {
		foreach ($data as $key => $value) {
			$this->$key = (!empty($data[$key])) ? $value : null;
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

	// given the portfolio type id, return the name
	public function getPortfolioTypeName($id) {
		$query = "SELECT name FROM portfolio_type WHERE id = :id;";
		$statement = $this->adapter->createStatement($query);
		$statement->prepare($query);
		$result = $statement->execute(array('id' => $id));

		$resultSet = new ResultSet();
		$resultSet = $resultSet->initialize($result)->toArray();
		if (count($resultSet) > 0) {
			return $resultSet[0]['name'];
		} else {
			return '';
		}
	}
}