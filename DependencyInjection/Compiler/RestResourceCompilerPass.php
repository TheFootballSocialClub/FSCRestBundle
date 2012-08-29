<?php

namespace FSC\Common\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class RestResourceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $routingLoaderDefinition = $container->getDefinition('fsc.common.rest.routing.loader');

        $resources = array();

        foreach ($container->findTaggedServiceIds('fsc_common_rest.resource') as $id => $attributes) {
            // Create a controller for each resource
            $resourceControllerDefinition = new DefinitionDecorator('fsc.common.rest.resource.controller');
            $resourceControllerDefinition->replaceArgument(0, new Reference($id));
            $controllerServiceId = $id.'.controller';
            $container->setDefinition($controllerServiceId, $resourceControllerDefinition);

            // Add resource to the route loader
            $routingLoaderDefinition->addMethodCall('addResource', array($controllerServiceId, new Reference($id)));

            // Add resource to the root controller if asked
            if (isset($attributes[0]['root_rel'])) {
                $resources[$attributes[0]['root_rel']] = new Reference($id);
            }
        }

        $rootControllerDefinition = $container->getDefinition('fsc.common.rest.controller.root');
        $rootControllerDefinition->replaceArgument(0, $resources);
    }
}
