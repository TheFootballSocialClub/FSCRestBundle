<?php

namespace FSC\Common\RestBundle\REST;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\Common\Util\ClassUtils;
use FOS\RestBundle\View\ViewHandlerInterface;
use FOS\RestBundle\View\View;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use FSC\Common\RestBundle\Form\Model\Collection;
use FSC\Common\RestBundle\Form\Type\CollectionType;
use FSC\Common\RestBundle\Model\Representation\Collection as CollectionRepresentation;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
abstract class AbstractResource
{
    /** @var EntityManager */
    protected $entityManager;

    /** @var RouterInterface */
    protected $router;

    /** @var ViewHandlerInterface */
    protected $viewHandler;

    protected $formNormalizer;

    /** @var FormFactory */
    protected $formFactory;

    protected $serializerXmlSerializationVisitor;

    /**
     * @var AtomLinkFactory
     */
    protected $atomLinkFactory;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var ContainerInterface
     */
    protected $container;

    private $configuration;
    private $configurationCollection;
    private $configurationCollectionSearch;
    private $configurationEntity;
    private $configurationEntityCollections;
    private $configurationEntityRelations;

    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setViewHandler(ViewHandlerInterface $viewHandler)
    {
        $this->viewHandler = $viewHandler;
    }

    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function setFormNormalizer($formNormalizer)
    {
        $this->formNormalizer = $formNormalizer;
    }

    public function setFormFactory(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }
    
    public function setSerializerXmlSerializationVisitor($serializerXmlSerializationVisitor)
    {
        $this->serializerXmlSerializationVisitor = $serializerXmlSerializationVisitor;
    }

    public function setAtomLinkFactory(AtomLinkFactory $atomLinkFactory)
    {
        $this->atomLinkFactory = $atomLinkFactory;
    }

    public function setSecurityContext(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    protected function getConfiguration()
    {
        if (null === $this->configuration) {
            $this->configuration = $this->configure();
        }

        return $this->configuration;
    }

    protected function getConfigurationCollection()
    {
        if (null === $this->configurationCollection) {
            $this->configurationCollection = $this->configureCollection();
        }

        return $this->configurationCollection;
    }

    protected function getConfigurationCollectionSearch()
    {
        if (null === $this->configurationCollectionSearch) {
            $this->configurationCollectionSearch = $this->configureCollectionSearch();
        }

        return $this->configurationCollectionSearch;
    }

    protected function getConfigurationEntity()
    {
        if (null === $this->configurationEntity) {
            $this->configurationEntity = $this->configureEntity();
        }

        return $this->configurationEntity;
    }

    protected function getConfigurationEntityCollections()
    {
        if (null === $this->configurationEntityCollections) {
            $this->configurationEntityCollections = $this->configureEntityCollections();
        }

        return $this->configurationEntityCollections;
    }

    protected function getConfigurationEntityRelations()
    {
        if (null === $this->configurationEntityRelations) {
            $this->configurationEntityRelations= $this->configureEntityRelations();
        }

        return $this->configurationEntityRelations;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    protected function configure()
    {
        // Prefix (ie: /users)
        // Route Name Prefix (ie: fsc_core_user_rest_users ...)

        return array(
            'prefix' => null,
            'route_name_prefix' => null,
            'entity_class' => null,
        );
    }

    protected function configureCollection()
    {
        // Query Builder

        return array(
            'representation_class' => 'FSC\Common\RestBundle\Model\Representation\Collection',
            'create_qb' => function () {
                $alias = substr($this->guessResourceName(), 0, 1);

                return $this->getRepository()->createQueryBuilder($alias);
            },
        );
    }

    protected function configureCollectionSearch()
    {
        // Form instance
        // Form object to bind

        return array(
            'create_form' => function () {
                return $this->formFactory->createNamed('search', new CollectionType());
            },
            'create_form_object' => function () {
                return new Collection();
            }
        );
    }

    protected function configureEntity()
    {
        return array(
            'representation_class' => 'FSC\Common\RestBundle\Model\Representation\Entity',
            'get_entity' => array($this, 'getEntity'),
            'normalize_attributes' => array(
                'id' => 'getId',
            ),
            'normalize_elements' => array(),
            'expanded_collections' => array(),
            'expanded_relations' => array(),
            'xml_root_name' => 'entity',
            'can_see' => function (SecurityContextInterface $securityContext, $entity) {
                return true;
            },
        );
    }

    protected function configureEntityCollections()
    {
        return array(

        );
    }

    protected function configureEntityRelations()
    {
        return array(

        );
    }

    public function configureRoutes(RouteCollection $routes, $serviceId)
    {
        $prefix = $this->getConfiguration()['prefix'];
        $routeNamePrefix = $this->getConfiguration()['route_name_prefix'];

        // Collection
        $routes->add($routeNamePrefix.'_collection', new Route($prefix, array(
            '_controller' => $serviceId.':getCollectionAction',
        ), array(
            '_method' => 'GET',
        )));

        // Collection Forms
        $collectionFormsRouteNamePrefix = $routeNamePrefix.'_collection_form';
        $collectionFormsPrefix = $prefix.'/forms';

        $formRel = 'search';
        $routes->add($collectionFormsRouteNamePrefix.'_'.$formRel, new Route($collectionFormsPrefix.'/'.$formRel, array(
            '_controller' => $serviceId.':getCollectionFormAction',
        ), array(
            '_method' => 'GET',
        )));

        // Entity
        $entityPrefix = $prefix.'/{id}';
        $routes->add($routeNamePrefix.'_entity', new Route($entityPrefix, array(
            '_controller' => $serviceId.':getEntityAction',
        ), array(
            '_method' => 'GET',
        )));

        // Entity collections
        foreach ($this->getConfigurationEntityCollections() as $rel => $entityCollection) {
            $entityCollectionRouteName = $routeNamePrefix.'_entity_collection_'.$rel;
            $entityCollectionPrefix = $entityPrefix.'/'.$rel;

            $routes->add($entityCollectionRouteName, new Route($entityCollectionPrefix, array(
                '_controller' => $serviceId.':getEntityCollectionAction',
            ), array(
                '_method' => 'GET',
            )));

            // Entity Collection Forms
            $entityCollectionFormsRouteNamePrefix = $entityCollectionRouteName.'_form';
            $entityCollectionFormsPrefix = $entityCollectionPrefix.'/forms';

            $formRel = 'search';
            $routes->add($entityCollectionFormsRouteNamePrefix.'_'.$formRel, new Route($entityCollectionFormsPrefix.'/'.$formRel, array(
                '_controller' => $serviceId.':getEntityCollectionFormAction',
            ), array(
                '_method' => 'GET',
            )));
        }
    }

    public function getCollectionAction(Request $request)
    {
        $createSearch = $this->getConfigurationCollectionSearch()['create_form_object'];
        $search = $createSearch();
        $createForm = $this->getConfigurationCollectionSearch()['create_form'];
        $searchForm = $createForm();
        $searchForm->setData($search);
        $searchForm->bind($request);

        if (!$searchForm->isValid()) {
            return $this->viewHandler->handle($this->createView($searchForm));
        }

        $pager = $this->getCollectionPager($search);
        $collectionRepresentation = $this->normalizeCollection($pager, $searchForm);

        $entityRootName = $this->getConfigurationCollection()['xml_root_name'];
        $this->serializerXmlSerializationVisitor->setDefaultRootName($entityRootName);

        return $this->handle($this->createView($collectionRepresentation));
    }

    public function getCollectionFormAction(Request $request)
    {
        $route = $request->attributes->get('_route');
        preg_match('#collection_form_([^_]+)#', $route, $matches);
        $rel = $matches[1];

        if ($rel != 'search') {
            throw new \Exception();
        }

        $createForm = $this->getConfigurationCollectionSearch()['create_form'];
        $form = $createForm();

        $formRepresentation = $this->formNormalizer->normalize($form);
        $formRepresentation->method = 'GET';
        $formRepresentation->action = $this->generateUrl($this->getRouteName('collection'));

        $formRepresentation->addLink($this->atomLinkFactory->create('self', $this->generateUrl($this->getRouteName('collection_form_'.$rel))));

        return $this->handle($this->createView($formRepresentation));
    }

    public function getEntityAction(Request $request)
    {
        $entity = $this->getEntity($request);
        $entityRepresentation = $this->normalizeEntity($entity);

        $entityRootName = $this->getConfigurationEntity()['xml_root_name'];
        $this->serializerXmlSerializationVisitor->setDefaultRootName($entityRootName);

        return $this->handle($this->createView($entityRepresentation));
    }

    public function getEntityCollectionAction(Request $request)
    {
        $entity = $this->getEntity($request);

        $route = $request->attributes->get('_route');
        preg_match('#collection_([^_]+)#', $route, $matches);
        $rel = $matches[1];

        if (false) { // Check rel exists
            throw new NotFoundHttpException();
        }

        $entitiesRepresentation = $this->getEntityCollectionRepresentation($entity, $rel, true);

        $this->serializerXmlSerializationVisitor->setDefaultRootName($rel);

        return $this->handle($this->createView($entitiesRepresentation));
    }

    public function getEntityCollectionFormAction(Request $request)
    {
        $entity = $this->getEntity($request);

        $route = $request->attributes->get('_route');
        preg_match('#collection_([^_]+)_form_([^_]+)#', $route, $matches);
        $rel = $matches[1];
        $formRel = $matches[2];

        if (false) { // TODO Check rel exists
            throw new NotFoundHttpException();
        }

        if ($formRel != 'search') {
            throw new \Exception();
        }

        $createForm = $this->getConfigurationCollectionSearch()['create_form'];
        $form = $createForm();

        $entityCollectionRouteName = $this->getRouteName('entity_collection_'.$rel);
        $entityCollectionRouteParameters = array('id' => $entity->getId());

        $formRepresentation = $this->formNormalizer->normalize($form);
        $formRepresentation->method = 'GET';
        $formRepresentation->action = $this->generateUrl($entityCollectionRouteName, $entityCollectionRouteParameters);

        $formRepresentation->addLink($this->atomLinkFactory->create('self', $this->generateUrl($entityCollectionRouteName.'_form_'.$formRel, $entityCollectionRouteParameters)));

        return $this->handle($this->createView($formRepresentation));
    }
    
    protected function generateUrl($name, $parameters = array())
    {
        return $this->router->generate($name, $parameters, true);
    }

    public function getRouteName($type)
    {
        return $this->getConfiguration()['route_name_prefix'].'_'.$type;
    }

    protected function guessResourceName()
    {
        $classParts = explode('\\', get_class($this));
        $class = end($classParts);
        preg_match('#^(.+)Resource$#', $class, $matches);

        return lcfirst($matches[1]);
    }

    public function guessRouteNamePrefix()
    {
        $routeName = strtolower(str_replace('\\', '_', get_class($this)));
        $routeName = substr($routeName, 0, strpos($routeName, 'bundle'));

        return sprintf('%s_rest_%s', $routeName, $this->guessResourceName());
    }

    public function normalizeEntity($entity, $doNotExpandRelationsAndCollections = false)
    {
        // Make sure that the used is authorized to see this resource
        $canSee = $this->getConfigurationEntity()['can_see'];
        if (false === $canSee($this->securityContext, $entity)) {
            throw new AccessDeniedException();
        }

        $entityRepresentationClass = $this->getConfigurationEntity()['representation_class'];
        $entityRepresentation = new $entityRepresentationClass();

        $entityRouteName = $this->getRouteName('entity');
        $entityRepresentation->addLink($this->atomLinkFactory->create('self', $this->generateUrl($entityRouteName, array(
            'id' => $entity->getId(),
        ))));

        $getEntityValue = function ($value) use ($entity) {
            if ($value instanceof \Closure) {
                return $value($entity);
            }

            return $entity->{$value}();
        };

        // Properties
        $normalizeAttributes = $this->getConfigurationEntity()['normalize_attributes'];
        foreach ($normalizeAttributes as $key => $value) {
            $entityRepresentation->setAttribute($key, $getEntityValue($value));
        }

        // Elements
        $normalizeElements = $this->getConfigurationEntity()['normalize_elements'];
        foreach ($normalizeElements as $key => $value) {
            $entityRepresentation->setElement($key, $getEntityValue($value));
        }

        // Entity collections
        foreach ($this->getConfigurationEntityCollections() as $entityCollectionRel => $configurationEntityCollection) {
            $entityCollectionRouteName = $entityRouteName.'_collection_'.$entityCollectionRel;
            $entityCollectionRouteParameters = array('id' => $entity->getId());

            if ($doNotExpandRelationsAndCollections || !in_array($entityCollectionRel, $this->getConfigurationEntity()['expanded_collections'])) {
                $entityRepresentation->addLink($this->atomLinkFactory->create($entityCollectionRel, $this->generateUrl($entityCollectionRouteName, $entityCollectionRouteParameters)));
            } else {
                $entityRelationRepresentation = $this->getEntityCollectionRepresentation($entity, $entityCollectionRel, true);
                $entityRelationRepresentation->rel = $entityCollectionRel;
                $entityRepresentation->addCollection($entityRelationRepresentation);
            }
        }

        // Entity relations
        foreach ($this->getConfigurationEntityRelations() as $entityRelationRel => $entityRelationConfiguration) {
            $getRelation = $entityRelationConfiguration['get_relation'];
            $entityRelation = $getRelation($entity);

            $entityRelationClassName = ClassUtils::getRealClass(get_class($entityRelation));
            $entityRelationResource = $this->container->get($entityRelationConfiguration['resources'][$entityRelationClassName]);

            $entityRelationRouteName = $entityRelationResource->getRouteName('entity');
            $entityRelationRouteParameters = array('id' => $entityRelation->getId());

            $canSeeEntityRelation = $entityRelationResource->getConfigurationEntity()['can_see'];

            if (!$canSeeEntityRelation($this->securityContext, $entityRelation)
                || $doNotExpandRelationsAndCollections
                || !in_array($entityRelationRel, $this->getConfigurationEntity()['expanded_relations'])) {
                $entityRepresentation->addLink($this->atomLinkFactory->create($entityRelationRel, $this->generateUrl($entityRelationRouteName, $entityRelationRouteParameters)));
            } else {
                $entityRelationRepresentation = $entityRelationResource->normalizeEntity($entityRelation, true);
                $entityRelationRepresentation->rel = $entityRelationRel;
                $entityRepresentation->addRelation($entityRelationRepresentation);
            }
        }

        return $entityRepresentation;
    }

    protected function normalizeCollection(Pagerfanta $pager, $searchForm)
    {
        $collectionRepresentationClass = $this->getConfigurationCollection()['representation_class'];
        $collectionRepresentation = new $collectionRepresentationClass();

        $collectionRouteName = $this->getRouteName('collection');
        $this->configureCollectionRepresentation($collectionRepresentation, $pager, $collectionRouteName);

        // Results
        $collectionRepresentation->results = array();
        foreach ($pager->getCurrentPageResults() as $entity) {
            $collectionRepresentation->results[] = $this->normalizeEntity($entity, true);
        }

        // Forms
        $formRel = 'search';
        $collectionFormSearchURL = $this->generateUrl($collectionRouteName.'_form_'.$formRel);

        if (in_array($formRel, $this->getConfigurationCollection()['expanded_forms'])) {
            $formRepresentation = $this->formNormalizer->normalize($searchForm);
            $formRepresentation->rel = $formRel;
            $formRepresentation->method = 'GET';
            $formRepresentation->action = $this->generateUrl($collectionRouteName);

            $formRepresentation->addLink($this->atomLinkFactory->create('self', $collectionFormSearchURL));

            $collectionRepresentation->addForm($formRepresentation);
        } else {
            $collectionRepresentation->addLink($this->atomLinkFactory->create($formRel, $collectionFormSearchURL));
        }

        return $collectionRepresentation;
    }

    protected function normalizeEntityCollection($entity, $rel, Pagerfanta $pager, $searchForm, $doNotExpandRelationsAndCollections = false)
    {
        $configurationEntityCollection = $this->getConfigurationEntityCollections()[$rel];
        $entityCollectionRepresentationClass = $configurationEntityCollection['representation_class'];
        $entityCollectionRepresentation = new $entityCollectionRepresentationClass();

        $entityCollectionRouteName = $this->getRouteName('entity_collection_'.$rel);
        $entityCollectionRouteNameParameters = array('id' => $entity->getId());
        $this->configureCollectionRepresentation($entityCollectionRepresentation, $pager, $entityCollectionRouteName, $entityCollectionRouteNameParameters);

        // Results
        $entityCollectionRepresentation->results = array();
        foreach ($pager->getCurrentPageResults() as $entity) {
            $entityResource = $this->container->get($configurationEntityCollection['resources'][get_class($entity)]);
            $entityCollectionRepresentation->results[] = $entityResource->normalizeEntity($entity, $doNotExpandRelationsAndCollections);
        }

        // Forms
        $formRel = 'search';
        $entityCollectionFormSearchURL = $this->generateUrl($entityCollectionRouteName.'_form_'.$formRel, $entityCollectionRouteNameParameters);

        if (in_array($formRel, $configurationEntityCollection['expanded_forms'])) {
            $formRepresentation = $this->formNormalizer->normalize($searchForm);
            $formRepresentation->rel = $formRel;
            $formRepresentation->method = 'GET';
            $formRepresentation->action = $this->generateUrl($entityCollectionRouteName, $entityCollectionRouteNameParameters);

            $formRepresentation->addLink($this->atomLinkFactory->create('self', $entityCollectionFormSearchURL));

            $entityCollectionRepresentation->addForm($formRepresentation);
        } else {
            $entityCollectionRepresentation->addLink($this->atomLinkFactory->create($formRel, $entityCollectionFormSearchURL));
        }

        return $entityCollectionRepresentation;
    }

    protected function configureCollectionRepresentation(CollectionRepresentation $collectionRepresentation, Pagerfanta $pager, $routeName, $routeParameters = array())
    {
        // Properties
        $collectionRepresentation->total = $pager->getNbResults();
        $collectionRepresentation->page = $pager->getCurrentPage();
        $collectionRepresentation->limit = $pager->getMaxPerPage();

        // Links between pages
        $createRoute = function ($page, $limit) use ($routeName, $routeParameters) {
            return $this->generateUrl($routeName, array_merge($routeParameters, array(
                'search' => array(
                    'page' => $page,
                    'limit' => $limit,
                ),
            )));
        };

        $collectionRepresentation->addLink($this->atomLinkFactory->create('self', $createRoute($pager->getCurrentPage(), $pager->getMaxPerPage())));

        if ($pager->hasNextPage()) {
            $collectionRepresentation->addLink($this->atomLinkFactory->create('next', $createRoute($pager->getNextPage(), $pager->getMaxPerPage())));
        }

        if ($pager->hasPreviousPage()) {
            $collectionRepresentation->addLink($this->atomLinkFactory->create('previous', $createRoute($pager->getPreviousPage(), $pager->getMaxPerPage())));
        }

        $collectionRepresentation->addLink($this->atomLinkFactory->create('first', $createRoute(1, $pager->getMaxPerPage())));
        $collectionRepresentation->addLink($this->atomLinkFactory->create('last', $createRoute($pager->getNbPages(), $pager->getMaxPerPage())));
    }

    protected function getEntityCollectionRepresentation($entity, $rel, $doNotExpandRelationsAndCollections = false)
    {
        $createSearch = $this->getConfigurationCollectionSearch()['create_form_object'];
        $createForm = $this->getConfigurationCollectionSearch()['create_form'];
        $search = $createSearch();
        $searchForm = $createForm();
        $searchForm->setData($search);

        $pager = $this->getEntityCollectionPager($entity, $search, $rel);

        return $this->normalizeEntityCollection($entity, $rel, $pager, $searchForm, $doNotExpandRelationsAndCollections);
    }

    protected function getEntity(Request $request)
    {
        $entity = $this->getRepository()->find($request->attributes->get('id'));

        if (null === $entity) {
            throw new NotFoundHttpException();
        }

        return $entity;
    }

    protected function getCollectionPager(Collection $search)
    {
        $createQb = $this->getConfigurationCollection()['create_qb'];
        $qb = $createQb();

        return $this->createORMPager($qb, $search);
    }

    protected function getEntityCollectionPager($entity, Collection $search, $rel)
    {
        $createQb = $this->getConfigurationEntityCollections()[$rel]['create_qb'];
        $qb = $createQb($entity);

        return $this->createORMPager($qb, $search);
    }

    protected function createView($data = null, $statusCode = null, $headers = array())
    {
        return View::create($data, $statusCode, $headers);
    }

    protected function handle(View $view, Request $request = null)
    {
        return $this->viewHandler->handle($view, $request);
    }

    protected function getRepository()
    {
        return $this->entityManager->getRepository($this->getConfiguration()['entity_class']);
    }

    /**
     * @param QueryBuilder|Query $query
     * @param Collection         $collection
     *
     * @return Pagerfanta
     */
    public function createORMPager($query, Collection $collection = null)
    {
        $pager = new Pagerfanta(new DoctrineORMAdapter($query));

        if (null !== $collection) {
            $pager->setMaxPerPage($collection->getLimit());
            $pager->setCurrentPage($collection->getPage());
        }

        return $pager;
    }
}
