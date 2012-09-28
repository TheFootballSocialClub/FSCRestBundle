<?php

namespace FSC\RestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fsc_rest');

        $rootNode
            ->children()
                ->arrayNode('root_controller')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->variableNode('resources')
                            ->defaultValue(array())
                        ->end()
                        ->variableNode('routes')
                            ->defaultValue(array())
                        ->end()
                        ->variableNode('service_id')
                            ->defaultValue(null)
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
