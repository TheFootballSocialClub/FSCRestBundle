<?php

namespace FSC\RestBundle\Model\Representation;

use JMS\SerializerBundle\Annotation as Serializer;

/**
 * Collection
 *
 * @author Adrien Brault <adrien.brault@gmail.com>
 *
 * @Serializer\XmlRoot("collection")
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
