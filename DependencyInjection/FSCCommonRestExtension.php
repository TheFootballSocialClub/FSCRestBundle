<?php

namespace FSC\Common\RestBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class FSCCommonRestExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        foreach (array('form.yml', 'routing.yml', 'resource.yml', 'normalizer.yml', 'controller.yml') as $resource) {
            $loader->load($resource);
        }
    }
}
