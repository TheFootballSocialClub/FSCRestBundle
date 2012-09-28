<?php

namespace FSC\RestBundle\Routing;

interface RouteNameProviderInterface
{
    public function getCollectionRouteName();
    public function getCollectionFormRouteName($formRel);
    public function getEntityRouteName();
    public function getEntityFormRouteName($formRel);
    public function getEntityCollectionRouteName($collectionRel);
    public function getEntityCollectionFormRouteName($collectionRel, $formRel);
}
