<?php
namespace Portfolio;

use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\ModuleManager\ModuleEvent;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use Portfolio\Model\SimpleTable;
use Portfolio\Model\JoinedTable;

class Module implements InitProviderInterface
{
    protected $serviceManager;
    // http://stackoverflow.com/a/34388698
    // get the service manager which at the time of the EVENT_LOAD_MODULES_POST event should contain
    // the database adapters we need
    public function init(ModuleManagerInterface $moduleManager) {
        $eventManager = $moduleManager->getEventManager();
        $eventManager->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, [$this, 'onLoadModulesPost']);
    }
    public function onLoadModulesPost(ModuleEvent $event) {
        $this->serviceManager = $event->getParam('ServiceManager');
    }

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'seoRoute'), 0);
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }
    // when a url key is found that matches a portfolio item, modify the route
    // so that the user is redirected to the portfolio item page
    public function seoRoute(MvcEvent $e) {
        $routeMatch = $e->getRouteMatch();
        $url_key = $routeMatch->getParam('action');

        $table = $this->serviceManager->get('Portfolio\Model\PortfolioTable');
        $item = $table->fetchItemForUrlKey($url_key);
        if ($item) {
            $routeMatch->setParam('action', 'view');
            $routeMatch->setParam('id', $item->id);
        }
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    // the service creates and returns instances of objects when called 
    // by the rest of the application
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Portfolio\Model\PortfolioTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new SimpleTable($dbAdapter, 'portfolio_item', 'Portfolio\\Model\\PortfolioItem');
                    return $table;
                },
                'Portfolio\Model\PortfolioTypeTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new JoinedTable($dbAdapter);
                    return $table;
                },
                'Portfolio\Model\ItemTagTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new JoinedTable($dbAdapter);
                    return $table;
                },
                'Portfolio\Model\TagTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new SimpleTable($dbAdapter, 'tags', 'Portfolio\\Model\\Tag');
                    return $table;
                }
            ),
        );
    }
}
