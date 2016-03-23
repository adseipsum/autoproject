<?php
namespace Oldtimers;
use Oldtimers\Mapper\CarMapper;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
class Module
{
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
    
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }
    
    public function getServiceConfig()
    {
        return array(
                //Our Entry takes not parameters
                'invokables' => array(
                        'OldtimersCar' => 'Oldtimers\Entity\Car',
                ),
                'factories' => array(
                        //But the mapper has some dependencies which can be injected in
                        'OldtimersCarMapper' => function($sm) {
                            $dm = $sm->get('doctrine.documentmanager.odm_default');
                            return new CarMapper($dm, $dm->getRepository('Oldtimers\Entity\Car'));
                        },
                ),
                //The Entry entity should be unique
                        'shared' => array(
                                'OldtimersCar' => false
                        ),
        );
    }
}