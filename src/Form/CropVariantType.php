<?php

namespace App\Form;

use App\Entity\CropVariant;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class CropVariantType extends AbstractType
{
    const CROP_MIN = 1;
    const CROP_MAX = 600;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('design_number', TextType::class, [
                'label' => false,
                'disabled' => true
            ])
            ->add('width_teaser_block', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'min' => self::CROP_MIN,
                    'max' => self::CROP_MAX,
                ],
                'constraints' => [
                    new Range([
                        'min' => self::CROP_MIN,
                        'max' => self::CROP_MAX,
                    ]),
                    new NotBlank()
                ],
                'required' => false
            ])
            ->add('height_teaser_block', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'min' => self::CROP_MIN,
                    'max' => self::CROP_MAX,
                ],
                'constraints' => [
                    new Range([
                        'min' => self::CROP_MIN,
                        'max' => self::CROP_MAX,
                    ]),
                    new NotBlank()
                ],
                'required' => false
            ])
            ->add('width_news_block', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'min' => self::CROP_MIN,
                    'max' => self::CROP_MAX,
                ],
                'constraints' => [
                    new Range([
                        'min' => self::CROP_MIN,
                        'max' => self::CROP_MAX,
                    ]),
                    new NotBlank()
                ],
                'required' => false
            ])
            ->add('height_news_block', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'min' => self::CROP_MIN,
                    'max' => self::CROP_MAX,
                ],
                'constraints' => [
                    new Range([
                        'min' => self::CROP_MIN,
                        'max' => self::CROP_MAX,
                    ]),
                    new NotBlank()
                ],
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CropVariant::class,
        ]);
    }
}
