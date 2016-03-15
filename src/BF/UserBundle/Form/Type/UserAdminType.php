<?php

namespace BF\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserAdminType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'text')
            ->add('name', 'text')
            ->add('firstname', 'text')
            ->add('birthday')
            ->add('city', 'text')
            ->add('gender', 'text')
            ->add('footballClub', 'text')
            ->add('fieldPosition', 'text')
            ->add('foot', 'text')
            ->add('points', 'text')
            ->add('duelWins', 'text')
            ->add('duelPoints', 'text')
            ->add('facebook_id', 'text')
            ->add('facebook_access_token', 'text')
            ->add('google_id', 'text')
            ->add('google_access_token', 'text')
            ->add('country', 'entity', array(
              'class'    => 'BFSiteBundle:Country',
              'property' => 'name',
              'multiple' => false
            ))
            ->add('state', 'entity', array(
              'class'    => 'BFSiteBundle:State',
              'property' => 'name',
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
