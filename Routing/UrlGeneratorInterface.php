<?php

namespace FSC\Common\RestBundle\Routing;

interface UrlGeneratorInterface
{
    public function generateCollectionUrl($parameters = array());
    public function generateCollectionFormUrl($formRel, $parameters = array());
    public function generateEntityUrl($entity, $parameters = array());
    public function generateEntityFormUrl($entity, $formRel, $parameters = array());
    public function generateEntityCollectionUrl($entity, $collectionRel, $parameters = array());
    public function generateEntityCollectionFormUrl($entity, $collectionRel, $formRel, $parameters = array());
}
