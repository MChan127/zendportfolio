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
	    $view->setVariables(array('gallery_items' => $table->fetchAll()));
	    return $view;
	}

	public function viewAction() {
	}

	public function tagAction() {
	}

	public function aboutAction() {
	}
}