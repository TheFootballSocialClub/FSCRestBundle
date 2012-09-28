<?php

namespace FSC\RestBundle\Model\Representation\Common;

use JMS\SerializerBundle\Annotation as Serializer;

/**
 * Measure
 *
 * @author Adrien Brault <adrien.brault@gmail.com>
 *
 * @Serializer\XmlRoot("measure")
 */
class Measure
{
    /**
     * @var string
     *
     * @Serializer\XmlValue
     */
    public $value;

    /**
     * @var string
     *
     * @Serializer\XmlAttribute
     */
    public $unit;

    /**
     * @param string      $value
     * @param string|null $unit
     *
     * @return Measure
     */
    public static function create($value, $unit = null)
    {
        $measure = new static();

        $measure->value = $value;
        $measure->unit = $unit;

        return $measure;
    }
}
