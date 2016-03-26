<?php
namespace Oldtimers;
return array(
    
    /*
     * All controllers must be registered in the following format:
     * Canonical name => Namespaced class
     */
    'controllers' => array(
        'invokables' => array(
            'Oldtimers\Controller\Index' => 'Oldtimers\Controller\IndexController'
        ),
    ),
        
    'router' => array(
            'routes' => array(
                    'home' => array(
                            'type' => 'Zend\Mvc\Router\Http\Literal',
                            'options' => array(
                                    'route'    => '/',
                                    'defaults' => array(
                                            'controller' => 'Oldtimers\Controller\Index',
                                            'action'     => 'list',
                                    ),
                            ),
                            'may_terminate' => true,
                            'child_routes' => array(
                                    'default' => array(
                                            'type' => 'Segment',
                                            'options' => array(
                                                    'route' => '[:controller[/:action]]',
                                                    'constraints' => array(
                                                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                                                    ),
                                                    'defaults' => array(
                                                            'action' => 'index',
                                                            '__NAMESPACE__' => 'Oldtimers\Controller'
                                                    )
                                            )
                                    )
                            )
                    ),
            		'new' => array(
            				'type' => 'Zend\Mvc\Router\Http\Literal',
            				'options' => array(
            						'route'    => '/new',
            						'defaults' => array(
            								'controller' => 'Oldtimers\Controller\Index',
            								'action'     => 'newAdvertisement',
            						),
            				),
            				'may_terminate' => true,
            		),
            		'advertisement' => array(
                        	'type' => 'Zend\Mvc\Router\Http\Segment',
                        	'options' => array(
                            		'route' => '/advertisement/[:id]',
                            		'constraints' => array(
                                		'id' => '[a-zA-Z0-9_-]+',
                            		),
                            		'defaults' => array(
	                                	'controller' => 'Oldtimers\Controller\Index',
	            						'action'     => 'advertisement',
                            		),
                        	),
            				'may_terminate' => true,
                    ),
            		'import' => array(
            				'type' => 'Zend\Mvc\Router\Http\Segment',
            				'options' => array(
            						'route' => '/import/[:from]/[:to]',
            						'constraints' => array(
            								'from' => '[0-9]+',
            								'to' => '[0-9]+',
            						),
            						'defaults' => array(
            								'controller' => 'Oldtimers\Controller\Index',
            								'action'     => 'import',
            						),
            				),
            		)
            )
    ),
    
    /*
     * A basic route called entry which will route on /entry
     * Further, child routes allow for /entry/action/id to be mapped out 
     */
    
    /*
     * Set this to say that we're using JSON instead of HTML. This actually will
     * still work even without this, in our example
     */
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'oldtimers/index/list' => __DIR__ . '/../view/oldtimers/index/list.phtml',
            'oldtimers/index/new-advertisement' => __DIR__ . '/../view/oldtimers/index/new-advertisement.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    
    /*
     * MUST register the Entity with doctrine ODM_Driver or else you will get an Exception
     * referring to "Demo\Entity\Entry cannot be found in the chained namespaces"
     */
    'doctrine' => array(
        'driver' => array(
            'odm_default' => array(
                'drivers' => array(
                    'Oldtimers\Entity\Car' => 'ODM_Driver'
                ),
            ),
            __NAMESPACE__ . '_driver' => array(
                    'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                    'cache' => 'array',
                    'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
            ),
            'orm_default' => array(
                    'drivers' => array(
                            __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                    )
            )
        ),
        'connection' => array(
                // default connection name
                'orm_default' => array(
                        'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                        'params' => array(
                                'host'     => 'localhost',
                                'port'     => '3306',
                                'user'     => 'root',
                                'password' => 'opossum',
                                'dbname'   => 'autoproject',
                        )
                )
        )
    ),
    'environment' => 'development',
);