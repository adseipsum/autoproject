<?php
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
    
    /*
     * A basic route called entry which will route on /entry
     * Further, child routes allow for /entry/action/id to be mapped out 
     */
    'router' => array(
        'routes' => array(
            'entry' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Oldtimers\Controller',
                        'controller'    => 'Index',
                        'action'        => 'list',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '[/:action][/:id]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            )
                        ),
                    ),
                ),
            ),
        ),
    ),
    
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
            'oldtimers/index/index' => __DIR__ . '/../view/oldtimers/index/index.phtml',
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
        ),
    ),
);