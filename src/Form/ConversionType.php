<?php

namespace App\Form;

use App\Entity\Conversions;
use App\Entity\ConversionStatus;
use App\Entity\Partners;
use App\Entity\User;
use App\Form\DataTransformer\TeaserClickTransformer;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConversionType extends AbstractType
{
    const STATUS = [
        'подтвержден' => 'подтвержден',
        'в ожидании' => 'в ожидании',
        'отклонен' => 'отклонен'
    ];
    const ADD_DATE = ['По времени клика', 'Текущее'];
    const ADMIN = 'ROLE_ADMIN';
    const MEDIABUYER = '["ROLE_MEDIABUYER"]';
    const SOURCE_LINK_MAXLENGTH = 120;
    const CLICK_ID_MIN_VALUE = 0;
    const AMOUNT_MAXLENGTH = 11;

    private $mediaBuyer;

    private TeaserClickTransformer $transformer;

    public function __construct(TeaserClickTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['user'];
        $clickIdDisabled= false;

        if ($this->isEditForm($options)){
            $clickIdDisabled = true;
        }

        if($user->getRole() == self::ADMIN){

            if ($this->isEditForm($options)){
                $this->mediaBuyer = $builder->getData()->getMediabuyer();
                $builder
                    ->add('mediabuyer', TextType::class, [
                        'label' => 'Медиабаер',
                        'disabled' => true,
                    ]);
            } else {
                $builder
                    ->add('mediabuyer', EntityType::class, [
                        'label' => 'Медиабаер*',
                        //'label' => false,
                        'class' => User::class,
                        'placeholder' => 'Выберите медиабаера',
                        'query_builder' => function (EntityRepository $er) {
                            return $er
                                ->createQueryBuilder('u')
                                ->where('u.roles = :role')
                                ->setParameter(
                                    'role', self::MEDIABUYER,
                            );
                        },
                        'required' => true,
                        'constraints' => [
                            new NotBlank()
                        ],
//                        'attr' => [
//                            'class' => 'hide-form-control'
//                        ]
                    ]);
            }

            $formModifier = function (FormInterface $form, User $mediaBuyer = null) {
                $this->mediaBuyer = null === $mediaBuyer ? array() : $mediaBuyer;

                $form->add('affilate', EntityType::class, [
                    'label' => 'Партнерка*',
                    'class' => Partners::class,
                    'placeholder' => 'Выбрать',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('p')
                            ->where('p.user = :user')
                            ->andWhere('p.is_deleted = :isDeleted')
                            ->setParameters([
                                'user' => $this->mediaBuyer,
                                'isDeleted' => false
                            ])
                        ;
                    },
                    'choice_label' => 'title',
                    'required' => true,
                    'constraints' => [
                        new NotBlank()
                    ],
                ]);
            };
            $builder->get('mediabuyer')->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) use ($formModifier) {
                    $mediaBuyer = $event->getForm()->getData();
                    $formModifier($event->getForm()->getParent(), $mediaBuyer);
                }
            );
        } else {
            $this->mediaBuyer = $user;
        }

        $builder
            ->add('teaserClick', TextType::class, [
                'label' => 'Уникальный ID клика в системе*',
                'invalid_message' => 'Клик с таким ID отсуствует в системе',
                'constraints' => [
                    new NotBlank()
                ],
                'required' => false,
                'disabled' => $clickIdDisabled,
            ])
            ->add('affilate', EntityType::class, [
                'label' => 'Партнерка*',
                'class' => Partners::class,
                'placeholder' => 'Выбрать',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.user = :user')
                        ->andWhere('p.is_deleted = :isDeleted')
                        ->setParameters([
                            'user' => $this->mediaBuyer,
                            'isDeleted' => false
                        ])
                    ;
                },
                'choice_label' => 'title',
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('status', EntityType::class, [
                'label' => 'Статус*',
                'placeholder' => 'Выбрать',
                'class' => ConversionStatus::class,
                'choice_label' => 'label_ru',
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('amount', NumberType::class, [
                'label' => 'Выплата*',
                'help' => 'Введите сумму в валюте партнерки',
                'attr' => [
                    'maxlength' => self::AMOUNT_MAXLENGTH,
                ],
                'empty_data' => 0,
                'constraints' => [
                    new NotBlank()
                ],
                'required' => false,
            ]);

        $builder
            ->get('teaserClick')
            ->addModelTransformer($this->transformer);

        if (!$this->isEditForm($options)){
            $builder
                ->add('addDate', ChoiceType::class, [
                    'label' => 'Время создания лида*',
                    'choices' => self::ADD_DATE,
                    'placeholder' => 'Выбрать',
                    'choice_value' => function($elem) {
                        return array_search($elem, self::ADD_DATE);
                    },
                    'choice_label' => function($elem) {
                        return $elem;
                    },
                    'mapped' => false,
                    'required' => true,
                ]);
        }

        $builder->add('save', SubmitType::class, [
            'label' => 'Сохранить'
        ]);
        $listener = function (FormEvent $event) {
            $formData = $event->getData();
            $formData->setAmountRub(1);
        };
        $builder->addEventListener(FormEvents::PRE_SET_DATA, $listener);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Conversions::class,
            'user' => null,
            'compound' => true,
            'allow_extra_fields' => true,
        ]);
    }

    private function isEditForm($options)
    {
        return ($options['data']->getId()) ? true : false;
    }
}
