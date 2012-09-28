<?php

namespace FSC\RestBundle\Tests\Routing;

use FSC\RestBundle\Routing\RouteNameProvider;

class RouteNameProviderTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $routeNameProvider = new RouteNameProvider('acme_hello');

        $this->assertEquals('acme_hello_collection', $routeNameProvider->getCollectionRouteName());
        $this->assertEquals('acme_hello_collection_form_search', $routeNameProvider->getCollectionFormRouteName('search'));
        $this->assertEquals('acme_hello_entity', $routeNameProvider->getEntityRouteName());
        $this->assertEquals('acme_hello_entity_form_edit', $routeNameProvider->getEntityFormRouteName('edit'));
        $this->assertEquals('acme_hello_entity_collection_friends', $routeNameProvider->getEntityCollectionRouteName('friends'));
        $this->assertEquals('acme_hello_entity_collection_friends_form_search', $routeNameProvider->getEntityCollectionFormRouteName('friends', 'search'));
    }
}
