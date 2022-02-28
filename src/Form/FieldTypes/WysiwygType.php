<?php

namespace App\Form\FieldTypes;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WysiwygType  extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => [
                'class' => 'wysiwyg-editor form-control',
                'cols' => 5,
                'rows' => 5
            ]
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wysiwyg_editor';
    }

    public function getParent()
    {
        return TextareaType::class;
    }

}