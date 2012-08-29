<?php

namespace FSC\Common\RestBundle\Routing;

class RouteNameProvider implements RouteNameProviderInterface
{
    protected $resourceBaseRoute;

    public function __construct($resourceBaseRoute)
    {
        $this->resourceBaseRoute = $resourceBaseRoute;
    }

    public function getCollectionRouteName()
    {
        return $this->resourceBaseRoute.'_collection';
    }

    public function getCollectionFormRouteName($formRel)
    {
        return $this->resourceBaseRoute.'_collection_form_'.$formRel;
    }

    public function getEntityRouteName()
    {
        return $this->resourceBaseRoute.'_entity';
    }

    public function getEntityFormRouteName($formRel)
    {
        return $this->resourceBaseRoute.'_entity_form_'.$formRel;
    }

    public function getEntityCollectionRouteName($collectionRel)
    {
        return $this->resourceBaseRoute.'_entity_collection_'.$collectionRel;
    }

    public function getEntityCollectionFormRouteName($collectionRel, $formRel)
    {
        return $this->resourceBaseRoute.'_entity_collection_'.$collectionRel.'_form_'.$formRel;
    }
}
