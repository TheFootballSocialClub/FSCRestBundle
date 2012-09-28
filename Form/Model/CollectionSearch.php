<?php

namespace FSC\RestBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class CollectionSearch extends Collection
{
    /**
     * @var string
     *
     * @Assert\Type(type = "string")
     */
    protected $query;

    /**
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }
}
