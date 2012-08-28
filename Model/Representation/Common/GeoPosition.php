<?php

namespace FSC\Common\RestBundle\Model\Representation\Common;

use JMS\SerializerBundle\Annotation as Serializer;

/**
 * GeoPosition
 *
 * @author Adrien Brault <adrien.brault@gmail.com>
 *
 * @Serializer\XmlRoot("position")
 */
class GeoPosition
{
    /**
     * @var float
     *
     * @Serializer\XmlAttribute
     */
    public $latitude;

    /**
     * @var float
     *
     * @Serializer\XmlAttribute
     */
    public $longitude;

    public static function create($latitude, $longitude)
    {
        $geoPosition = new static();

        $geoPosition->latitude = $latitude;
        $geoPosition->longitude = $longitude;

        return $geoPosition;
    }
}
