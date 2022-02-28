<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;


class CountersType extends AbstractType
{
    const COUNTERS_MAXLENGTH = 2000;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('head', TextareaType::class, [
                'label' => 'Доп. код в Head',
                'attr' =>  [
                    'maxlength' => self::COUNTERS_MAXLENGTH
                ],
                'constraints' => [
                    new Length([
                        'max' => self::COUNTERS_MAXLENGTH,
                    ]),
                ],
            ])
            ->add('body', TextareaType::class, [
                'label' => 'Доп. код в Body',
                'attr' =>  [
                    'maxlength' => self::COUNTERS_MAXLENGTH
                ],
                'constraints' => [
                    new Length([
                        'max' => self::COUNTERS_MAXLENGTH,
                    ]),
                ],
            ]);

        $builder->add('save', SubmitType::class, [
            'label' => 'Сохранить'
        ]);

    }
}
