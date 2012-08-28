<?php

namespace FSC\Common\RestBundle\Form;

use Symfony\Component\Form\FormRenderer as BaseFormRenderer;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;

class RestFormRenderer extends BaseFormRenderer
{
    public function __construct(RestRendererEngine $engine, CsrfProviderInterface $csrfProvider = null)
    {
        parent::__construct($engine, $csrfProvider);

        $engine->setRenderer($this);
    }
}
