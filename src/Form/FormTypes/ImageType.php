<?php


namespace App\Form\FormTypes;


use App\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageType extends AbstractType
{
    public function getParent()
    {
        return FileType::class;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
            'attr' => [
                'class' => 'image_upload form-control',
            ]
        ]);
    }

    public function getBlockPrefix()
    {
        return 'image_upload';
    }

}