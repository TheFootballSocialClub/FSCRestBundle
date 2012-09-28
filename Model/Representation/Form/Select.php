<?php

namespace FSC\RestBundle\Model\Representation\Form;

use JMS\SerializerBundle\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("select")
 */
class Select extends Element
{
    /**
     * @var array
     *
     * @Serializer\XmlList(inline = true, entry = "option")
     */
    public $options = array();
}
