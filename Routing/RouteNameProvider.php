<?php

namespace FSC\RestBundle\Routing;

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
        return $this->resourceBaseRoute.'_collection_form_'.$this->normalizeRel($formRel);
    }

    public function getEntityRouteName()
    {
        return $this->resourceBaseRoute.'_entity';
    }

    public function getEntityFormRouteName($formRel)
    {
        return $this->resourceBaseRoute.'_entity_form_'.$this->normalizeRel($formRel);
    }

    public function getEntityCollectionRouteName($collectionRel)
    {
        return $this->resourceBaseRoute.'_entity_collection_'.$this->normalizeRel($collectionRel);
    }

    public function getEntityCollectionFormRouteName($collectionRel, $formRel)
    {
        return $this->resourceBaseRoute.'_entity_collection_'.$this->normalizeRel($collectionRel).'_form_'.$this->normalizeRel($formRel);
    }

    public static function normalizeRel($rel)
    {
        return preg_replace('/[^0-9a-zA-Z_.]+/', '', $rel);
    }
}
