<?php

namespace FSC\Common\RestBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;

use FSC\Common\RestBundle\REST\AbstractResource;

class ResourceController
{
    /** @var AbstractResource */
    protected $resource;

    protected $viewHandler;

    protected $serializerXmlSerializationVisitor;

    public function __construct(AbstractResource $resource, $viewHandler, $serializerXmlSerializationVisitor)
    {
        $this->resource = $resource;
        $this->viewHandler = $viewHandler;
        $this->serializerXmlSerializationVisitor = $serializerXmlSerializationVisitor;
    }

    public function getCollectionAction(Request $request)
    {
        $createSearch = $this->resource->getConfigurationCollectionSearch()['create_form_object'];
        $search = $createSearch();
        $createForm = $this->resource->getConfigurationCollectionSearch()['create_form'];
        $searchForm = $createForm();
        $searchForm->setData($search);
        $searchForm->bind($request);

        if (!$searchForm->isValid()) {
            return $this->viewHandler->handle(View::create($searchForm));
        }

        $pager = $this->resource->getCollectionPager($search);
        $collectionRepresentation = $this->resource->normalizeCollection($pager, $searchForm);

        $entityRootName = $this->resource->getConfigurationCollection()['xml_root_name'];
        $this->serializerXmlSerializationVisitor->setDefaultRootName($entityRootName);

        return $this->viewHandler->handle(View::create($collectionRepresentation));
    }

    public function getCollectionFormAction(Request $request)
    {
        $route = $request->attributes->get('_route');
        preg_match('#collection_form_([^_]+)#', $route, $matches);
        $rel = $matches[1];

        if ($rel != 'search') { // TODO Check rel exists
            throw new NotFoundHttpException();
        }

        $createForm = $this->resource->getConfigurationCollectionSearch()['create_form'];
        $form = $createForm();

        $actionUrl = $this->resource->getUrlGenerator()->generateCollectionUrl();
        $linkHref = $this->resource->getUrlGenerator()->generateCollectionFormUrl($rel);

        $formRepresentation = $this->resource->normalizeForm($form, $actionUrl, $linkHref);

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

        $route = $request->attributes->get('_route');
        preg_match('#collection_([^_]+)#', $route, $matches);
        $rel = $matches[1];

        if (false) { // Check rel exists
            throw new NotFoundHttpException();
        }

        $entitiesRepresentation = $this->resource->getEntityCollectionRepresentation($entity, $rel, true);

        $this->serializerXmlSerializationVisitor->setDefaultRootName($rel);

        return $this->viewHandler->handle(View::create($entitiesRepresentation));
    }

    public function getEntityCollectionFormAction(Request $request)
    {
        $entity = $this->resource->getEntity($request);

        $route = $request->attributes->get('_route');
        preg_match('#collection_([^_]+)_form_([^_]+)#', $route, $matches);
        $rel = $matches[1];
        $formRel = $matches[2];

        if ($formRel != 'search') { // TODO Check rel exists
            throw new NotFoundHttpException();
        }

        $createForm = $this->resource->getConfigurationCollectionSearch()['create_form'];
        $form = $createForm();

        $actionUrl = $this->resource->getUrlGenerator()->generateEntityCollectionUrl($entity, $rel);
        $linkHref = $this->resource->getUrlGenerator()->generateEntityCollectionFormUrl($entity, $rel, $formRel);

        $formRepresentation = $this->resource->normalizeForm($form, $actionUrl, $linkHref);

        return $this->viewHandler->handle(View::create($formRepresentation));
    }

}
