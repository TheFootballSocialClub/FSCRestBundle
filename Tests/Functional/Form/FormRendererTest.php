<?php

namespace FSC\Common\RestBundle\Tests\Functional;

use FSC\Common\Test\FunctionalTestCase;

use FSC\Common\RestBundle\Form\Model\Collection;
use FSC\Common\RestBundle\Form\RestRendererEngine;

class FormRendererTest extends FunctionalTestCase
{
    public function testTextFields()
    {
        $form = $this->get('form.factory')->createBuilder('form')
            ->add('name', 'text')
            ->add('description', 'textarea')
            ->add('email', 'email')
            ->add('age', 'integer')
            ->add('height', 'number')
            ->add('password', 'password')
            ->add('progress', 'percent')
            ->add('query', 'search')
            ->add('website', 'url')
            ->getForm();

        $formRenderer = $this->get('fsc.common.rest.form.renderer');

        $formView = $form->createView();
        $renderedFormView = $formRenderer->searchAndRenderBlock($formView, 'rest');

        $this->assertTrue(is_array($renderedFormView));

        $index = 0;

        // name
        $this->assertInstanceOf('FSC\Common\RestBundle\Model\Representation\Form\Input', $renderedFormView[$index]);
        $this->assertEquals('text', $renderedFormView[$index]->attributes['type']);
        $index++;

        // description
        $this->assertInstanceOf('FSC\Common\RestBundle\Model\Representation\Form\Textarea', $renderedFormView[$index]);
        $index++;

        // email
        $this->assertInstanceOf('FSC\Common\RestBundle\Model\Representation\Form\Input', $renderedFormView[$index]);
        $this->assertEquals('email', $renderedFormView[$index]->attributes['type']);
        $index++;

        // age
        $this->assertInstanceOf('FSC\Common\RestBundle\Model\Representation\Form\Input', $renderedFormView[$index]);
        $this->assertEquals('integer', $renderedFormView[$index]->attributes['type']);
        $index++;

        // height
        $this->assertInstanceOf('FSC\Common\RestBundle\Model\Representation\Form\Input', $renderedFormView[$index]);
        $this->assertEquals('number', $renderedFormView[$index]->attributes['type']);
        $index++;

        // password
        $this->assertInstanceOf('FSC\Common\RestBundle\Model\Representation\Form\Input', $renderedFormView[$index]);
        $this->assertEquals('password', $renderedFormView[$index]->attributes['type']);
        $index++;

        // progress
        $this->assertInstanceOf('FSC\Common\RestBundle\Model\Representation\Form\Input', $renderedFormView[$index]);
        $this->assertEquals('text', $renderedFormView[$index]->attributes['type']);
        $index++;

        // query
        $this->assertInstanceOf('FSC\Common\RestBundle\Model\Representation\Form\Input', $renderedFormView[$index]);
        $this->assertEquals('search', $renderedFormView[$index]->attributes['type']);
        $index++;

        // website
        $this->assertInstanceOf('FSC\Common\RestBundle\Model\Representation\Form\Input', $renderedFormView[$index]);
        $this->assertEquals('url', $renderedFormView[$index]->attributes['type']);
        $index++;

        $this->assertCount($index, $renderedFormView);
    }
}
