<?php

namespace App\Form;

use App\Entity\Teaser;
use App\Entity\TeasersGroup;
use App\Entity\TeasersSubGroup;
use App\Entity\User;
use App\Form\FormTypes\ImageType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TeaserType extends AbstractType
{
    const TEXT_MAXLENGTH = 120;

    private User $user;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->user = $options['user'];

        $builder
            ->add('text', TextType::class, [
                'label' => 'Текст*',
                'help' => 'Макрос [CITY] выведет в тексте город пользователя',
                'attr' =>  [
                    'maxlength' => self::TEXT_MAXLENGTH
                ],
                'constraints' => [
                    new Length([
                        'max' => self::TEXT_MAXLENGTH,
                    ]),
                    new NotBlank()
                ],
                'empty_data' => '',
                'required' => false
            ])
            ->add('image', ImageType::class, [
                'label' => 'Изображение*',
                'mapped' => false,
                'constraints' => [
                    new NotBlank()
                ],
                'required' => false,
            ])
            ->add('teasersSubGroup', EntityType::class, [
                      'label' => 'Подгруппа*',
                      'class' => TeasersSubGroup::class,
                      'placeholder' => 'Выберите подгруппу',
                      'query_builder' => function (EntityRepository $er) {
                          return $er
                              ->createQueryBuilder('tsg')
                              ->leftJoin(TeasersGroup::class, 'tg', 'WITH', 'tsg.teaserGroup = tg.id')
                              ->where('tg.user = :user')
                              ->andWhere('tg.isActive = :isActive')
                              ->andWhere('tg.is_deleted = :isDeleted')
                              ->andWhere('tsg.isActive = :isActive')
                              ->andWhere('tsg.is_deleted = :isDeleted')
                              ->setParameters([
                                  'user' => $this->user,
                                  'isActive' => true,
                                  'isDeleted' => false,
                              ]);
                      },
                      'required' => true,
                      'constraints' => [
                          new NotBlank()
                      ],
                      'group_by' => 'teaserGroup.name',
                  ])
            ->add('isActive', ChoiceType::class, [
                'label' => 'Статус тизера*',
                'label_attr' => [
                    'style' => 'font-weight: 700'
                ],
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Активен' => 1,
                    'Неактивен' => 0,
                ],
                'required' => true
            ])
            ->add('dropNews', TextType::class, [
                'label' => 'Запрет на новости',
                'help' => 'Введите ID новостей через запятую',
                'required' => false
            ])
            ->add('dropSources', TextType::class, [
                'label' => 'Запрет на источники',
                'help' => 'Введите ID источников через запятую',
                'required' => false
            ])
            ->add('isTop', CheckboxType::class, [
                'label' => 'Закрепить тизер в топе',
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
            'data_class' => Teaser::class,
            'allow_extra_fields' => true,
            'user' => null
        ]);
    }
}
