<?php

namespace FSC\Common\RestBundle\Tests\Functional\Model\Representation;

use FSC\Common\RestBundle\Test\SerializationTestCase;

use FSC\Common\RestBundle\Model\Representation\AtomLink;

class AtomLinkTest extends SerializationTestCase
{
    public function testEmptySerialization()
    {
        $atomLink = new AtomLink();

        $this->assertSerializedXmlEquals(
            '<link/>',
            $atomLink
        );
    }

    public function testClassicSerialization()
    {
        $atomLink = AtomLink::create('self', 'http://fsc.com', 'application/vnd.com.fsc+xml');

        $this->assertSerializedXmlEquals(
            '<link rel="self" href="http://fsc.com" type="application/vnd.com.fsc+xml"/>',
            $atomLink
        );
    }

    public function testSerializationWithNoType()
    {
        $atomLink = AtomLink::create('self', 'http://fsc.com');

        $this->assertSerializedXmlEquals('<link rel="self" href="http://fsc.com"/>', $atomLink);
    }
}
