<?php

namespace FSC\RestBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\RouteCollection;

use FSC\RestBundle\REST\AbstractResource;

class RestLoader implements LoaderInterface
{
    protected $resources;

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        $routes = new RouteCollection();

        foreach ($this->resources as $serviceId => $resource) {
            $resource->configureRoutes($routes, $serviceId);
        }

        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'fsc_rest' == $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getResolver()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function setResolver(LoaderResolverInterface $resolver)
    {

    }

    public function addResource($serviceId, AbstractResource $resource)
    {
        $this->resources[$serviceId] = $resource;
    }
}
