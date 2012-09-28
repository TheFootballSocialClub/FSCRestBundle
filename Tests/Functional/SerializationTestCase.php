<?php

namespace FSC\RestBundle\Tests\Functional;

abstract class SerializationTestCase extends TestCase
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
