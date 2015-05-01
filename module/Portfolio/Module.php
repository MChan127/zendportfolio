<?php
namespace Portfolio;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use Portfolio\Model\SimpleTable;
use Portfolio\Model\JoinedTable;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
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
                'Portfolio\Model\TagTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new JoinedTable($dbAdapter);
                    return $table;
                }
            ),
        );
    }
}
