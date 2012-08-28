<?php

namespace FSC\Common\RestBundle\Model\Representation\Form;

use JMS\SerializerBundle\Annotation as Serializer;

use FSC\Common\RestBundle\Model\Representation\AtomLink;

/**
 * Form
 *
 * @author Adrien Brault <adrien.brault@gmail.com>
 *
 * @Serializer\XmlRoot("form")
 */
class Form
{
    /**
     * @var string
     *
     * @Serializer\XmlAttribute
     */
    public $rel;

    /**
     * @var array
     *
     * @Serializer\XmlList(inline=true, entry="link")
     */
    public $links;

    /**
     * @var string
     *
     * @Serializer\XmlAttribute
     */
    public $method;

    /**
     * @var string
     *
     * @Serializer\XmlAttribute
     */
    public $action;

    /**
     * @var array
     *
     * @Serializer\XmlList(inline = true, entry = "input")
     */
    public $inputs;

    /**
     * @var array
     *
     * @Serializer\XmlList(inline = true, entry = "textarea")
     */
    public $textareas;

    public function addLink(AtomLink $link)
    {
        $this->links[$link->rel] = $link;
    }
}
