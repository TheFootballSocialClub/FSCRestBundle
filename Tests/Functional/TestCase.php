<?php

namespace FSC\RestBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TestCase extends WebTestCase
{
    protected static function createKernel(array $options = array())
    {
        return new AppKernel('test', true);
    }

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
    }

    public function get($name)
    {
        return static::$kernel->getContainer()->get($name);
    }
}
