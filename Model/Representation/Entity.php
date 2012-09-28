<?php

namespace FSC\RestBundle\Model\Representation;

use JMS\SerializerBundle\Annotation as Serializer;

/**
 * Entity
 *
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class Entity extends Resource
{
    /**
     * @var array
     *
     * @Serializer\XmlList(inline=true, entry="collection")
     */
    protected $collections;

    /**
     * @var array
     *
     * @Serializer\XmlList(inline=true, entry="relation")
     */
    protected $relations;

    /**
     * @var array
     *
     * @Serializer\XmlKeyValuePairs()
     * @Serializer\Inline()
     */
    protected $elements;

    /**
     * @var array
     *
     * @Serializer\XmlAttributeMap
     * @Serializer\Inline
     */
    protected $attributes;

    public function addCollection(Collection $collection)
    {
        $this->collections[$collection->rel] = $collection;
    }

    public function addRelation(Entity $entity)
    {
        $this->relations[$entity->rel] = $entity;
    }

    public function setElement($key, $value)
    {
        $this->elements[$key] = $value;
    }

    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }
}
