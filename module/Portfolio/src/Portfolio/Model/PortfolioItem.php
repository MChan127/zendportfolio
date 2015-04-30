<?php

namespace Portfolio\Model;

class PortfolioItem {
	public $id;
	public $title;
	public $start;
	public $end;
	public $link;
	public $desc;
	public $img_filename;

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