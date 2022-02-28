<?php

namespace App\Form;

use App\Entity\CurrencyList;
use App\Entity\Sources;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;

class SourceType extends AbstractType
{
    const TITLE_MAXLENGTH = 50;
    const MACROS_MAXLENGHT = 40;
    const MACROS_REGEXP = '/^[a-zA-Z0-9-_%{}()\[\]]+$/'; //Разрешены только латинские буквы без пробевлов и символы -_{}
    const MULTIPLIER_MAXLENGTH = 6;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Название*',
                'attr' => ['maxlength' => self::TITLE_MAXLENGTH],
                'empty_data' => '',
                'constraints' => [
                    new Length([
                        'max' => self::TITLE_MAXLENGTH,
                    ]),
                    new NotBlank()
                ],
                'required' => false
            ])
            ->add('currency', EntityType::class, [
                'label' => 'Валюта*',
                'class' => CurrencyList::class,
                'placeholder' => 'Выбрать',
                'choice_label' => 'name',
                'constraints' => [
                    new NotBlank()
                ],
                'required' => false
            ])
            ->add('multiplier', NumberType::class, [
                'label' => 'Множитель*',
                'data' => $options['data']->getMultiplier() ? $options['data']->getMultiplier() : 1.0,
                'attr' => ['maxlength' => self::MULTIPLIER_MAXLENGTH],
                'empty_data' => 0,
                'constraints' => [
                    new Length([
                        'max' => self::MULTIPLIER_MAXLENGTH,
                    ]),
                    new NotBlank()
                ],
                'required' => false
            ])
            ->add('utm_campaign', TextType::class, [
                'label' => 'Макрос источника для передачи id или наименования рекламной кампании (utm_campaign)',
                'required' => false,
                'attr' => ['maxlength' => self::MACROS_MAXLENGHT],
                'constraints' => [
                    new Length([
                        'max' => self::MACROS_MAXLENGHT,
                    ]),
                    new Regex([
                        'pattern'=> self::MACROS_REGEXP
                    ]),
                ],
            ])
            ->add('utm_term', TextType::class, [
                'label' => 'Макрос источника для передачи id сайта (utm_term)',
                'required' => false,
                'attr' => ['maxlength' => self::MACROS_MAXLENGHT],
                'constraints' => [
                    new Length([
                        'max' => self::MACROS_MAXLENGHT,
                    ]),
                    new Regex([
                        'pattern'=> self::MACROS_REGEXP
                    ]),
                ],
            ])
            ->add('utm_content', TextType::class, [
                'label' => 'Макрос источника для передачи id рекламного объявления (utm_content)',
                'required' => false,
                'attr' => ['maxlength' => self::MACROS_MAXLENGHT],
                'constraints' => [
                    new Length([
                        'max' => self::MACROS_MAXLENGHT,
                    ]),
                    new Regex([
                        'pattern'=> self::MACROS_REGEXP
                    ]),
                ],
            ])
            ->add('subid1', TextType::class, [
                'label' => 'Кастомный макрос источника (subid1)',
                'required' => false,
                'attr' => ['maxlength' => self::MACROS_MAXLENGHT],
                'constraints' => [
                    new Length([
                        'max' => self::MACROS_MAXLENGHT,
                    ]),
                    new Regex([
                        'pattern'=> self::MACROS_REGEXP
                    ]),
                ],
            ])
            ->add('subid2', TextType::class, [
                'label' => 'Кастомный макрос источника (subid2)',
                'required' => false,
                'attr' => ['maxlength' => self::MACROS_MAXLENGHT],
                'constraints' => [
                    new Length([
                        'max' => self::MACROS_MAXLENGHT,
                    ]),
                    new Regex([
                        'pattern'=> self::MACROS_REGEXP
                    ]),
                ],
            ])
            ->add('subid3', TextType::class, [
                'label' => 'Кастомный макрос источника (subid3)',
                'required' => false,
                'attr' => ['maxlength' => self::MACROS_MAXLENGHT],
                'constraints' => [
                    new Length([
                        'max' => self::MACROS_MAXLENGHT,
                    ]),
                    new Regex([
                        'pattern'=> self::MACROS_REGEXP
                    ]),
                ],
            ])
            ->add('subid4', TextType::class, [
                'label' => 'Кастомный макрос источника (subid4)',
                'required' => false,
                'attr' => ['maxlength' => self::MACROS_MAXLENGHT],
                'constraints' => [
                    new Length([
                        'max' => self::MACROS_MAXLENGHT,
                    ]),
                    new Regex([
                        'pattern'=> self::MACROS_REGEXP
                    ]),
                ],
            ])
            ->add('subid5', TextType::class, [
                'label' => 'Кастомный макрос источника (subid5)',
                'required' => false,
                'attr' => ['maxlength' => self::MACROS_MAXLENGHT],
                'constraints' => [
                    new Length([
                        'max' => self::MACROS_MAXLENGHT,
                    ]),
                    new Regex([
                        'pattern'=> self::MACROS_REGEXP
                    ]),
                ],
            ]);

        $builder->add('save', SubmitType::class, [
            'label' => 'Сохранить'
        ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sources::class,
            'allow_extra_fields' => true,
            'multiplier' => 1.0,
        ]);
    }
}
