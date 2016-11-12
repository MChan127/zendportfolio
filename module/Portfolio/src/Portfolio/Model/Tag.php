<?php

namespace Portfolio\Model;

class Tag {
	public function __construct($data = null) {
		if ($data) {
			foreach ($data as $key => $value) {
				$this->$key = (!empty($data[$key])) ? $value : null;
			}
		}
	}

	public function exchangeArray($data) {
		foreach ($data as $key => $value) {
			$this->$key = (!empty($data[$key])) ? $value : null;
		}
	}
}