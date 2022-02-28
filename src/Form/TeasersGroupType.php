<?php

namespace App\Form;

use App\Entity\TeasersGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TeasersGroupType extends AbstractType
{

    const NAME_MAXLENGTH = 80;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Название*',
                'attr' =>  [
                    'maxlength' => self::NAME_MAXLENGTH
                ],
                'constraints' => [
                    new Length([
                        'max' => self::NAME_MAXLENGTH,
                    ]),
                    new NotBlank()
                ],
                'empty_data' => '',
                'required' => false
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Группа активна',
                'required' => false
            ])
        ;

        $builder->add('save', SubmitType::class, [
            'label' => 'Сохранить'
        ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TeasersGroup::class,
        ]);
    }
}
