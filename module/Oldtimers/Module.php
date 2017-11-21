<?php
namespace Oldtimers;
use Oldtimers\Mapper\CarMapper;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container;

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
        $serviceManager      = $e->getApplication()->getServiceManager();
        $serviceManager->get('MvcTranslator');
        $this->initTranslator($e);

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

    protected function initTranslator(MvcEvent $event)
    {
        $serviceManager = $event->getApplication()->getServiceManager();
        $session = New Container('language');

        $translator = $serviceManager->get('MvcTranslator');
        $translator
            ->setLocale($session->language)
            ->setFallbackLocale('sr_SP');
    }
}