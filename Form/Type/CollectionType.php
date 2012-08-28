<?php

namespace FSC\Common\RestBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation as DI;

use FSC\Common\RestBundle\Form\EventListener\RemoveNonSubmittedFieldListener;
use FSC\Common\RestBundle\Form\EventListener\ReplaceNullSubmittedValuesByDefaultsListener;

/**
 * @DI\FormType
 */
class CollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $subscriber = new ReplaceNullSubmittedValuesByDefaultsListener($builder->getFormFactory());
        $builder->addEventSubscriber($subscriber);

        $builder->add('page', 'hidden', array('required' => false, 'attr' => array(
            'id' => 'page',
        )));
        $builder->add('limit', 'hidden', array('required' => false, 'attr' => array(
            'id' => 'limit',
        )));
    }

    public function getName()
    {
        return 'fsc_common_rest_collection';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FSC\Common\RestBundle\Form\Model\Collection',
        ));
    }
}
