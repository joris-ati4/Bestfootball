<?php

namespace BF\SiteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChallengeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titleFR', 'text')
            ->add('titleEN', 'text')
            ->add('descriptionFR', 'textarea')
            ->add('descriptionEN', 'textarea')
            ->add('endDate', 'datetime')
            ->add('one', 'integer')
            ->add('two', 'integer')
            ->add('three', 'integer')
            ->add('four', 'integer')
            ->add('five', 'integer')
            ->add('six', 'text')
            ->add('partner','checkbox',array('required' => false))
            ->add('logoPartner','text',array('required' => false))
            ->add('nomPartner','text',array('required' => false))
            ->add('lienPartner','text',array('required' => false))
            ->add('descriptionPartnerEN','textarea',array('required' => false))
            ->add('descriptionPartnerFR','textarea',array('required' => false))
            ->add('firstPrizeTitleEN','text',array('required' => false))
            ->add('firstPrizeTitleFR','text',array('required' => false))
            ->add('firstPrizeLogo','text',array('required' => false))
            ->add('secondPrizeTitleEN','text',array('required' => false))
            ->add('secondPrizeTitleFR','text',array('required' => false))
            ->add('secondPrizeLogo','text',array('required' => false))
            ->add('thirdPrizeTitleEN','text',array('required' => false))
            ->add('thirdPrizeTitleFR','text',array('required' => false))
            ->add('thirdPrizeLogo','text',array('required' => false))
            ->add('file', 'file')
            ->add('save', 'submit')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BF\SiteBundle\Entity\Challenge'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bf_sitebundle_challenge';
    }
}
