<?php

namespace App\Form;

use App\Entity\CropVariantCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CropVariantCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('crop_variants', CollectionType::class, [
                'entry_type'   => CropVariantType::class,
                'entry_options' => [
                    'attr' => [
                        'class' => 'item',
                    ],
                ],
                'allow_add'    => true,
                'allow_delete' => true,
                'prototype'    => true,
                'required'     => true,
                'by_reference' => true,
                'delete_empty' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Сохранить',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CropVariantCollection::class,
        ]);
    }
}
