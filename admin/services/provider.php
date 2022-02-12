<?php

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;

use ETE\Component\EventTableEdit\Administrator\Extension\EventTableEditComponent;


return new class implements ServiceProviderInterface {
    
    public function register(Container $container): void {
		$container->registerServiceProvider(new MVCFactory('\\ETE\\Component\\EventTableEdit'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\ETE\\Component\\EventTableEdit'));
		$container->registerServiceProvider(new RouterFactory('\\ETE\\Component\\EventTableEdit'));
		
		
        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new EventTableEditComponent($container->get(ComponentDispatcherFactoryInterface::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
				$component->setRouterFactory($container->get(RouterFactoryInterface::class));

                return $component;
            }
        );
    }
};