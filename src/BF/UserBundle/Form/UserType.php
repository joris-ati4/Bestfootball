<?php

namespace BF\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('firstname', 'text')
            ->add('birthday', 'date')
            ->add('city', 'text')
            ->add('country', 'choice', array('choices' => array('Belgium' => 'Belgium', 'France' => 'France', 'Germany' => 'Germany', 'USA' => 'USA'),'choices_as_values' => true))
            ->add('gender', 'choice', array('choices' => array('Male' => 'Male', 'Female' => 'Female'),'choices_as_values' => true))
            ->add('footballClub', 'text')
            ->add('fieldPosition', 'choice', array('choices' => array('Goal Keeper' => 'Goal Keeper', 'Defensive' => 'Defensive', 'Mid-field' => 'Mid-field', 'Attack' => 'Attack'),'choices_as_values' => true))
            ->add('foot', 'choice', array('choices' => array('Left' => 'Left', 'Right' => 'Right', 'Both feet' => 'Both Feet'),'choices_as_values' => true))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BF\UserBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bf_userbundle_user';
    }
}
