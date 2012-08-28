<?php

namespace FSC\Common\RestBundle\Test;

use FSC\Common\Test\FunctionalTestCase;

abstract class SerializationTestCase extends FunctionalTestCase
{
    protected function assertSerializedXmlEquals($expectedXml, $value)
    {
        $serializedValue = $this->get('serializer')->serialize($value, 'xml');

        $this->assertEquals(sprintf('<?xml version="1.0" encoding="UTF-8"?>%s%s%s', "\n", $expectedXml, "\n"), $serializedValue);
    }

    protected function assertSerializedJsonEquals($expectedSerializedValue, $value)
    {
        $serializedValue = $this->get('serializer')->serialize($value, 'json');

        $this->assertEquals($expectedSerializedValue, $serializedValue);
    }
}
