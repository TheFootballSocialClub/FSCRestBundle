<?php

namespace FSC\RestBundle\Tests\Functional\Serializer;

use FSC\RestBundle\Test\SerializationTestCase;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

use FSC\RestBundle\Normalizer\FormNormalizer;

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

        $formNormalizer = new FormNormalizer();
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
