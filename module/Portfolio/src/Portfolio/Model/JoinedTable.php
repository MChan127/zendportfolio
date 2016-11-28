<?php
/*
* For cases where two or more tables must be joined in order to access
* more columns. 
* Specifically, at this moment, the use cases are to either fetch all the 
* tags for a portfolio item, or all portfolio items with a particular tag.
* Tables are joined so that we may return the names of these tags/items.
*
* Invoked by the Service Manager.
*/

namespace Portfolio\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Paginator\Paginator;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;

use Portfolio\Model\Tag;
use Portfolio\Model\PortfolioItem;

class JoinedTable extends AbstractTableGateway {
	/*
	* $adapter - db adapter necessary to interact with database
	* $table - name of the table we're querying
	* $objname - name of the single row object (e.g. record, user) to return
	* 	from this class's methods
	*/
	public function __construct($adapter) {
		if (empty($adapter)) {
			throw new Exception('Error: invalid table initialization' .
				'(missing adapter).');
		}

		$this->adapter = $adapter;
	}

	// get all portfolio items of a given type
	public function fetchAllOfType($order = null, $typeName = null) {
		$this->table = 'portfolio_item';

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
		if ($typeName === null) {
			$typeName = "Personal Project";
		}

		 // create a new Select object for the table we want to fetch from
         $select = new Select();
         $select
         	->join(array('t' => 'portfolio_type'), $this->table . '.portfolio_type = t.id', array('name'))
         	->where(array('t.name' => $typeName));
         $select->from($this->table)->order($sortRule);

         // set our object type (passed into constructor along with table name) 
         // as the prototype for this result set
         $resultSetPrototype = new ResultSet();
         $resultSetPrototype->setArrayObjectPrototype(new PortfolioItem($this->adapter));

         // create a new pagination adapter object
         $paginatorAdapter = new DbSelect($select, $this->adapter, $resultSetPrototype);

		return new Paginator($paginatorAdapter);
	}

	// get all tags for this item
	public function fetchTagsForItem($id) {
		$this->table = 'item_tags';

		// create the SQL statement
		$select = new Select();
		$select->from($this->table, array());
		$select->join(array('t' => 'tags'), 'item_tags.tag_id = t.id', array('id', 'name'));
        $select->where(array('item_tags.item_id' => $id));

        // use this TableGateway object itself to execute the statement
        // thus returning the result set
        $resultSet = $this->selectWith($select);

        $objSet = array();

        // loop through results and return each row as an object
		foreach ($resultSet as $row) {
			array_push($objSet, new Tag($this->adapter, $row));
		}

		return $objSet;
	}

	// get all items for this tag
	public function fetchItemsForTag($id) {
        $this->table = 'item_tags';

		$select = new Select();
		$select->from($this->table, array());
		$select->join(array('p' => 'portfolio_item'), 'item_tags.item_id = p.id', array('id', 'title',
			'start', 'end', 'link', 'description', 'img_filename', 'url_key', 'portfolio_type'));
        $select->where(array('item_tags.tag_id' => $id));

        $resultSet = $this->selectWith($select);

        $objSet = array();

        // loop through results and return each row as an object
		foreach ($resultSet as $row) {
			array_push($objSet, new PortfolioItem($this->adapter, $row));
		}

		return $objSet;
	}
}