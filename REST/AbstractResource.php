<?php

namespace FSC\RestBundle\REST;

use Doctrine\Common\Util\ClassUtils;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Util\PropertyPath;
use Symfony\Component\Form\Form;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use FSC\RestBundle\Form\Model\Collection;
use FSC\RestBundle\Form\Type\CollectionType;
use FSC\RestBundle\Model\Representation\Collection as CollectionRepresentation;
use FSC\RestBundle\Routing\RouteNameProviderInterface;
use FSC\RestBundle\Routing\RouteNameProvider;
use FSC\RestBundle\Routing\UrlGeneratorInterface;
use FSC\RestBundle\Routing\UrlGenerator;
use FSC\RestBundle\Normalizer\FormNormalizer;
use FSC\RestBundle\REST\Description\FormDescription;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
abstract class AbstractResource
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var FormNormalizer
     */
    protected $formNormalizer;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var AtomLinkFactory
     */
    protected $atomLinkFactory;

    /**
     * @var RouteNameProviderInterface
     */
    protected $routeNameProvider;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    private $configuration;
    private $configurationCollection;
    private $configurationCollectionSearch;
    private $configurationEntity;
    private $configurationEntityCollections;
    private $configurationEntityRelations;

    public function __construct(RouterInterface $router, FormNormalizer $formNormalizer, FormFactory $formFactory,
                                AtomLinkFactory $atomLinkFactory, ContainerInterface $container)
    {
        $this->configuration = $this->configure();
        $this->configurationCollection = $this->configureCollection();
        $this->configurationCollectionSearch = $this->configureCollectionSearch();
        $this->configurationEntity = $this->configureEntity();
        $this->configurationEntityCollections = $this->configureEntityCollections();
        $this->configurationEntityRelations = $this->configureEntityRelations();

        $this->router = $router;
        $this->formNormalizer = $formNormalizer;
        $this->formFactory = $formFactory;
        $this->atomLinkFactory = $atomLinkFactory;
        $this->container = $container;

        $this->routeNameProvider = new RouteNameProvider($this->getConfiguration()['route_name_prefix']);
        $this->urlGenerator = new UrlGenerator($this->router, $this->routeNameProvider);

        // Normalizer
        foreach ($this->configurationEntityCollections as $rel => $config) {
            $this->configurationEntityCollections[$rel]['route_rel'] = $this->getRouteNameProvider()->normalizeRel($rel);
        }
    }

    /**
     * @param SecurityContextInterface $securityContext
     */
    public function setSecurityContext(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getConfigurationCollection()
    {
        return $this->configurationCollection;
    }

    public function getConfigurationCollectionSearch()
    {
        return $this->configurationCollectionSearch;
    }

    public function getConfigurationEntity()
    {
        return $this->configurationEntity;
    }

    public function getConfigurationEntityCollections()
    {
        return $this->configurationEntityCollections;
    }

    public function getConfigurationEntityRelations()
    {
        return $this->configurationEntityRelations;
    }

    public function getRouteNameProvider()
    {
        return $this->routeNameProvider;
    }

    public function getUrlGenerator()
    {
        return $this->urlGenerator;
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
            'representation_class' => 'FSC\RestBundle\Model\Representation\Collection',
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
            'representation_class' => 'FSC\RestBundle\Model\Representation\Entity',
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
            /*
            'supporters' => array(
                'representation_class' => 'FSC\Core\UserBundle\Model\Representation\Users',
                'resources' => array(
                    'FSC\Core\UserBundle\Entity\User' => 'fsc.core.user.resource.users',
                ),
                'create_qb' => function ($em, $repository, $entity) {
                    return $this->getRepository()->createSelectNonDeletedBySupportedQB($entity);
                },
            ),
            */
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
                'create_qb' => function ($em, $repository, $entity) {
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
            $routeRel = $entityCollection['route_rel'];

            $routes->add($resourceRouteNameProvider->getEntityCollectionRouteName($routeRel), new Route($entityCollectionPrefix, array(
                '_controller' => $serviceId.':getEntityCollectionAction',
            ), array(
                '_method' => 'GET',
            )));

            // Entity Collection Forms
            $entityCollectionFormsPrefix = $entityCollectionPrefix.'/forms';

            $formRel = 'search';
            $routes->add(
                $resourceRouteNameProvider->getEntityCollectionFormRouteName($routeRel, $formRel),
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
            if (isset($configurationEntityCollection['can_see']) && !$configurationEntityCollection['can_see']($this->securityContext, $entity)) {
                continue;
            }

            if ($doNotExpandRelationsAndCollections || !in_array($entityCollectionRel, $this->getConfigurationEntity()['expanded_collections'])) {
                $entityRepresentation->addLink($this->atomLinkFactory->create(
                    $entityCollectionRel,
                    $this->getUrlGenerator()->generateEntityCollectionUrl($entity, $entityCollectionRel)
                ));
            } else {
                $entityRelationSearchFormDescription = $this->createEntityCollectionSearchFormDescription($entity, $entityCollectionRel);
                $entityRelationPager = $this->getEntityCollectionPager($entity, $entityRelationSearchFormDescription->getData(), $entityCollectionRel);
                $entityRelationRepresentation = $this->normalizeEntityCollection($entity, $entityCollectionRel, $entityRelationPager, $entityRelationSearchFormDescription);

                $entityRelationRepresentation->rel = $this->atomLinkFactory->getRel($entityCollectionRel);
                $entityRepresentation->addCollection($entityRelationRepresentation);
            }
        }

        // Entity relations
        foreach ($this->getConfigurationEntityRelations() as $entityRelationRel => $entityRelationConfiguration) {
            $entityRelation = $this->getEntityRelation($entity, $entityRelationRel);

            if (null === $entityRelation) {
                continue;
            }

            $entityRelationClassName = ClassUtils::getRealClass(get_class($entityRelation));
            $entityRelationResource = $this->container->get($entityRelationConfiguration['resources'][$entityRelationClassName]);

            $canSeeEntityRelation = $entityRelationResource->getConfigurationEntity()['can_see'];

            if (!$canSeeEntityRelation($this->securityContext, $entityRelation)
                || $doNotExpandRelationsAndCollections
                || !in_array($entityRelationRel, $this->getConfigurationEntity()['expanded_relations'])) {
                $entityRepresentation->addLink($this->atomLinkFactory->create(
                    $entityRelationRel,
                    $entityRelationResource->getUrlGenerator()->generateEntityUrl($entityRelation)
                ));
            } else {
                $entityRelationRepresentation = $entityRelationResource->normalizeEntity($entityRelation, true);
                $entityRelationRepresentation->rel = $this->atomLinkFactory->getRel($entityRelationRel);
                $entityRepresentation->addRelation($entityRelationRepresentation);
            }
        }

        return $entityRepresentation;
    }

    public function normalizeCollection(Pagerfanta $pager, FormDescription $formDescription)
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
        $collectionRepresentation->addForm($this->formNormalizer->normalizeFormDescription($formDescription));

        return $collectionRepresentation;
    }

    public function normalizeEntityCollection($entity, $rel, Pagerfanta $pager, FormDescription $formDescription)
    {
        $configurationEntityCollection = $this->getConfigurationEntityCollections()[$rel];

        if (isset($configurationEntityCollection['can_see']) && !$configurationEntityCollection['can_see']($this->securityContext, $entity)) {
            throw new AccessDeniedException();
        }

        $entityCollectionRepresentationClass = $configurationEntityCollection['representation_class'];
        $entityCollectionRepresentation = new $entityCollectionRepresentationClass();

        $this->configureCollectionRepresentation($entityCollectionRepresentation, $pager, $entity, $rel);

        // Results
        $entityCollectionRepresentation->results = array();
        foreach ($pager->getCurrentPageResults() as $entity) {
            $class = ClassUtils::getRealClass(get_class($entity));
            $entityResource = $this->container->get($configurationEntityCollection['resources'][$class]);
            $entityCollectionRepresentation->results[] = $entityResource->normalizeEntity($entity, true);
        }

        // Search form
        $entityCollectionRepresentation->addForm($this->formNormalizer->normalizeFormDescription($formDescription));

        return $entityCollectionRepresentation;
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

    public function createCollectionFormDescription($rel, Request $request = null)
    {
        if ('search' == $rel) {
            return $this->createCollectionSearchFormDescription($request);
        }

        throw new \Exception('Not implemented, sorry!');
    }

    public function createCollectionSearchFormDescription(Request $request = null)
    {
        $createForm = $this->getConfigurationCollectionSearch()['create_form'];
        $createFormData = $this->getConfigurationCollectionSearch()['create_form_object'];
        $form = $createForm();
        $formData = $createFormData();
        $form->setData($formData);

        if (null !== $request) {
            $form->bind($request);
        }

        $rel = 'search';
        $method = 'GET';
        $actionUrl = $this->urlGenerator->generateCollectionUrl();

        $selfUrl = $this->getUrlGenerator()->generateCollectionFormUrl($rel);

        return new FormDescription($rel, $method, $actionUrl, $form, $formData, $selfUrl);
    }

    public function createEntityCollectionFormDescription($rel, $entity, $collectionRel, Request $request = null)
    {
        if ('search' == $rel) {
            return $this->createEntityCollectionSearchFormDescription($entity, $collectionRel, $request);
        }

        throw new \Exception('Not implemented, sorry!');
    }

    public function createEntityCollectionSearchFormDescription($entity, $collectionRel, Request $request = null)
    {
        $createForm = $this->getConfigurationCollectionSearch()['create_form'];
        $createFormData = $this->getConfigurationCollectionSearch()['create_form_object'];
        $form = $createForm();
        $formData = $createFormData();
        $form->setData($formData);

        if (null !== $request) {
            $form->bind($request);
        }

        $rel = 'search';
        $method = 'GET';
        $actionUrl = $this->urlGenerator->generateEntityCollectionUrl($entity, $collectionRel);

        $selfUrl = $this->getUrlGenerator()->generateEntityCollectionFormUrl($entity, $collectionRel, $rel);

        return new FormDescription($rel, $method, $actionUrl, $form, $formData, $selfUrl);
    }

    public function getEntityCollectionRelFromRouteRel($routeRel)
    {
        foreach ($this->configurationEntityCollections as $rel => $config) {
            if ($config['route_rel'] == $routeRel) {
                return $rel;
            }
        }
    }

    abstract public function getEntity(Request $request);
    abstract public function getCollectionPager(Collection $search);
    abstract public function getEntityCollectionPager($entity, Collection $search, $rel);

    public function getEntityRelation($entity, $entityRelationRel)
    {
        $getRelation = $this->getConfigurationEntityRelations()[$entityRelationRel]['get_relation'];

        return $getRelation($entity);
    }
}
