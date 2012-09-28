FSCRestBundle
=============

[![Build Status](https://secure.travis-ci.org/TheFootballSocialClub/FSCRestBundle.png)](http://travis-ci.org/TheFootballSocialClub/FSCRestBundle)

This bundle will generate a REST HATEOAS api based on the configuration you'll do.
ATM it can only generate a read only api, backed by doctrine ORM.
The representations are optimized for the XML format, but are also usable in JSON.

If you create relations between resources, the bundle will automatically create links between them.

The design obviously took many shortchuts, and is not a reference of OOP design ... but it works.
But it's easy to override behavior because you need to the extend the classe that does everything,
for each resource...

This bundle was extracted from an app, and need some love to be usable. (ie composer, make tests work etc ...)

LICENSE
-------

MIT: Resources/meta/LICENSE

TODO
----

* Create a "sanbox" symfony2 app to demonstrate how to fully use the bundle

Installation
------------

Edit your composer.json like this:

```
{
    "require": {
        ...
        "jms/serializer-bundle": "dev-xml-attribute-map as 0.9.0",
        "fsc/rest-bundle": "*"
        ...
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/adrienbrault/JMSSerializerBundle"
        }
    ]
}
```

Update your deps: `composer update`.

Add the bundle to your AppKernel:

```
// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new FSC\RestBundle\FSCRestBundle(),
    new JMS\SerializerBundle\JMSSerializerBundle(),
    new FOS\FOSRestBundle(),
    // ...
);
```

Be aware that this bundle relies on a fork of JMSSerializerBundle, because this PR is still not merged:
https://github.com/schmittjoh/JMSSerializerBundle/pull/164

Example
-------

For an example of what the controller returns: https://gist.github.com/3800069

You need to define services, tagged with `fsc_rest.resource`.

```
services:
    fsc.core.spot.resource.spots:
        class: FSC\Core\SpotBundle\REST\SpotsResource
        parent: fsc.rest.resource.abstract_doctrine
        tags:
          - { name: fsc_rest.resource }
```

```
<?php

namespace FSC\Core\TeamBundle\REST;

use FSC\Core\MainBundle\REST\AbstractSocialEntityResource;
use FSC\Common\RestBundle\Form\Model\Collection;
use FSC\Core\TeamBundle\Repository\FormationRepository;
use FSC\Core\SpotBundle\Repository\SpotRepository;

class TeamsResource extends AbstractSocialEntityResource
{
    protected function configure()
    {
        return array_merge(parent::configure(), array(
            'prefix' => '/teams',
            'entity_class' => 'FSC\Core\TeamBundle\Entity\Team',
        ));
    }

    // Configure root collection ... /teams
    protected function configureCollection()
    {
        return array_merge(parent::configureCollection(), array(
            'xml_root_name' => 'teams',
            'representation_class' => 'FSC\Core\TeamBundle\Model\Representation\Teams',
            'pager_fetch_join_collection' => false,
        ));
    }

    // Configure how each entity representation looks like
    // ie:
    //
    // <team id="">
    //   <name></name>
    //   ...
    // </team>
    protected function configureEntity()
    {
        return array_merge(parent::configureEntity(), array(
            'expanded_collections' => array('formations'),
            'xml_root_name' => 'team',
            'normalize_attributes' => array(
                'id' => 'id',
            ),
            'normalize_elements' => array(
                'name' => 'name',
                'clubName' => 'clubName',
                'category' => 'category',
                'division' => 'division',
                'foundedAt' => 'foundedAt',
            ),
        ));
    }

    // Configure each entity collection, ie: a teams has formations, so you'll have a collection
    // at /teams/{id}/formations
    protected function configureEntityCollections()
    {
        return array_merge_recursive(parent::configureEntityCollections(), array(
            'formations' => array(
                'representation_class' => 'FSC\Core\TeamBundle\Model\Representation\Formations',

                // Which resources should be asked to normalize the collection elements ...
                'resources' => array(
                    'FSC\Core\TeamBundle\Entity\Formation' => 'fsc.core.team.resource.formations',
                ),
                'create_qb' => function ($em, $repository, $entity) {
                    $formationRepository = $em->getRepository('FSCCoreTeamBundle:Formation'); /** @var $formationRepository FormationRepository */

                    return $formationRepository->createSelectByTeamQB($entity);
                },
            ),
            'official-spots' => array(
                'representation_class' => 'FSC\Core\SpotBundle\Model\Representation\Spots',
                'resources' => array(
                    'FSC\Core\SpotBundle\Entity\Spot' => 'fsc.core.spot.resource.spots',
                ),
                'create_qb' => function ($em, $repository, $team) {
                    $spotRepository = $em->getRepository('FSCCoreSpotBundle:Spot'); /** @var $spotRepository SpotRepository */

                    return $spotRepository->createSelectNonDeletedByOfficialTeamQB($team);
                },
            ),
        ));
    }
}
```

```
<?php

namespace FSC\Core\SpotBundle\REST;

use FSC\Core\MainBundle\REST\AbstractSocialEntityResource;
use FSC\Common\RestBundle\Form\Model\Collection;
use FSC\Core\TeamBundle\Repository\TeamRepository;

class SpotsResource extends AbstractSocialEntityResource
{
    protected function configure()
    {
        return array_merge(parent::configure(), array(
            'prefix' => '/spots',
            'entity_class' => 'FSC\Core\SpotBundle\Entity\Spot',
        ));
    }

    protected function configureCollection()
    {
        return array_merge(parent::configureCollection(), array(
            'xml_root_name' => 'spots',
            'representation_class' => 'FSC\Core\SpotBundle\Model\Representation\Spots',
            'pager_fetch_join_collection' => false,
        ));
    }

    protected function configureEntity()
    {
        return array_merge(parent::configureEntity(), array(
            'xml_root_name' => 'spot',
            'normalize_attributes' => array(
                'id' => 'id',
            ),
            'normalize_elements' => array(
                'name' => 'name',
                'description' => 'description',
                'subCategory' => 'subCategory',
                'category' => 'category',
                'surface' => 'surface',
                'previousNames' => 'previousNames',
                'capacity' => 'capacity',
                'facts' => 'facts',
                'opened' => 'opened',
                'architect' => 'architect',
            ),
        ));
    }

    protected function configureEntityCollections()
    {
        return array_merge_recursive(parent::configureEntityCollections(), array(
            'official-teams' => array(
                'representation_class' => 'FSC\Core\TeamBundle\Model\Representation\Teams',
                'resources' => array(
                    'FSC\Core\TeamBundle\Entity\Team' => 'fsc.core.team.resource.teams',
                ),
                'create_qb' => function ($em, $repository, $spot) {
                    $teamRepository = $em->getRepository('FSCCoreTeamBundle:Team'); /** @var $teamRepository TeamRepository */

                    return $teamRepository->createSelectByOfficialSpotQB($spot);
                },
            ),
        ));
    }

    protected function configureEntityRelations()
    {
        return array_merge(parent::configureEntityRelations(), array(
            'creator' => array(
                'get_relation' => function ($em, $repository, $spot) {
                    return $spot->getSpotCreator();
                },
                'resources' => array(
                    'FSC\Core\UserBundle\Entity\User' => 'fsc.core.user.resource.users',
                ),
            ),
        ));
    }
}
```


More details
------------

When you request a resource, the bundle will:

```
Entity --(normalize)--> Representation --(serialize)--> XML/JSON
             ||                              ||
        RESTResources                   JMSSerializer
```


