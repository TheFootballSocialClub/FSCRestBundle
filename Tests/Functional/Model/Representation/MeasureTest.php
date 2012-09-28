<?php

namespace FSC\RestBundle\Tests\Functional\Model\Representation;

use FSC\RestBundle\Test\SerializationTestCase;

use FSC\RestBundle\Model\Representation\Common\Measure;

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
