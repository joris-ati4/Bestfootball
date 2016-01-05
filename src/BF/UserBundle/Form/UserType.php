<?php

namespace BF\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use BF\SiteBundle\Entity\Country;

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
            ->add('gender', 'choice', array('choices' => array('Male' => 'Male', 'Female' => 'Female'),'choices_as_values' => true))
            ->add('footballClub', 'text')
            ->add('fieldPosition', 'choice', array('choices' => array('Goal Keeper' => 'Goal Keeper', 'Defensive' => 'Defensive', 'Mid-field' => 'Mid-field', 'Attack' => 'Attack'),'choices_as_values' => true))
            ->add('foot', 'choice', array('choices' => array('Left' => 'Left', 'Right' => 'Right', 'Both feet' => 'Both Feet'),'choices_as_values' => true))
            ->add('country', 'entity', array(
              'class'    => 'BFSiteBundle:Country',
              'property' => 'name',
              'multiple' => false
            ))
        ;


            $formModifier = function (FormInterface $form, Country $country = null) {
            $states = null === $country ? array() : $country->getStates();

            $form->add('state', 'entity', array(
                'class'       => 'BFSiteBundle:State',
                'property' => 'name',
                'choices'     => $states,
            ));
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                // this would be your entity, i.e. SportMeetup
                $data = $event->getData();

                $formModifier($event->getForm(), $data->getCountry());
            }
        );

        $builder->get('country')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $country = $event->getForm()->getData();

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback functions!
                $formModifier($event->getForm()->getParent(), $country);
            }
        );
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
