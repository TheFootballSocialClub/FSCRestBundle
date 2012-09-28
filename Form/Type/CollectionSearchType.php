<?php

namespace FSC\RestBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\FormType
 */
class CollectionSearchType extends CollectionType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('query', 'text', array('attr' => array(
            'id' => 'query',
        )));
    }

    public function getName()
    {
        return 'fsc_rest_collection_search';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'data_class' => 'FSC\RestBundle\Form\Model\CollectionSearch',
        ));
    }
}
