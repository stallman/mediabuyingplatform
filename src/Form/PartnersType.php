<?php

namespace App\Form;

use App\Entity\CurrencyList;
use App\Form\FieldTypes\UploadFileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class PartnersType extends AbstractType
{
    const TITLE_MAXLENGTH = 120;
    const POSTBACK_MAXLENGTH = 240;
    const STATUSES_MAXLENGTH = 50;
    const MACROSES_MAXLENGTH = 50;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Название*',
                'required' => false,
                'empty_data' => '',
                'attr' => [
                    'maxlength' => self::TITLE_MAXLENGTH
                ],
                'constraints' => [
                    new Length([
                        'max' => self::TITLE_MAXLENGTH,
                    ]),
                    new NotBlank()
                ]
            ]);
        if ($builder->getData()->getId()) {
            $builder->add('postback', TextType::class, [
                'label' => 'Postback*',
                'required' => false,
                'empty_data' => '',
                'attr' => [
                    'maxlength' => self::POSTBACK_MAXLENGTH
                ],
                'constraints' => [
                    new Length([
                        'max' => self::POSTBACK_MAXLENGTH,
                    ]),
                    new NotBlank()
                ]
            ]);
        }
        $builder->add('currency', EntityType::class, [
            'label' => 'Валюта*',
            'class' => CurrencyList::class,
            'placeholder' => 'Выбрать',
            'choice_label' => 'name',
            'multiple' => false,
            'required' => true,
        ])
            ->add('status_approved', TextType::class, [
                'label' => 'Код статуса “подтвержден”*',
                'required' => false,
                'empty_data' => '',
                'attr' => [
                    'maxlength' => self::STATUSES_MAXLENGTH
                ],
                'constraints' => [
                    new Length([
                        'max' => self::STATUSES_MAXLENGTH,
                    ]),
                    new NotBlank()
                ]
            ])
            ->add('status_pending', TextType::class, [
                'label' => 'Код статуса “в ожидании”*',
                'required' => false,
                'empty_data' => '',
                'attr' => [
                    'maxlength' => self::STATUSES_MAXLENGTH
                ],
                'constraints' => [
                    new Length([
                        'max' => self::STATUSES_MAXLENGTH,
                    ]),
                    new NotBlank()
                ]
            ])
            ->add('status_declined', TextType::class, [
                'label' => 'Код статуса “отклонен”*',
                'required' => false,
                'empty_data' => '',
                'attr' => [
                    'maxlength' => self::STATUSES_MAXLENGTH
                ],
                'constraints' => [
                    new Length([
                        'max' => self::STATUSES_MAXLENGTH,
                    ]),
                    new NotBlank(),
                ]
            ])
            ->add('macros_uniq_click', TextType::class, [
                'label' => 'Макрос уникального ID клика',
                'attr' => [
                    'maxlength' => self::MACROSES_MAXLENGTH
                ],
                'constraints' => [
                    new Length([
                        'max' => self::MACROSES_MAXLENGTH,
                    ]),
                ],
                'required' => false,
            ])
            ->add('macros_status', TextType::class, [
                'label' => 'Макрос статуса',
                'attr' => [
                    'maxlength' => self::MACROSES_MAXLENGTH
                ],
                'constraints' => [
                    new Length([
                        'max' => self::MACROSES_MAXLENGTH,
                    ]),
                ],
                'required' => false,
            ])
            ->add('macros_payment', TextType::class, [
                'label' => 'Макрос выплаты',
                'attr' => [
                    'maxlength' => self::MACROSES_MAXLENGTH
                ],
                'constraints' => [
                    new Length([
                        'max' => self::MACROSES_MAXLENGTH,
                    ]),
                ],
                'required' => false,
            ]);

        $builder->add('save', SubmitType::class, [
            'label' => 'Сохранить'
        ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
        ]);
    }
}