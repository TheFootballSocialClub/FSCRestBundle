<?php

namespace FSC\RestBundle\Tests\Functional\Model\Representation;

use FSC\RestBundle\Test\SerializationTestCase;

use FSC\RestBundle\Model\Representation\Form\Form;
use FSC\RestBundle\Model\Representation\AtomLink;

class FormTest extends SerializationTestCase
{
    public function testSerialization()
    {
        $form = new Form();
        $form->links[] = AtomLink::create('self', 'http://fsc.com/aform');

        $form->method = 'POST';
        $form->action = 'http://fsc.com/aresource';

        $this->assertSerializedXmlEquals(
'<form method="POST" action="http://fsc.com/aresource">
  <link rel="self" href="http://fsc.com/aform"/>
</form>',
            $form
        );
    }
}
