<?php

namespace FSC\RestBundle\Model\Representation\Form;

use JMS\SerializerBundle\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("element")
 */
class Element
{
    /**
     * @Serializer\XmlAttributeMap
     * @Serializer\Inline
     */
    public $attributes = array();
}
