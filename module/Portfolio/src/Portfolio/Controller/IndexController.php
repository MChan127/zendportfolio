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
		$table = $this->getPortfolioTable();
		$view = new ViewModel();
	    
	    // fetch a paginated array of portfolio items
	    $gallery_items = $table->fetchAll(true, $this->params()->fromQuery('sortby', null));
		$gallery_items->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
    	$gallery_items->setItemCountPerPage(4);

	    $view->setVariables(
	    	array(
	    		'gallery_items' => $gallery_items,
	    		'current_page' => (int)$this->params()->fromQuery('page', 1)
	    	)
	    );
	    return $view;
	}

	public function viewAction() {
	}

	public function tagAction() {
	}

	public function aboutAction() {
	}
}