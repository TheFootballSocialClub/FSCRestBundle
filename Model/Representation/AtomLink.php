<?php

namespace FSC\Common\RestBundle\Model\Representation;

use JMS\SerializerBundle\Annotation as Serializer;

/**
 * AtomLink
 *
 * @author Adrien Brault <adrien.brault@gmail.com>
 *
 * @Serializer\XmlRoot("link")
 */
class AtomLink
{
    /**
     * @var string
     *
     * @Serializer\XmlAttribute
     */
    public $rel;

    /**
     * @var string
     *
     * @Serializer\XmlAttribute
     */
    public $href;

    /**
     * @var string
     *
     * @Serializer\XmlAttribute
     */
    public $type;

    /**
     * @param string      $rel
     * @param string      $href
     * @param string|null $type
     *
     * @return AtomLink
     */
    public static function create($rel, $href, $type = null)
    {
        $link = new static();

        $link->rel = $rel;
        $link->href = $href;
        $link->type = $type;

        return $link;
    }
}
