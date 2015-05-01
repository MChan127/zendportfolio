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

	public function indexAction() {
		//echo (int)file_exists("data/images/anispace.png") . '<br>';
		//var_dump(getimagesize("data/images/anispace.png"));
		//die('');

		$table = $this->getPortfolioTable();
	    
	    // fetch a paginated array of portfolio items
	    $gallery_items = $table->fetchAll(true, $this->params()->fromQuery('sortby', null));
		$gallery_items->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
    	$gallery_items->setItemCountPerPage(6);

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

		$table = $this->getPortfolioTable();

		// fetch this one row from the database with this id
		$gallery_item = $table->fetchOne($id);

		$view = new ViewModel();
	    $view->setVariables(
	    	array(
	    		'gallery_item' => $gallery_item
	    	)
	    );
	    return $view;
	}

	public function tagAction() {
	}

	public function aboutAction() {
	}
}