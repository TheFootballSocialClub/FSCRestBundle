<?php

namespace FSC\Common\RestBundle\Tests\Functional\Model\Representation;

use FSC\Common\RestBundle\Test\SerializationTestCase;

use FSC\Common\RestBundle\Model\Representation\Resource;
use FSC\Common\RestBundle\Model\Representation\AtomLink;

class ResourceTest extends SerializationTestCase
{
    public function testSerialization()
    {
        $resource = new Resource();

        $resource->addLink(AtomLink::create('self', 'http://fsc.com/resource/1', 'application/vnd.com.fsc+xml'));
        $resource->addLink(AtomLink::create('edit', 'http://fsc.com/resource/1/edit', 'application/vnd.com.fsc+xml'));

        $this->assertSerializedXmlEquals(
'<resource>
  <link rel="self" href="http://fsc.com/resource/1" type="application/vnd.com.fsc+xml"/>
  <link rel="edit" href="http://fsc.com/resource/1/edit" type="application/vnd.com.fsc+xml"/>
</resource>',
            $resource
        );
    }

    public function testLinksOrder2Serialization()
    {
        $resource = new Resource();

        $resource->addLink(AtomLink::create('edit', 'http://fsc.com/resource/1/edit', 'application/vnd.com.fsc+xml'));
        $resource->addLink(AtomLink::create('self', 'http://fsc.com/resource/1', 'application/vnd.com.fsc+xml'));

        $this->assertSerializedXmlEquals(
'<resource>
  <link rel="edit" href="http://fsc.com/resource/1/edit" type="application/vnd.com.fsc+xml"/>
  <link rel="self" href="http://fsc.com/resource/1" type="application/vnd.com.fsc+xml"/>
</resource>',
            $resource
        );
    }

    public function testJsonSerialization()
    {
        // This test makes sure that even json, the json serialization include all needed data

        $resource = new Resource();

        $resource->addLink(AtomLink::create('self', 'http://fsc.com/resource/1', 'application/vnd.com.fsc+xml'));
        $resource->addLink(AtomLink::create('edit', 'http://fsc.com/resource/1/edit', 'application/vnd.com.fsc+xml'));

        $this->assertSerializedJsonEquals(
'{'
    .'"links":{'
        .'"self":{"rel":"self","href":"http:\/\/fsc.com\/resource\/1","type":"application\/vnd.com.fsc+xml"},'
        .'"edit":{"rel":"edit","href":"http:\/\/fsc.com\/resource\/1\/edit","type":"application\/vnd.com.fsc+xml"}'
    .'},'
    .'"forms":[]'
.'}',
            $resource
        );
    }
}
