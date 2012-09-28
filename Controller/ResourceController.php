<?php

namespace FSC\RestBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;

use FSC\RestBundle\REST\AbstractResource;
use FSC\RestBundle\Normalizer\FormNormalizer;

class ResourceController
{
    /**
     * @var AbstractResource
     */
    protected $resource;

    protected $formNormalizer;

    /**
     * @var ViewHandlerInterface
     */
    protected $viewHandler;

    protected $serializerXmlSerializationVisitor;

    public function __construct(AbstractResource $resource, FormNormalizer $formNormalizer, ViewHandlerInterface $viewHandler,
                                $serializerXmlSerializationVisitor)
    {
        $this->resource = $resource;
        $this->formNormalizer = $formNormalizer;
        $this->viewHandler = $viewHandler;
        $this->serializerXmlSerializationVisitor = $serializerXmlSerializationVisitor;
    }

    public function getCollectionAction(Request $request)
    {
        $formDescription = $this->resource->createCollectionSearchFormDescription($request);

        if (!$formDescription->getForm()->isValid()) {
            return $this->viewHandler->handle(View::create($formDescription->getForm()));
        }

        $pager = $this->resource->getCollectionPager($formDescription->getData());
        $collectionRepresentation = $this->resource->normalizeCollection($pager, $formDescription);

        $entityRootName = $this->resource->getConfigurationCollection()['xml_root_name'];
        $this->serializerXmlSerializationVisitor->setDefaultRootName($entityRootName);

        return $this->viewHandler->handle(View::create($collectionRepresentation));
    }

    public function getCollectionFormAction(Request $request)
    {
        $rel = $this->getRelFromCollectionFormRoute($request);

        $formDescription = $this->resource->createCollectionFormDescription($rel);
        $formRepresentation = $this->resource->normalizeForm($formDescription);

        return $this->viewHandler->handle(View::create($formRepresentation));
    }

    public function getEntityAction(Request $request)
    {
        $entity = $this->resource->getEntity($request);
        $entityRepresentation = $this->resource->normalizeEntity($entity);

        $entityRootName = $this->resource->getConfigurationEntity()['xml_root_name'];
        $this->serializerXmlSerializationVisitor->setDefaultRootName($entityRootName);

        return $this->viewHandler->handle(View::create($entityRepresentation));
    }

    public function getEntityCollectionAction(Request $request)
    {
        $entity = $this->resource->getEntity($request);
        $rel = $this->getRelFromEntityCollectionRoute($request);

        $formDescription = $this->resource->createEntityCollectionSearchFormDescription($entity, $rel, $request);
        $pager = $this->resource->getEntityCollectionPager($entity, $formDescription->getData(), $rel);
        $entitiesRepresentation = $this->resource->normalizeEntityCollection($entity, $rel, $pager, $formDescription);

        return $this->viewHandler->handle(View::create($entitiesRepresentation));
    }

    public function getEntityCollectionFormAction(Request $request)
    {
        $entity = $this->resource->getEntity($request);
        list($rel, $formRel) = $this->getRelsFromEntityCollectionFormRoute($request);

        $formDescription = $this->resource->createEntityCollectionFormDescription($formRel, $entity, $rel);
        $formRepresentation = $this->formNormalizer->normalizeFormDescription($formDescription);

        return $this->viewHandler->handle(View::create($formRepresentation));
    }

    protected function getRelFromCollectionFormRoute(Request $request)
    {
        $route = $request->attributes->get('_route');
        preg_match('#collection_form_([^_]+)#', $route, $matches);

        return $matches[1];
    }

    protected function getRelFromEntityCollectionRoute(Request $request)
    {
        $route = $request->attributes->get('_route');
        preg_match('#collection_([^_]+)#', $route, $matches);

        return $this->resource->getEntityCollectionRelFromRouteRel($matches[1]);
    }

    protected function getRelsFromEntityCollectionFormRoute(Request $request)
    {
        $route = $request->attributes->get('_route');
        preg_match('#collection_([^_]+)_form_([^_]+)#', $route, $matches);

        return array($this->resource->getEntityCollectionRelFromRouteRel($matches[1]), $matches[2]); // Rel & FormRel
    }
}
