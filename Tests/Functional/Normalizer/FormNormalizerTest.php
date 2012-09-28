<?php

namespace FSC\RestBundle\Tests\Functional\Normalizer;

use FSC\RestBundle\Tests\Functional\TestCase;
use FSC\RestBundle\Form\Model\Collection;
use FSC\RestBundle\Form\RestRendererEngine;

class FormNormalizerTest extends TestCase
{
    public function test()
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
            ->add('gender', 'choice', array(
                'choices' => array('m' => 'male', 'f' => 'female')
            ))
            ->getForm();

        $formNormalizer = $this->get('fsc.rest.normalizer.form');

        $formView = $form->createView();
        $renderedFormView = $formNormalizer->normalize($formView);

        $this->assertInstanceOf('FSC\RestBundle\Model\Representation\Form\Form', $renderedFormView);

        // name
        $this->assertInstanceOf('FSC\RestBundle\Model\Representation\Form\Input', $renderedFormView->inputs[0]);
        $this->assertEquals('text', $renderedFormView->inputs[0]->attributes['type']);

        // description
        $this->assertInstanceOf('FSC\RestBundle\Model\Representation\Form\Textarea', $renderedFormView->textareas[0]);

        // email
        $this->assertInstanceOf('FSC\RestBundle\Model\Representation\Form\Input', $renderedFormView->inputs[1]);
        $this->assertEquals('email', $renderedFormView->inputs[1]->attributes['type']);

        // age
        $this->assertInstanceOf('FSC\RestBundle\Model\Representation\Form\Input', $renderedFormView->inputs[2]);
        $this->assertEquals('integer', $renderedFormView->inputs[2]->attributes['type']);

        // height
        $this->assertInstanceOf('FSC\RestBundle\Model\Representation\Form\Input', $renderedFormView->inputs[3]);
        $this->assertEquals('number', $renderedFormView->inputs[3]->attributes['type']);

        // password
        $this->assertInstanceOf('FSC\RestBundle\Model\Representation\Form\Input', $renderedFormView->inputs[4]);
        $this->assertEquals('password', $renderedFormView->inputs[4]->attributes['type']);

        // progress
        $this->assertInstanceOf('FSC\RestBundle\Model\Representation\Form\Input', $renderedFormView->inputs[5]);
        $this->assertEquals('text', $renderedFormView->inputs[5]->attributes['type']);

        // query
        $this->assertInstanceOf('FSC\RestBundle\Model\Representation\Form\Input', $renderedFormView->inputs[6]);
        $this->assertEquals('search', $renderedFormView->inputs[6]->attributes['type']);

        // website
        $this->assertInstanceOf('FSC\RestBundle\Model\Representation\Form\Input', $renderedFormView->inputs[7]);
        $this->assertEquals('url', $renderedFormView->inputs[7]->attributes['type']);

        // gender
        $this->assertInstanceOf('FSC\RestBundle\Model\Representation\Form\Select', $renderedFormView->selects[0]);
        $this->assertInstanceOf('FSC\RestBundle\Model\Representation\Form\Option', $renderedFormView->selects[0]->options[0]);
        $this->assertEquals('m', $renderedFormView->selects[0]->options[0]->attributes['value']);
        $this->assertEquals('male', $renderedFormView->selects[0]->options[0]->value);
        $this->assertInstanceOf('FSC\RestBundle\Model\Representation\Form\Option', $renderedFormView->selects[0]->options[1]);
        $this->assertEquals('f', $renderedFormView->selects[0]->options[1]->attributes['value']);
        $this->assertEquals('female', $renderedFormView->selects[0]->options[1]->value);
    }
}
