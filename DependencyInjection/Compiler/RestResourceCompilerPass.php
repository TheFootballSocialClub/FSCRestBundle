<?php

namespace FSC\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class RestResourceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $routingLoaderDefinition = $container->getDefinition('fsc.rest.routing.loader');

        foreach ($container->findTaggedServiceIds('fsc_rest.resource') as $id => $attributes) {
            // Create a controller for each resource
            $resourceControllerDefinition = new DefinitionDecorator('fsc.rest.resource.controller');
            $resourceControllerDefinition->replaceArgument(0, new Reference($id));
            $controllerServiceId = $id.'.controller';
            $container->setDefinition($controllerServiceId, $resourceControllerDefinition);

            // Add resource to the route loader
            $routingLoaderDefinition->addMethodCall('addResource', array($controllerServiceId, new Reference($id)));
        }
    }
}
