<?php

namespace FSC\RestBundle\Controller;

use FOS\RestBundle\View\ViewHandlerInterface;
use FOS\RestBundle\View\View;
use Symfony\Component\Routing\RouterInterface;

use FSC\RestBundle\Model\Representation\Resource;
use FSC\RestBundle\REST\AtomLinkFactory;

class RootController
{
    /**
     * @var array rel => resource
     */
    protected $resources = array();

    /**
     * @var array rel => route
     */
    protected $routes = array();

    /**
     * @var ViewHandlerInterface
     */
    protected $viewHandler;

    /**
     * @var AtomLinkFactory
     */
    protected $atomLinkFactory;

    /**
     * @var RouterInterface
     */
    protected $router;

    public function indexAction()
    {
        $rootRepresentation = $this->createRootRepresentation();

        return $this->viewHandler->handle(View::create($rootRepresentation));
    }

    public function addResource($rel, $resource)
    {
        $this->resources[$rel] = $resource;
    }

    public function addRoute($rel, $route)
    {
        $this->routes[$rel] = $route;
    }

    /**
     * @param AtomLinkFactory $atomLinkFactory
     */
    public function setAtomLinkFactory($atomLinkFactory)
    {
        $this->atomLinkFactory = $atomLinkFactory;
    }

    /**
     * @param RouterInterface $router
     */
    public function setRouter($router)
    {
        $this->router = $router;
    }

    /**
     * @param ViewHandlerInterface $viewHandler
     */
    public function setViewHandler($viewHandler)
    {
        $this->viewHandler = $viewHandler;
    }

    protected function createRootRepresentation()
    {
        $rootRepresentation = new Resource();

        // Add link to every resource collection
        foreach ($this->resources as $rel => $resource) { /** @var $resource \FSC\RestBundle\REST\AbstractResource */
            $href = $this->router->generate($resource->getRouteNameProvider()->getCollectionRouteName(), array(), true);
            $rootRepresentation->addLink($this->atomLinkFactory->create($rel, $href));
        }

        // Add a link to every custom route
        foreach ($this->routes as $rel => $route) {
            $rootRepresentation->addLink($this->atomLinkFactory->create($rel, $this->router->generate($route, array(), true)));
        }

        return $rootRepresentation;
    }
}
