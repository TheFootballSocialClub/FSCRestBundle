<?php

namespace FSC\RestBundle\Tests\Model\Representation;

use FSC\RestBundle\Model\Representation\AtomLink;

class AtomLinkTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $rel = 'self';
        $href = 'http://fsc.com';
        $type = 'application/vnd.com.fsc+xml';

        $atomLink = AtomLink::create($rel, $href, $type);
        $atomLinkWithoutType = AtomLink::create($rel, $href);

        $this->assertInstanceOf('FSC\RestBundle\Model\Representation\AtomLink', $atomLink);
        $this->assertEquals($rel, $atomLink->rel);
        $this->assertEquals($href, $atomLink->href);
        $this->assertEquals($type, $atomLink->type);

        $this->assertInstanceOf('FSC\RestBundle\Model\Representation\AtomLink', $atomLinkWithoutType);
        $this->assertEquals($rel, $atomLinkWithoutType->rel);
        $this->assertEquals($href, $atomLinkWithoutType->href);
    }
}
