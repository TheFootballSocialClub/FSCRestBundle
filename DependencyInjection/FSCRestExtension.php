<?php

namespace FSC\RestBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;

class FSCRestExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        foreach (array('form.yml', 'routing.yml', 'resource.yml', 'normalizer.yml', 'controller.yml') as $resource) {
            $loader->load($resource);
        }

        // Replace the default root controller by a new one
        if (null !== $config['root_controller']['service_id']) {
            $container->setAlias('fsc.rest.controller.root', $config['root_controller']['service_id']);
        }

        $rootControllerDefinition = $container->getDefinition('fsc.rest.controller.root.default');
        foreach ($config['root_controller']['resources'] as $rel => $resourceId) {
            $rootControllerDefinition->addMethodCall('addResource', array($rel, new Reference($resourceId)));
        }
        foreach ($config['root_controller']['routes'] as $rel => $route) {
            $rootControllerDefinition->addMethodCall('addRoute', array($rel, $route));
        }

        if ($container->has('security.context')) {
            $abstractResourceDefinition = $container->getDefinition('fsc.rest.resource.abstract');
            $abstractResourceDefinition->addMethodCall('setSecurityContext', array(
                $container->getDefinition('security.context'),
            ));
        }
    }

    public function getAlias()
    {
        return 'fsc_rest';
    }
}
