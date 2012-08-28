<?php

namespace FSC\Common\RestBundle\Model\Representation;

use JMS\SerializerBundle\Annotation as Serializer;

/**
 * Collection
 *
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class Collection extends Resource
{
    /**
     * @var int
     *
     * @Serializer\XmlAttribute
     */
    public $total;

    /**
     * @var int
     *
     * @Serializer\XmlAttribute
     */
    public $page;

    /**
     * @var int
     *
     * @Serializer\XmlAttribute
     */
    public $limit;

    /**
     * @var array
     *
     * @Serializer\XmlList(inline=true)
     */
    public $results;
}
