<?php

namespace FSC\Common\RestBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormRendererInterface;

use FSC\Common\RestBundle\Model\Representation\Form as FormRepresentations;

/**
 * FormNormalizer
 *
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class FormNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    /**
     * @var FormRendererInterface
     */
    protected $formRenderer;

    /**
     * @param FormRendererInterface $formRenderer
     */
    public function __construct(FormRendererInterface $formRenderer)
    {
        $this->formRenderer = $formRenderer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null)
    {
        if ($object instanceof Form) {
            $object = $object->createView();
        }

        $formView = $object; /** @var $formView FormView */

        $formElements = $this->formRenderer->searchAndRenderBlock($formView, 'rest');

        $formRepresentation = new FormRepresentations\Form();

        $formRepresentation->inputs = array_filter($formElements, function ($element) {
            return $element instanceof FormRepresentations\Input;
        });

        $formRepresentation->textareas = array_filter($formElements, function ($element) {
            return $element instanceof FormRepresentations\Textarea;
        });

        return $formRepresentation;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Form || $data instanceof FormView;
    }
}
