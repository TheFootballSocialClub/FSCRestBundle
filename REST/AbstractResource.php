<?php

namespace FSC\Common\RestBundle\REST;

use Doctrine\Common\Util\ClassUtils;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Util\PropertyPath;
use Symfony\Component\Form\Form;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use FSC\Common\RestBundle\Form\Model\Collection;
use FSC\Common\RestBundle\Form\Type\CollectionType;
use FSC\Common\RestBundle\Model\Representation\Collection as CollectionRepresentation;
use FSC\Common\RestBundle\Routing\RouteNameProviderInterface;
use FSC\Common\RestBundle\Routing\RouteNameProvider;
use FSC\Common\RestBundle\Routing\UrlGeneratorInterface;
use FSC\Common\RestBundle\Routing\UrlGenerator;


/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
abstract class AbstractResource
{
    /** @var RouterInterface */
    protected $router;

    protected $formNormalizer;

    /** @var FormFactory */
    protected $formFactory;

    /**
     * @var AtomLinkFactory
     */
    protected $atomLinkFactory;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /** @var RouteNameProviderInterface */
    protected $routeNameProvider;

    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

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

    public function setAtomLinkFactory(AtomLinkFactory $atomLinkFactory)
    {
        $this->atomLinkFactory = $atomLinkFactory;
    }

    public function setSecurityContext(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function getConfiguration()
    {
        if (null === $this->configuration) {
            $this->configuration = $this->configure();
        }

        return $this->configuration;
    }

    public function getConfigurationCollection()
    {
        if (null === $this->configurationCollection) {
            $this->configurationCollection = $this->configureCollection();
        }

        return $this->configurationCollection;
    }

    public function getConfigurationCollectionSearch()
    {
        if (null === $this->configurationCollectionSearch) {
            $this->configurationCollectionSearch = $this->configureCollectionSearch();
        }

        return $this->configurationCollectionSearch;
    }

    public function getConfigurationEntity()
    {
        if (null === $this->configurationEntity) {
            $this->configurationEntity = $this->configureEntity();
        }

        return $this->configurationEntity;
    }

    public function getConfigurationEntityCollections()
    {
        if (null === $this->configurationEntityCollections) {
            $this->configurationEntityCollections = $this->configureEntityCollections();
        }

        return $this->configurationEntityCollections;
    }

    public function getConfigurationEntityRelations()
    {
        if (null === $this->configurationEntityRelations) {
            $this->configurationEntityRelations= $this->configureEntityRelations();
        }

        return $this->configurationEntityRelations;
    }

    public function getRouteNameProvider()
    {
        if (null === $this->routeNameProvider) {
            $this->routeNameProvider = new RouteNameProvider($this->getConfiguration()['route_name_prefix']);
        }

        return $this->routeNameProvider;
    }

    public function getUrlGenerator()
    {
        if (null === $this->urlGenerator) {
            $this->urlGenerator = new UrlGenerator($this->router, $this->getRouteNameProvider());
        }

        return $this->urlGenerator;
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
            'route_name_prefix' => $this->guessRouteNamePrefix(),
            'entity_class' => null,
        );
    }

    protected function configureCollection()
    {
        // Query Builder

        return array(
            'xml_root_name' => null,
            'representation_class' => 'FSC\Common\RestBundle\Model\Representation\Collection',
            'expanded_forms' => array(),
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
            /*
            'addresses' => array(
                'representation_class' => 'FSC\Core\MainBundle\Model\Representation\Addresses',
                'expanded_forms' => array('search'),
                'resources' => array(
                    'FSC\Core\MainBundle\Entity\Address' => 'fsc.core.main.resource.addresses',
                ),
                'create_qb' => function ($entity) {
                    $entityAddressesIds = $this->getRepository()->getEntityCollectionIds($entity, 'addresses');

                    return $this->entityManager->getRepository('FSCCoreMainBundle:Address')->createSelectByIdsQB($entityAddressesIds);
                },
            ),
            */
        );
    }

    public function configureRoutes(RouteCollection $routes, $serviceId)
    {
        $prefix = $this->getConfiguration()['prefix'];
        $resourceRouteNameProvider = $this->getRouteNameProvider();

        // Collection
        $routes->add($resourceRouteNameProvider->getCollectionRouteName(), new Route($prefix, array(
            '_controller' => $serviceId.':getCollectionAction',
        ), array(
            '_method' => 'GET',
        )));

        // Collection Forms
        $collectionFormsPrefix = $prefix.'/forms';

        $formRel = 'search';
        $routes->add($resourceRouteNameProvider->getCollectionFormRouteName($formRel), new Route($collectionFormsPrefix.'/'.$formRel, array(
            '_controller' => $serviceId.':getCollectionFormAction',
        ), array(
            '_method' => 'GET',
        )));

        // Entity
        $entityPrefix = $prefix.'/{id}';
        $routes->add($resourceRouteNameProvider->getEntityRouteName(), new Route($entityPrefix, array(
            '_controller' => $serviceId.':getEntityAction',
        ), array(
            '_method' => 'GET',
        )));

        // Entity collections
        foreach ($this->getConfigurationEntityCollections() as $rel => $entityCollection) {
            $entityCollectionPrefix = $entityPrefix.'/'.$rel;

            $routes->add($resourceRouteNameProvider->getEntityCollectionRouteName($rel), new Route($entityCollectionPrefix, array(
                '_controller' => $serviceId.':getEntityCollectionAction',
            ), array(
                '_method' => 'GET',
            )));

            // Entity Collection Forms
            $entityCollectionFormsPrefix = $entityCollectionPrefix.'/forms';

            $formRel = 'search';
            $routes->add(
                $resourceRouteNameProvider->getEntityCollectionFormRouteName($rel, $formRel),
                new Route($entityCollectionFormsPrefix.'/'.$formRel, array(
                    '_controller' => $serviceId.':getEntityCollectionFormAction',
                ), array(
                    '_method' => 'GET',
                ))
            );
        }
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

        $entityRepresentation->addLink($this->atomLinkFactory->create('self', $this->getUrlGenerator()->generateEntityUrl($entity)));

        $getEntityValue = function ($value) use ($entity) {
            if ($value instanceof \Closure) {
                return $value($entity);
            }

            $propertyPath = new PropertyPath($value);

            return $propertyPath->getValue($entity);
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
            if ($doNotExpandRelationsAndCollections || !in_array($entityCollectionRel, $this->getConfigurationEntity()['expanded_collections'])) {
                $entityRepresentation->addLink($this->atomLinkFactory->create(
                    $entityCollectionRel,
                    $this->getUrlGenerator()->generateEntityCollectionUrl($entity, $entityCollectionRel)
                ));
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

            $canSeeEntityRelation = $entityRelationResource->getConfigurationEntity()['can_see'];

            if (!$canSeeEntityRelation($this->securityContext, $entityRelation)
                || $doNotExpandRelationsAndCollections
                || !in_array($entityRelationRel, $this->getConfigurationEntity()['expanded_relations'])) {
                $entityRepresentation->addLink($this->atomLinkFactory->create(
                    $entityRelationRel,
                    $entityRelationResource->getUrlGenerator()->generateEntityUrl($entity)
                ));
            } else {
                $entityRelationRepresentation = $entityRelationResource->normalizeEntity($entityRelation, true);
                $entityRelationRepresentation->rel = $entityRelationRel;
                $entityRepresentation->addRelation($entityRelationRepresentation);
            }
        }

        return $entityRepresentation;
    }

    public function normalizeCollection(Pagerfanta $pager, $searchForm)
    {
        $collectionRepresentationClass = $this->getConfigurationCollection()['representation_class'];
        $collectionRepresentation = new $collectionRepresentationClass();

        $this->configureCollectionRepresentation($collectionRepresentation, $pager);

        // Results
        $collectionRepresentation->results = array();
        foreach ($pager->getCurrentPageResults() as $entity) {
            $collectionRepresentation->results[] = $this->normalizeEntity($entity, true);
        }

        // Forms
        $formRel = 'search';
        $collectionFormSearchURL = $this->getUrlGenerator()->generateCollectionFormUrl($formRel);

        if (in_array($formRel, $this->getConfigurationCollection()['expanded_forms'])) {
            $formRepresentation = $this->formNormalizer->normalize($searchForm);
            $formRepresentation->rel = $formRel;
            $formRepresentation->method = 'GET';
            $formRepresentation->action = $this->getUrlGenerator()->generateCollectionUrl();

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

        $this->configureCollectionRepresentation($entityCollectionRepresentation, $pager, $entity, $rel);

        // Results
        $entityCollectionRepresentation->results = array();
        foreach ($pager->getCurrentPageResults() as $entity) {
            $entityResource = $this->container->get($configurationEntityCollection['resources'][get_class($entity)]);
            $entityCollectionRepresentation->results[] = $entityResource->normalizeEntity($entity, $doNotExpandRelationsAndCollections);
        }

        // Forms
        $formRel = 'search';
        $entityCollectionFormSearchURL = $this->getUrlGenerator()->generateEntityCollectionFormUrl($entity, $rel, $formRel);

        if (in_array($formRel, $configurationEntityCollection['expanded_forms'])) {
            $formRepresentation = $this->formNormalizer->normalize($searchForm);
            $formRepresentation->rel = $formRel;
            $formRepresentation->method = 'GET';
            $formRepresentation->action = $this->getUrlGenerator()->generateEntityCollectionUrl($entity, $rel);

            $formRepresentation->addLink($this->atomLinkFactory->create('self', $entityCollectionFormSearchURL));

            $entityCollectionRepresentation->addForm($formRepresentation);
        } else {
            $entityCollectionRepresentation->addLink($this->atomLinkFactory->create($formRel, $entityCollectionFormSearchURL));
        }

        return $entityCollectionRepresentation;
    }

    public function normalizeForm(Form $form, $actionUrl, $linkHref)
    {
        $formRepresentation = $this->formNormalizer->normalize($form);
        $formRepresentation->method = 'GET';
        $formRepresentation->action = $actionUrl;

        $formRepresentation->addLink($this->atomLinkFactory->create('self', $linkHref));

        return $formRepresentation;
    }

    protected function configureCollectionRepresentation(CollectionRepresentation $collectionRepresentation, Pagerfanta $pager, $entity = null, $collectionRel = null)
    {
        // Properties
        $collectionRepresentation->total = $pager->getNbResults();
        $collectionRepresentation->page = $pager->getCurrentPage();
        $collectionRepresentation->limit = $pager->getMaxPerPage();

        // Links between pages
        $createRoute = function ($page, $limit) use ($entity, $collectionRel) {
            $parameters = array(
                'search' => array(
                    'page' => $page,
                    'limit' => $limit,
                ),
            );

            return null !== $entity && null !== $collectionRel
                ? $this->getUrlGenerator()->generateEntityCollectionUrl($entity, $collectionRel, $parameters)
                : $this->getUrlGenerator()->generateCollectionUrl($parameters)
            ;
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

    public function getEntityCollectionRepresentation($entity, $rel, $doNotExpandRelationsAndCollections = false)
    {
        $createSearch = $this->getConfigurationCollectionSearch()['create_form_object'];
        $createForm = $this->getConfigurationCollectionSearch()['create_form'];
        $search = $createSearch();
        $searchForm = $createForm();
        $searchForm->setData($search);

        $pager = $this->getEntityCollectionPager($entity, $search, $rel);

        return $this->normalizeEntityCollection($entity, $rel, $pager, $searchForm, $doNotExpandRelationsAndCollections);
    }

    abstract public function getEntity(Request $request);
    abstract public function getCollectionPager(Collection $search);
    abstract public function getEntityCollectionPager($entity, Collection $search, $rel);
}
