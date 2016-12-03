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
	// get portfolio item table linked to the portfolio types
	public function getPortfolioTypeTable() {
		$sm = $this->getServiceLocator();
		return $sm->get('Portfolio\Model\PortfolioTypeTable');
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
		/*$cache = $this->getServiceLocator()->get('cache');
		if (!$cache->hasItem('test')) {
			$cache->addItem('test', '123');
		} else {
			echo $cache->getItem('test');
		}
		die;*/

		$table = $this->getPortfolioTable();
	    
	    // fetch a paginated array of portfolio items
	    $gallery_items = $table->fetchAll($this->params()->fromQuery('sortby', null));
		$gallery_items->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
    	$gallery_items->setItemCountPerPage(5);

    	$this->getServiceLocator()->get('ViewHelperManager')->get('HeadTitle')->set('Matthew Chan - Web Portfolio');
    	$view = new ViewModel();
	    $view->setVariables(
	    	array(
	    		'action' => '',
	    		'gallery_items' => $gallery_items,
	    		'current_page' => (int)$this->params()->fromQuery('page', 1)
	    	)
	    );
	    return $view;
	}

	public function personalAction() {
		$table = $this->getPortfolioTypeTable();
	    
	    // fetch a paginated array of portfolio items
	    $gallery_items = $table->fetchAllOfType($this->params()->fromQuery('sortby', null), "Personal Project");
		$gallery_items->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
    	$gallery_items->setItemCountPerPage(5);

    	$this->getServiceLocator()->get('ViewHelperManager')->get('HeadTitle')->set('Matthew Chan - Personal Projects');
    	$view = new ViewModel();
    	$view->setTemplate('portfolio/index/index.phtml');
	    $view->setVariables(
	    	array(
	    		'action' => 'personal',
	    		'gallery_items' => $gallery_items,
	    		'current_page' => (int)$this->params()->fromQuery('page', 1)
	    	)
	    );
	    return $view;
	}

	public function workAction() {
		$table = $this->getPortfolioTypeTable();
	    
	    // fetch a paginated array of portfolio items
	    $gallery_items = $table->fetchAllOfType($this->params()->fromQuery('sortby', null), "Work History");
		$gallery_items->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
    	$gallery_items->setItemCountPerPage(5);

    	$this->getServiceLocator()->get('ViewHelperManager')->get('HeadTitle')->set('Matthew Chan - Work History');
    	$view = new ViewModel();
    	$view->setTemplate('portfolio/index/index.phtml');
	    $view->setVariables(
	    	array(
	    		'action' => 'work',
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

    	$this->getServiceLocator()->get('ViewHelperManager')->get('HeadTitle')->set('Matthew Chan - ' . $gallery_item->title);
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
	    $uri = $this->getRequest()->getUri();
	    $scheme = $uri->getScheme();
	    $host = $uri->getHost();
		$prevRoute = str_replace("{$scheme}://{$host}", '', $fullPrevRoute);

		// if not from another route in this application
		if (!$prevRoute) {
			$fullPrevRoute = null;
			$prevRoute = '/';
		}

		$id = $this->params()->fromRoute('id', null);
		if (!$id) {
			$this->getResponse()->setStatusCode(404);
			return;
		}

		$item_tag_table = $this->getItemTagTable();
		$tag_table = $this->getTagTable();

		// fetch all items with this tag id
		$gallery_items = $item_tag_table->fetchItemsForTag($id);
		// fetch the tag name belonging to this id (so we may display it on the page)
		$tag_name = $tag_table->fetchOne($id)->name;

    	$this->getServiceLocator()->get('ViewHelperManager')->get('HeadTitle')->set('Matthew Chan - ' . $tag_name);
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

	public function contactAction() {
		$this->getServiceLocator()->get('ViewHelperManager')->get('HeadTitle')->set('Matthew Chan - Contact Me');
		$view = new ViewModel();
	    return $view;
	}
}