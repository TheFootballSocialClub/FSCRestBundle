<?php

namespace FSC\Common\RestBundle\Controller;

use FOS\RestBundle\View\ViewHandlerInterface;
use FOS\RestBundle\View\View;
use Symfony\Component\Routing\RouterInterface;

use FSC\Common\RestBundle\Model\Representation\Resource;
use FSC\Common\RestBundle\REST\AtomLinkFactory;

class RootController
{
    /**
     * @var array rel => resource
     */
    protected $resources;

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

    public function __construct($resources, ViewHandlerInterface $viewHandler, AtomLinkFactory $atomLinkFactory,
                                RouterInterface $router)
    {
        $this->resources = $resources;
        $this->viewHandler = $viewHandler;
        $this->atomLinkFactory = $atomLinkFactory;
        $this->router = $router;
    }

    public function indexAction()
    {
        $rootRepresentation = new Resource();

        foreach ($this->resources as $rel => $resource) { /** @var $resource \FSC\Common\RestBundle\REST\AbstractResource */
            $href = $this->router->generate($resource->getRouteName('collection'), array(), true);
            $rootRepresentation->addLink($this->atomLinkFactory->create($rel, $href));
        }

        return $this->viewHandler->handle(View::create($rootRepresentation));
    }
}
