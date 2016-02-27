<?php

namespace BF\SiteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MediaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $media = $builder->getForm()->getData();

        $builder
            ->add('image', 'comur_image', array(
            'uploadConfig' => array(
            'uploadRoute' => 'comur_api_upload',        //optional
            'uploadUrl' => $media->getUploadRootDir(),       // required - see explanation below (you can also put just a dir path)
            'webDir' => $media->getUploadDir(),              // required - see explanation below (you can also put just a dir path)
            'fileExt' => '*.jpg;*.gif;*.png;*.jpeg',    //optional
            'libraryDir' => $picture->getUser()->getId(),                       //optional
            'libraryRoute' => 'comur_api_image_library', //optional
            'showLibrary' => true,                      //optional
            
        ),
        'cropConfig' => array(
            'minWidth' => 400,
            'minHeight' => 400,
            'aspectRatio' => true,              //optional
            'cropRoute' => 'comur_api_crop',    //optional
            'forceResize' => false,             //optional
            'thumbs' => array(                  //optional
                array(
                    'maxWidth' => 400,
                    'maxHeight' => 400,
                    'useAsFieldImage' => true  //optional
                )
            )
        )
    ))
        ->add('submit', 'submit')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BF\SiteBundle\Entity\Media'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bf_sitebundle_media';
    }
}
