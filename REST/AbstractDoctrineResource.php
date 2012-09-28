<?php

namespace FSC\RestBundle\REST;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use FSC\RestBundle\Form\Model\Collection;

abstract class AbstractDoctrineResource extends AbstractResource
{
    /** @var EntityManager */
    protected $entityManager;

    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function configureCollection()
    {
        return array_merge(parent::configureCollection(), array(
            'pager_fetch_join_collection' => true,
            'create_qb' => function ($em, $repository) {
                $alias = substr($this->guessResourceName(), 0, 1);

                return $this->getRepository()->createQueryBuilder($alias);
            },
        ));
    }

    public function getEntity(Request $request)
    {
        $entity = $this->getRepository()->find($request->attributes->get('id'));

        if (null === $entity) {
            throw new NotFoundHttpException();
        }

        return $entity;
    }

    public function getCollectionPager(Collection $search)
    {
        $createQb = $this->getConfigurationCollection()['create_qb'];
        $qb = $createQb($this->entityManager, $this->getRepository());

        return $this->createORMPager($qb, $search, $this->getConfigurationCollection()['pager_fetch_join_collection']);
    }

    public function getEntityCollectionPager($entity, Collection $search, $rel)
    {
        $createQb = $this->getConfigurationEntityCollections()[$rel]['create_qb'];
        $qb = $createQb($this->entityManager, $this->getRepository(), $entity);

        return $this->createORMPager($qb, $search, @$this->getConfigurationEntityCollections()[$rel]['pager_fetch_join_collection'] !== false);
    }

    public function getEntityRelation($entity, $entityRelationRel)
    {
        $getRelation = $this->getConfigurationEntityRelations()[$entityRelationRel]['get_relation'];

        return $getRelation($this->entityManager, $this->getRepository(), $entity);
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
    protected function createORMPager($query, Collection $collection = null, $fetchJoinCollection = false)
    {
        $pager = new Pagerfanta(new DoctrineORMAdapter($query, $fetchJoinCollection));

        if (null !== $collection) {
            $pager->setMaxPerPage($collection->getLimit());
            $pager->setCurrentPage($collection->getPage());
        }

        return $pager;
    }
}
