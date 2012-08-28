<?php

namespace FSC\Common\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RestResourceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $routingLoaderDefinition = $container->getDefinition('fsc.common.rest.routing.loader');

        $resources = array();

        // Add resources to the route loader
        foreach ($container->findTaggedServiceIds('fsc_common_rest.resource') as $id => $attributes) {
            $routingLoaderDefinition->addMethodCall('addResource', array($id, new Reference($id)));

            if (isset($attributes[0]['root_rel'])) {
                $resources[$attributes[0]['root_rel']] = new Reference($id);
            }
        }

        $rootControllerDefinition = $container->getDefinition('fsc.common.rest.controller.root');
        $rootControllerDefinition->replaceArgument(0, $resources);
    }
}
