<?php

namespace App\Form;

use App\Entity\Country;
use App\Entity\News;
use App\Entity\NewsCategory;
use App\Form\FieldTypes\WysiwygType;
use App\Form\FormTypes\ImageType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class NewsMediabuyerType extends AbstractType
{
    const TITLE_MAXLENGTH = 150;
    const SHORT_DESCRIPTION_MAXLENGTH = 255;
    const SOURCE_LINK_MAXLENGTH = 255;


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['show_all_form']) {
            $builder
                ->add('title', TextType::class, [
                    'label' => 'Название новости*',
                    'attr' =>  [
                        'maxlength' => self::TITLE_MAXLENGTH
                    ],
                    'constraints' => [
                        new Length([
                            'max' => self::TITLE_MAXLENGTH,
                        ]),
                        new NotBlank()
                    ],
                    'required' => false
                ])
                ->add('shortDescription', TextareaType::class, [
                    'label' => 'Короткая новость*',
                    'attr' => [
                        'cols' => 5,
                        'rows' => 5,
                        'maxlength' => self::SHORT_DESCRIPTION_MAXLENGTH
                    ],
                    'constraints' => [
                        new Length([
                            'max' => self::SHORT_DESCRIPTION_MAXLENGTH,
                        ]),
                        new NotBlank()
                    ],
                    'required' => false
                ])
                ->add('fullDescription', WysiwygType::class, [
                    'label' => 'Полная новость*',
                    'constraints' => [
                        new NotBlank()
                    ],
                    'required' => false
                ])
                ->add('sourceLink', UrlType::class, [
                    'label' => 'Ссылка на источник',
                    'attr' =>  [
                        'maxlength' => self::SOURCE_LINK_MAXLENGTH
                    ],
                    'constraints' => [
                        new Length([
                            'max' => self::SOURCE_LINK_MAXLENGTH,
                        ]),
                    ],
                    'required' => false,
                ])
                ->add('image', ImageType::class, [
                    'label' => 'Изображение',
                    'mapped' => false,
                    'required' => false,
                ])
                ->add('isActive', ChoiceType::class, [
                    'label' => 'Статус новости*',
                    'expanded' => true,
                    'multiple' => false,
                    'choices' => [
                        'Активна' => 1,
                        'Неактивна' => 0,
                    ],
                    'label_attr' => [
                        'style' => 'font-weight: 700'
                    ],
                    'required' => true,
                ])
                ->add('categories', EntityType::class, [
                    'label' => 'Группа*',
                    'class' => NewsCategory::class,
                    'choice_attr' => function($key, $val, $index) {
                        return !$key->getIsEnabled() ? ['class' => 'inactive'] : [];
                    },
                    'choice_label' => 'title',
                    'multiple' => true,
                    'required' => true,
                ])
                ->add('countries', EntityType::class, [
                    'label' => 'Страны*',
                    'class' => Country::class,
                    'attr' => [
                        'class' => 'multiple-selector'
                    ],
                    'choice_label' => 'name',
                    'multiple' => true,
                    'help' => ' ',
                    'help_attr' => [
                        'class' => 'multiple-selector-help'
                    ],
                    'required' => true,
                ]);
            if ($options['show_type_selector']) {
                $builder->add('type', ChoiceType::class, [
                    'label' => 'Тип новости*',
                    'expanded' => true,
                    'multiple' => false,
                    'choices' => [
                        'Собственная' => 'own',
                        'Общедоступная' => 'common',
                    ],
                    'label_attr' => [
                        'style' => 'font-weight: 700'
                    ],
                    'required' => true,
                ]);
            }
        }

        $builder->add('mediabuyerNews', CollectionType::class, [
            'entry_type' => MediabuyerNewsType::class,
            'entry_options' => [
                'label' => false,
                'show_type_selector' => $options['show_type_selector']
            ],
            'allow_add' => true,
            'label' => false,
        ]);

        $builder->add('save', SubmitType::class, [
            'label' => 'Сохранить'
        ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('show_type_selector');

        $resolver->setDefaults([
            'data_class' => News::class,
            'show_all_form' => false,
            'user' => null,
        ]);
    }
}
