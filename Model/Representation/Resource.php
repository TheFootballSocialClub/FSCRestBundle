<?php

namespace FSC\RestBundle\Model\Representation;

use JMS\SerializerBundle\Annotation as Serializer;

use FSC\RestBundle\Model\Representation\Form\Form;

/**
 * Resource
 *
 * @author Adrien Brault <adrien.brault@gmail.com>
 *
 * @Serializer\XmlRoot("resource")
 */
class Resource
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
    protected $links;

    /**
     * @var array
     *
     * @Serializer\XmlList(inline=true, entry="form")
     */
    protected $forms;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->links = array();
        $this->forms = array();
    }

    public function addLink(AtomLink $link)
    {
        $this->links[$link->rel] = $link;
    }

    public function addForm(Form $form)
    {
        $this->forms[$form->rel] = $form;
    }
}
