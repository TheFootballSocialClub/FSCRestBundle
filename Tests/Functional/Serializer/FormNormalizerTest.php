<?php

namespace FSC\Common\RestBundle\Tests\Functional\Normalizer;

use FSC\Common\RestBundle\Test\SerializationTestCase;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

use FSC\Common\RestBundle\Normalizer\FormNormalizer;

class FormNormalizerTest extends SerializationTestCase
{
    public function testSerialize()
    {
        $form = $form = $this->get('form.factory')->createBuilder('form')
            ->add('name', 'text')
            ->add('description', 'textarea')
            ->getForm();
        $form->bind(array(
            'name' => 'Adrien',
            'description' => 'THIS IS SPARTA',
        ));

        $formRenderer = $this->get('fsc.common.rest.form.renderer');

        $formNormalizer = new FormNormalizer($formRenderer);
        $symfonySerializer = new SymfonySerializer(array($formNormalizer));

        $normalizedForm = $symfonySerializer->normalize($form);

        $this->assertSerializedXmlEquals(
'<form>
  <input type="text" value="Adrien" name="form[name]" required="required"/>
  <textarea name="form[description]" required="required"><![CDATA[THIS IS SPARTA]]></textarea>
</form>',
            $normalizedForm
        );
    }
}
