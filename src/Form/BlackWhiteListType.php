<?php

namespace App\Form;

use App\Entity\Sources;
use App\Entity\User;
use App\Repository\CampaignRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class BlackWhiteListType extends AbstractType
{
    const REPORT_TYPE = [
        'Блэк-лист',
        'Вайт-лист'
    ];
    const DATA_TYPE = [
        'utmTerm' => 'Сайты',
        'utmContent' => 'Тизеры (источник)',
        'utmCampaign' => 'Кампании (источник)',
        'news' => 'Тизеры (новостник)',
        'subid1' => 'SUBID 1',
        'subid2' => 'SUBID 2',
        'subid3' => 'SUBID 3',
        'subid4' => 'SUBID 4',
        'subid5' => 'SUBID 5'
    ];
    const FORMAT = [
        'Список',
        'Через запятую'
    ];

    private User $mediaBuyer;
    private EntityManagerInterface $entityManager;
    private CampaignRepository $campaignRepo;

    public function __construct(EntityManagerInterface $entityManager, CampaignRepository $campaignRepo)
    {
        $this->entityManager = $entityManager;
        $this->campaignRepo = $campaignRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->mediaBuyer = $options['user'];
        $campaignList = $this->campaignRepo->findBy(['mediabuyer' => $this->mediaBuyer]);

        $campaigns = [];
        foreach ($campaignList as $campaign) {
            $campaigns[] = null === $campaign->getTitle() ? "NULL" : $campaign->getTitle() ;
        }

        $builder
            ->add('report_type', ChoiceType::class, [
                'label' => 'Тип отчета* ',
                'label_attr' => [
                    'style' => 'font-weight: 700'
                ],
                'placeholder' => 'Выбрать',
                'choices' => self::REPORT_TYPE,
                'choice_value' => function($elem) {
                    return array_search($elem, self::REPORT_TYPE);
                },
                'choice_label' => function($elem) {
                    return $elem;
                },
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('data_type', ChoiceType::class, [
                'label' => 'Тип данных*',
                'label_attr' => [
                    'style' => 'font-weight: 700'
                ],
                'placeholder' => 'Выбрать',
                'choices' => self::DATA_TYPE,
                'choice_value' => function($elem) {
                    return array_search($elem, self::DATA_TYPE);
                },
                'choice_label' => function($elem) {
                    return $elem;
                },
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('sources', EntityType::class, [
                'label' => 'Источники*',
                'class' => Sources::class,
                'placeholder' => 'Выбрать',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.user = :user')
                        ->andWhere('p.is_deleted = :is_deleted')
                        ->setParameters([
                            'user' => $this->mediaBuyer,
                            'is_deleted' => false,
                        ]);
                },
                'choice_label' => function (Sources $news) {
                    return $news->getTitle();
                },
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('campaigns', ChoiceType::class, [
                'label' => 'Кампании (источник)',
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank()
                ],
                'help' => ' ',
                'choices' => $campaigns,
                'placeholder' => 'Выберите кампанию',
                'multiple' => true,
                'choice_label' => function ($campaign) {
                    return $campaign;
                },
                'attr' => [
                    'class' => 'multiple-selector selected-all'
                ],
                'help_attr' => [
                    'class' => 'multiple-selector-help'
                ],
            ])
            ->add('format', ChoiceType::class, [
                'label' => 'Формат*',
                'label_attr' => [
                    'style' => 'font-weight: 700'
                ],
                'placeholder' => 'Выбрать',
                'choices' => self::FORMAT,
                'choice_value' => function($elem) {
                    return array_search($elem, self::FORMAT);
                },
                'choice_label' => function($elem) {
                    return $elem;
                },
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ]);

        $builder->add('save', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-danger'
            ],
            'label' => '[icon] Получить',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'user' => null
        ]);
    }
}
