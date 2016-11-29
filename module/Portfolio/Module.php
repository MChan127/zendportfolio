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
    protected $routesToCache;

    // http://stackoverflow.com/a/34388698
    // get the service manager which at the time of the EVENT_LOAD_MODULES_POST event should contain
    // the database adapters we need
    public function init(ModuleManagerInterface $moduleManager) {
        $this->routesToCache = array(
            'index/index', 'index/view', 'index/tag', 'index/personal', 'index/work'
        );

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
        $eventManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'loadPageCache'), -1000);
        $eventManager->attach(MvcEvent::EVENT_FINISH, array($this, 'savePageCache'), -1000);
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
    // full page caching
    public function loadPageCache(MvcEvent $e) {
        $routeMatch = $e->getRouteMatch();
        $routeName = strtolower(str_replace("Portfolio\\Controller\\", '', $routeMatch->getParam('controller'))
            . '/' . $routeMatch->getParam('action'));
        if (!in_array($routeName, $this->routesToCache))
            return;

        $routeName = str_replace('/', '_', $routeName);
        $this->getRouteParams($routeName, $routeMatch);

        $cache = $this->serviceManager->get('cache');
        if ($cache->hasItem('fpc-' . $routeName)) {
            $response = $e->getResponse();
            $content = $cache->getItem('fpc-' . $routeName);
            if ($content !== null) {
                $response->setContent($content);
                return $response;
            }
        }
    }
    public function savePageCache(MvcEvent $e) {
        $routeMatch = $e->getRouteMatch();
        $routeName = strtolower(str_replace("Portfolio\\Controller\\", '', $routeMatch->getParam('controller'))
            . '/' . $routeMatch->getParam('action'));
        if (!in_array($routeName, $this->routesToCache))
            return;

        $routeName = str_replace('/', '_', $routeName);
        $this->getRouteParams($routeName, $routeMatch);

        $cache = $this->serviceManager->get('cache');
        if ($cache->hasItem('fpc-' . $routeName))
            return;

        $response = $e->getResponse();
        $content = $response->getContent();
        $cache->setItem('fpc-' . $routeName, $content);
    }
    private function getRouteParams(&$routeName, $routeMatch) {
        switch($routeName) {
            case 'index_index':
            case 'index_personal':
            case 'index_work':
                if (isset($_GET['page']) && ctype_digit((string)$_GET['page'])) {
                    $routeName .= '_page' . $_GET['page'];
                } else {
                    $routeName .= '_page0';
                }
                if (isset($_GET['sortby']) && preg_match('/^[a-z_]+$/', $_GET['sortby']) !== false) {
                    $routeName .= '_sortby_' . $_GET['sortby'];
                }
                break;
            case 'index_tag':
            case 'index_view':
                $id = $routeMatch->getParam('id');
                $routeName .= '_id' . $id;
                break;
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
