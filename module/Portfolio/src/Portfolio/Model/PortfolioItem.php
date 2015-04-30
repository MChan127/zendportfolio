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

	public function __construct($data) {
		foreach ($data as $key => $value) {
			$this->$key = (!empty($data[$key])) ? $value : null;
		}
	}
}