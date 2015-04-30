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
use Zend\Paginator\Paginator;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;

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
	// order is only used for pagination (at this time)
	public function fetchAll($pagination, $order = null, $value = null, $columnname = null) {
		// based on $order parameter, the SQL statement retrieves from the db
		// with a sorted array of some kind
		$sortRule = null;
		// by default, if $order parameter null we get the ordinary list of rows
		switch ($order) {
			case null:
				$sortRule = 'id ASC';
				break;
			case 'alpha_asc':
				$sortRule = 'title ASC';
				break;
			case 'alpha_desc':
				$sortRule = 'title DESC';
				break;
			case 'date_asc':
				$sortRule = 'start ASC';
				break;
			case 'date_desc':
				$sortRule = 'start DESC';
				break;
			default:
				$sortRule = 'id ASC';
				break;
		}

		// if pagination is true, then use Zend paginator
		// we have to create a db adapter object
		if ($pagination) {
			 // create a new Select object for the table we want to fetch from
             $select = new Select();
             $select->from($this->table)->order($sortRule);

             // set our object type (passed into constructor along with table name) 
             // as the prototype for this result set
             $resultSetPrototype = new ResultSet();
             $resultSetPrototype->setArrayObjectPrototype(new $this->objname());

             // create a new pagination adapter object
             $paginatorAdapter = new DbSelect($select, $this->getAdapter(), $resultSetPrototype);

			return new Paginator($paginatorAdapter);
		}

		// else if not pagination
		// return a regular un-paginated array of objects
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