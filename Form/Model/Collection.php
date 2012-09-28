<?php

namespace FSC\RestBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class Collection
{
    /**
     * @var int
     *
     * @Assert\Type(type = "integer")
     * @Assert\Min(limit = "1")
     */
    protected $page;

    /**
     * @var int
     *
     * @Assert\Type(type = "integer", message = "The value {{ value }} is not a valid {{ type }}.")
     * @Assert\Range(
     *      min = "1",
     *      max = "100"
     * )
     */
    protected $limit;

    public function __construct($limit = 10, $page = 1)
    {
        $this->limit = $limit;
        $this->page = $page;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $page
     */
    public function setPage($page)
    {
        $this->page = (int) $page;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }
}
