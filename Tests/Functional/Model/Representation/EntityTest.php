<?php

namespace FSC\Common\RestBundle\Tests\Functional\Model\Representation;

use FSC\Common\RestBundle\Test\SerializationTestCase;

use FSC\Common\RestBundle\Model\Representation\Entity;
use FSC\Common\RestBundle\Model\Representation\AtomLink;

class EntityTest extends SerializationTestCase
{
    public function testSerialization()
    {
        $entity = new Entity();
        $entity->addLink(AtomLink::create('self', 'http://fsc.com/entity/1'));

        $entity->addLink(AtomLink::create('edit', 'http://fsc.com/entity/1/edit'));
        $entity->addLink(AtomLink::create('delete', 'http://fsc.com/entity/1/delete'));

        $entity->setAttribute('id', '1');
        $entity->setAttribute('happy', 'true');
        $entity->setAttribute('parentId', '34');
        $entity->setElement('name', 'Adrien');
        $entity->setElement('description', 'someDescriptionValue');

        $this->assertSerializedXmlEquals(
'<result id="1" happy="true" parentId="34">
  <link rel="self" href="http://fsc.com/entity/1"/>
  <link rel="edit" href="http://fsc.com/entity/1/edit"/>
  <link rel="delete" href="http://fsc.com/entity/1/delete"/>
  <name><![CDATA[Adrien]]></name>
  <description><![CDATA[someDescriptionValue]]></description>
</result>',
            $entity
        );
    }
}
