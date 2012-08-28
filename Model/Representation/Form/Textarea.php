<?php

namespace FSC\Common\RestBundle\Model\Representation\Form;

use JMS\SerializerBundle\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("textarea")
 */
class Textarea extends Element
{
    /**
     * @Serializer\XmlValue
     */
    public $value;
}
