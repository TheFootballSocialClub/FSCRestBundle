<?php

namespace FSC\RestBundle\Model\Representation\Form;

use JMS\SerializerBundle\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("option")
 */
class Option extends Element
{
    /**
     * @Serializer\XmlValue
     */
    public $value;
}
