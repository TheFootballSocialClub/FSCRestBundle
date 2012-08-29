<?php

namespace FSC\Common\RestBundle\REST;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use FSC\Common\RestBundle\Form\Model\Collection;

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
            'create_qb' => function () {
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
        $qb = $createQb();

        return $this->createORMPager($qb, $search);
    }

    public function getEntityCollectionPager($entity, Collection $search, $rel)
    {
        $createQb = $this->getConfigurationEntityCollections()[$rel]['create_qb'];
        $qb = $createQb($entity);

        return $this->createORMPager($qb, $search);
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
    protected function createORMPager($query, Collection $collection = null)
    {
        $pager = new Pagerfanta(new DoctrineORMAdapter($query));

        if (null !== $collection) {
            $pager->setMaxPerPage($collection->getLimit());
            $pager->setCurrentPage($collection->getPage());
        }

        return $pager;
    }
}
