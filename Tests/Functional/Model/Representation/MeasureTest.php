<?php

namespace FSC\Common\RestBundle\Tests\Functional\Model\Representation;

use FSC\Common\RestBundle\Test\SerializationTestCase;

use FSC\Common\RestBundle\Model\Representation\Common\Measure;

class MeasureTest extends SerializationTestCase
{
    public function testSerialization()
    {
        $measure = Measure::create('73', 'kilogram');

        $this->assertSerializedXmlEquals(
            '<measure unit="kilogram"><![CDATA[73]]></measure>',
            $measure
        );
    }
}
