<?php

namespace Portfolio\Model;

class Tag {
	public function __construct($adapter, $data = null) {
		$this->adapter = $adapter;

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