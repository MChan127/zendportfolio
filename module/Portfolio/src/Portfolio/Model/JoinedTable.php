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
			array_push($objSet, new Tag($row));
		}

		return $objSet;
	}

	// get all items for this tag
	public function fetchItemsForTags($id) {
         $this->table = 'item_tags';

		$select = new Select();
		$select->join(array('p' => 'portfolio_item'), 'item_tags.item_id = p.id', 'p.id', 'p.title',
			'p.start', 'p.end', 'p.link', 'p.description', 'p.img_filename');
        $select->where('item_tags.tag_id = ?', $id);

        $resultSet = $this->select($select);

        $objSet = array();

        // loop through results and return each row as an object
		foreach ($resultSet as $row) {
			array_push($objSet, new Tag($row));
		}

		return $objSet;
	}
}