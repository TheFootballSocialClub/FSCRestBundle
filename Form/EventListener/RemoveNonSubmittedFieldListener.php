<?php

namespace FSC\Common\RestBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;

/**
 * RemoveNonSubmittedFieldListener
 *
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class RemoveNonSubmittedFieldListener implements EventSubscriberInterface
{
    private $factory;

    public function __construct(FormFactoryInterface $factory)
    {
        $this->factory = $factory;
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
        $submittedData = $event->getData();
        $form = $event->getForm();

        // We remove every child that has no data to bind, to avoid "overriding" the form default data
        foreach ($form->all() as $name => $child) {
            if (!isset($submittedData[$name])) {
                $form->remove($name);
            }
        }
    }
}
