<?php
/*
* General set of simple methods/operations to perform against a db
* table (fetching one result or many/all).
*
* Invoked by the Service Manager, at which point an appropriate
* TableGateway object (corresponding to a particular db table) is 
* passed through this class's constructor.
*/

namespace Portfolio\Model;

use Zend\Db\TableGateway\AbstractTableGateway;

use Portfolio\Model\PortfolioItem;

class SimpleTable extends AbstractTableGateway {
	private $objname;

	/*
	* $adapter - db adapter necessary to interact with database
	* $table - name of the table we're querying
	* $objname - name of the single row object (e.g. record, user) to return
	* 	from this class's methods
	*/
	public function __construct($adapter, $table, $objname) {
		if (empty($adapter) || empty($table) || empty($objname)) {
			throw new Exception('Error: invalid table initialization' .
				'(missing adapter, table name and/or object name).');
		}

		$this->adapter = $adapter;
		$this->table = $table;
		$this->objname = $objname;
	}

	// get all items that have $value underneath $columnname
	// if insufficient arguments given, fetches all rows
	public function fetchAll($value = null, $columnname = null) {
		$objSet = array();

		if ($value === null || $columnname === null) {
			$resultSet = $this->select();
		} else {
			$resultSet = $this->select(array($columnname => $value));
		}

		// loop through results and return each row as an object
		foreach ($resultSet as $row) {
			array_push($objSet, new $this->objname($row));
		}

		return $objSet;
	}

	// get one item by an id
	public function fetchOne($id) {
		$resultSet = $this->select(array('id' => $id));
		$row = $resultSet->current();
		
		// if the table entry isn't found, throw an exception
		if (empty($row)) {
			throw new Exception('Item does not exist in table.');
		}

		return new $this->objname($row);
	}
}