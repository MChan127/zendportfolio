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

	public function fetchAll($order = null) {
		// based on $order parameter, the SQL statement retrieves from the db
		// with a sorted array of some kind
		$sortRule = null;
		// by default, if $order parameter null we get the ordinary list of rows
		switch ($order) {
			case null:
				$sortRule = 'end DESC';
				break;
			case 'alpha_asc':
				$sortRule = 'title ASC';
				break;
			case 'alpha_desc':
				$sortRule = 'title DESC';
				break;
			case 'date_asc':
				$sortRule = 'end ASC';
				break;
			case 'date_desc':
				$sortRule = 'end DESC';
				break;
			default:
				$sortRule = 'end DESC';
				break;
		}

		 // create a new Select object for the table we want to fetch from
         $select = new Select();
         $select->from($this->table)->order($sortRule);

         // set our object type (passed into constructor along with table name) 
         // as the prototype for this result set
         $resultSetPrototype = new ResultSet();
         $resultSetPrototype->setArrayObjectPrototype(new $this->objname($this->adapter));

         // create a new pagination adapter object
         $paginatorAdapter = new DbSelect($select, $this->adapter, $resultSetPrototype);

		return new Paginator($paginatorAdapter);
	}

	// get one item by an id
	public function fetchOne($id) {
		$resultSet = $this->select(array('id' => $id));
		$row = $resultSet->current();
		
		// if the table entry isn't found, throw an exception
		if (empty($row)) {
			throw new Exception('Item does not exist in table.');
		}

		return new $this->objname($this->adapter, $row);
	}

	// find an item with the url key
	public function fetchItemForUrlKey($key) {
		$resultSet = $this->select(array('url_key' => $key));
		$row = $resultSet->current();
		
		// if the table entry isn't found, throw an exception
		if (empty($row)) {
			return false;
		}

		return new $this->objname($this->adapter, $row);
	}
}