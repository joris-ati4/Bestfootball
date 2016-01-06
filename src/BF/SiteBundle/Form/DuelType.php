<?php

namespace BF\SiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DuelType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('challenge', 'entity', array(
              'class'    => 'BFSiteBundle:Challenge',
              'property' => 'title',
              'multiple' => false
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BF\SiteBundle\Entity\Duel'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bf_sitebundle_duel';
    }
}
