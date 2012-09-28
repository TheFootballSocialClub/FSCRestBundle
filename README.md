FSCRestBundle
=============

This bundle will generate a REST HATEOAS api based on the configuration you'll do.
ATM it can only generate a read only api, backed by doctrine ORM.

The design tooks obviously many shortchuts, and is not an example of OOP design, but it works.

This bundle was extracted from an app, and need some love to be usable. (ie composer, make tests work etc ...)

Be aware that this bundle relies on a fork of JMSSerializerBundle, because this PR is still not merged:
https://github.com/schmittjoh/JMSSerializerBundle/pull/164

Example
-------

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
use FSC\Core\SpotBundle\Manager\LinkSpotRoleCreatorManager;

class TeamsResource extends AbstractSocialEntityResource
{
    protected function configure()
    {
        return array_merge(parent::configure(), array(
            'prefix' => '/teams',
            'entity_class' => 'FSC\Core\TeamBundle\Entity\Team',
        ));
    }

    protected function configureCollection()
    {
        return array_merge(parent::configureCollection(), array(
            'xml_root_name' => 'teams',
            'representation_class' => 'FSC\Core\TeamBundle\Model\Representation\Teams',
            'pager_fetch_join_collection' => false,
        ));
    }

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

    protected function configureEntityCollections()
    {
        return array_merge_recursive(parent::configureEntityCollections(), array(
            'formations' => array(
                'representation_class' => 'FSC\Core\TeamBundle\Model\Representation\Formations',
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
use FSC\Core\SpotBundle\Manager\LinkSpotRoleCreatorManager;

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
                    return $this->linkManager->getSpotCreator($spot);
                },
                'resources' => array(
                    'FSC\Core\UserBundle\Entity\User' => 'fsc.core.user.resource.users',
                ),
            ),
        ));
    }
}
```
