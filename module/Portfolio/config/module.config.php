<?php

return array(
    // define controllers here
    'controllers' => array(
        'invokables' => array(
            'Portfolio\Controller\Index' => 'Portfolio\Controller\IndexController',
        ),
    ),

    // define different routes here
    // each route is assigned a controller and its own url rules
    // controllers are attached to pages (actions), so effectively
    //     pages are grouped by controller/route
    'router' => array(
        'routes' => array(
            // home route seems to be mandatory--application crashes without it
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Portfolio\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'index' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/[:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Portfolio\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

    // used to determine views folder
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/portfolio/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            'portfolio' => __DIR__ . '/../view',
        ),
    ),
 );