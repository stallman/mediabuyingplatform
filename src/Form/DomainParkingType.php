<?php

namespace App\Form;

use App\Entity\DomainParking;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class DomainParkingType extends AbstractType
{
    const SEND_PULSE_ID_MAXLENGTH = 50;
    const DOMAIN_MAXLENGTH = 50;
    const DOMAIN_REGEXP = "/^(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('domain', TextType::class, [
                'label' => 'Домен',
                'required' => false,
                'help' => 'Домен должен быть в формате domain.com',
                'attr' =>  [
                    'maxlength' => self::DOMAIN_MAXLENGTH
                ],
                'empty_data' => '',
                'constraints' => [
                    new Length([
                        'max' => self::DOMAIN_MAXLENGTH,
                    ]),
                    new Regex([
                        'pattern'=> self::DOMAIN_REGEXP,
                    ]),
                    new NotBlank(),
                ]
            ])
            ->add('send_pulse_id', TextType::class, [
                'label' => 'SendPulseID',
                'required' => false,
                'attr' =>  [
                    'maxlength' => self::SEND_PULSE_ID_MAXLENGTH
                ],
                'constraints' => [
                    new Length([
                        'max' => self::SEND_PULSE_ID_MAXLENGTH,
                    ])
                ]
            ]);

        $builder->add('save', SubmitType::class, [
            'label' => 'Сохранить'
        ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DomainParking::class,
        ]);
    }
}
