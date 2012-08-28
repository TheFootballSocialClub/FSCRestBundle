<?php

namespace FSC\Common\RestBundle\Tests\Model\Representation\Common;

use FSC\Common\RestBundle\Model\Representation\Common\Measure;

class MeasureTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $value = '73';
        $unit = 'kilogram';

        $measure = Measure::create($value, $unit);
        $measureWithoutUnit = Measure::create($value);

        $this->assertInstanceOf('FSC\Common\RestBundle\Model\Representation\Common\Measure', $measure);
        $this->assertEquals($value, $measure->value);
        $this->assertEquals($unit, $measure->unit);

        $this->assertInstanceOf('FSC\Common\RestBundle\Model\Representation\Common\Measure', $measure);
        $this->assertEquals($value, $measureWithoutUnit->value);
        $this->assertNull($measureWithoutUnit->unit);
    }
}
