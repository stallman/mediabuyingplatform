<?php

namespace App\Form;

use App\Entity\Country;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CountryType extends AbstractType
{
    const NAME_MAXLENGTH = 100;
    const ISO_CODE_MAXLENGTH = 2;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Название страны',
                'attr' => ['maxlength' => self::NAME_MAXLENGTH],
                'constraints' => [
                    new Length([
                        'max' => self::NAME_MAXLENGTH,
                    ]),
                    new NotBlank()
                ],
                'required' => false,
            ])
            ->add('iso_code', TextType::class, [
                'required' => false,
                'label' => 'Код страны в формате ISO 3166-1 alpha-2',
                'attr' => ['maxlength' => self::ISO_CODE_MAXLENGTH],
                'constraints' => [
                    new Length([
                        'max' => self::ISO_CODE_MAXLENGTH,
                    ]),
                    new NotBlank()
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Сохранить'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Country::class,
        ]);
    }
}
