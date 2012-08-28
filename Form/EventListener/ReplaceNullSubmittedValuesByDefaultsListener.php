<?php

namespace FSC\Common\RestBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;

/**
 * ReplaceNullSubmittedValuesByDefaultsListener
 *
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class ReplaceNullSubmittedValuesByDefaultsListener implements EventSubscriberInterface
{
    private $factory;
    private $ignoreRequiredFields;

    public function __construct(FormFactoryInterface $factory, $ignoreRequiredFields = true)
    {
        $this->factory = $factory;
        $this->ignoreRequiredFields = $ignoreRequiredFields;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_BIND => 'preBind');
    }

    /**
     * {@inheritdoc}
     */
    public function preBind(FormEvent $event)
    {
        $form = $event->getForm();
        $submittedData = $event->getData();

        if ($form->getConfig()->getCompound()) {
            foreach ($form->all() as $name => $child) {
                if (!isset($submittedData[$name])
                    && (!$this->ignoreRequiredFields || !$child->isRequired())) {
                    $submittedData[$name] = $child->getData();
                }
            }
        }

        $event->setData($submittedData);
    }
}
