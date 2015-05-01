<?php

namespace Portfolio\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController {
	// get the model/table gateway for the portfolio_item table
	public function getPortfolioTable() {
		$sm = $this->getServiceLocator();
		return $sm->get('Portfolio\Model\PortfolioTable');
	}
	// get item tags table (in which portfolio items are linked to their tags)
	public function getTagTable() {
		$sm = $this->getServiceLocator();
		return $sm->get('Portfolio\Model\TagTable');
	}

	public function indexAction() {
		//echo (int)file_exists("data/images/anispace.png") . '<br>';
		//var_dump(getimagesize("data/images/anispace.png"));
		//die('');

		$table = $this->getPortfolioTable();
	    
	    // fetch a paginated array of portfolio items
	    $gallery_items = $table->fetchAll(true, $this->params()->fromQuery('sortby', null));
		$gallery_items->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
    	$gallery_items->setItemCountPerPage(4);

    	$view = new ViewModel();
	    $view->setVariables(
	    	array(
	    		'gallery_items' => $gallery_items,
	    		'current_page' => (int)$this->params()->fromQuery('page', 1)
	    	)
	    );
	    return $view;
	}

	public function viewAction() {
		// user has decided to view this item with this id
		$id = $this->params()->fromRoute('id', 0);

		$item_table = $this->getPortfolioTable();
		$tag_table = $this->getTagTable();

		// fetch this one row from the database with this id
		$gallery_item = $item_table->fetchOne($id);

		// also fetch all the tags for this item
		$item_tags = $tag_table->fetchTagsForItem($id);

		$view = new ViewModel();
	    $view->setVariables(
	    	array(
	    		'item' => $gallery_item,
	    		'tags' => $item_tags
	    	)
	    );
	    return $view;
	}

	public function tagAction() {
		// user is viewing a list of all portfolio items that have this tag
		$id = $this->params()->fromRoute('id', 0);

		$table = $this->getTagTable();

		// fetch all items with this tag id
		$gallery_items = $table->fetchItemsForTag($id);

		$view = new ViewModel();
	    $view->setVariables(
	    	array(
	    		'gallery_items' => $gallery_items
	    	)
	    );
	    return $view;
	}

	public function aboutAction() {
	}

	public function resumeAction() {
	}
}