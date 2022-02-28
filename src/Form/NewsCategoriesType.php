<?php

namespace App\Form;

use App\Entity\NewsCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class NewsCategoriesType extends AbstractType
{
    const TITLE_MAXLENGTH = 100;
    const SLUG_MAXLENGTH = 100;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Название категории',
                'attr' => ['maxlength' => self::TITLE_MAXLENGTH],
                'constraints' => [
                    new Length([
                        'max' => self::TITLE_MAXLENGTH,
                    ]),
                    new NotBlank()
                ],
                'required' => false
            ])
            ->add('slug', TextType::class, [
                'label' => 'Слаг категории',
                'attr' => ['maxlength' => self::SLUG_MAXLENGTH],
                'constraints' => [
                    new Length([
                        'max' => self::SLUG_MAXLENGTH,
                    ]),
                    new NotBlank()
                ],
                'required' => false
            ])
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'Активировать категорию',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Сохранить'
            ]);;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NewsCategory::class,
        ]);
    }
}
