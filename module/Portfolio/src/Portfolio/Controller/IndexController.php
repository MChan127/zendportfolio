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
	public function getItemTagTable() {
		$sm = $this->getServiceLocator();
		return $sm->get('Portfolio\Model\ItemTagTable');
	}
	// get tags table (involves only tags, not items)
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
		$item_tag_table = $this->getItemTagTable();

		// fetch this one row from the database with this id
		$gallery_item = $item_table->fetchOne($id);

		// also fetch all the tags for this item
		$item_tags = $item_tag_table->fetchTagsForItem($id);

		$view = new ViewModel();
	    $view->setVariables(
	    	array(
	    		'item' => $gallery_item,
	    		'tags' => $item_tags
	    	)
	    );
	    return $view;
	}

	// user is viewing a list of all portfolio items that have a particular tag
	public function tagAction() {
		$fullPrevRoute = $this->getRequest()->getServer('HTTP_REFERER');
		$prevRoute = str_replace("http://portfolio-zend.com", "", $fullPrevRoute);

		// if not from another route in this application
		if (!$prevRoute) {
			$fullPrevRoute = null;
			$prevRoute = '/';
		}

		$id = $this->params()->fromRoute('id', 0);

		$item_tag_table = $this->getItemTagTable();
		$tag_table = $this->getTagTable();

		// fetch all items with this tag id
		$gallery_items = $item_tag_table->fetchItemsForTag($id);
		// fetch the tag name belonging to this id (so we may display it on the page)
		$tag_name = $tag_table->fetchOne($id)->name;

		$view = new ViewModel();
	    $view->setVariables(
	    	array(
	    		'gallery_items' => $gallery_items,
	    		'tag_name' => $tag_name,
	    		'previous_route' => $prevRoute
	    	)
	    );
	    return $view;
	}
}