<?php

namespace App\Form;

use App\Entity\CurrencyList;
use App\Entity\MediabuyerNews;
use App\Entity\MediabuyerNewsRotation;
use App\Entity\News;
use App\Entity\User;
use App\Entity\Sources;
use App\Repository\CampaignRepository;
use App\Repository\CurrencyListRepository;
use App\Repository\SourcesRepository;
use App\Repository\VisitsRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class CostsType extends AbstractType
{
    const COST_MAXLENGTH = 10;
    const COST_MAXVALUE = 99999.9999;
    const COST_MINVALUE = 0;

    private User $user;
    private string $candidate; // news/campaign
    private ?array $form_data;
    private VisitsRepository $visitsRepo;
    private CampaignRepository $campaignRepo;
    private CurrencyListRepository $clRepo;
    private SourcesRepository $sourcesRepo;

    public function __construct(VisitsRepository $visitsRepo, CampaignRepository $campaignRepo, CurrencyListRepository $clRepo, SourcesRepository $sourcesRepo)
    {
        $this->visitsRepo = $visitsRepo;
        $this->campaignRepo = $campaignRepo;
        $this->clRepo = $clRepo;
        $this->sourcesRepo = $sourcesRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->user = $options['user'];
        $this->candidate = $options['candidate'];
        $this->form_data = array_get($options, 'form_data.costs');

        $builder
            ->add('source', EntityType::class, $this->sourceSelectorAttrs($options))
        ;

        if ($this->candidate == 'news') {
            $builder->add('news', EntityType::class, $this->newsSelectorAttrs($options));
        }

        if ($this->isAddForm($options)) {
            if ($this->candidate == 'campaign') {
                $builder->add('campaign', ChoiceType::class, $this->campaignSelectorAttrs($options));
            }
            $builder = $this->addDateRangeFields($builder);
        } else {
            if ($this->candidate == 'campaign') {
                $builder->add('campaign', TextType::class, $this->campaignSelectorAttrs($options));
            }
        }

        $builder->add('currency', EntityType::class, $this->currencyInputAttrs($options));
        $builder->add('cost', NumberType::class, $this->costInputAttrs($options));

        $builder->add('save', SubmitType::class, [
            'label' => 'Сохранить'
        ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'user' => null,
            'candidate' => 'news',
            'form_data' => null,
        ]);
    }


    private function isAddForm($options)
    {
        return ($options['data']->getId()) ? false : true;
    }

    private function addDateRangeFields($builder)
    {
        $builder->add('date_from', DateType::class, [
            'label' => 'От',
            'mapped' => false,
            'data' => $this->dateFromData(),
        ])
        ->add('date_to', DateType::class, [
            'label' => 'До',
            'mapped' => false,
            'data' => $this->dateToData(),
        ]);

        return $builder;
    }

    /**
     * Create date from saved value
     * @param string $key
     * @return \DateTime
     */
    private function dateData(string $key) {
        $date = new \DateTime('now');
        if(array_has($this->form_data, $key)) {
            $date->setDate(
                array_get($this->form_data, $key . '.year'),
                array_get($this->form_data, $key . '.month'),
                array_get($this->form_data, $key . '.day')
            );
        }
        return $date;
    }
    private function dateFromData() {
        return $this->dateData('date_from');
    }
    private function dateToData() {
        return $this->dateData('date_to');
    }

    private function sourceSelectorAttrs($options)
    {
        if ($this->isAddForm($options)) {
            return [
                'label' => 'Источник',
                'class' => Sources::class,
                'choice_label' => 'title',
                'placeholder' => 'Выберите источник',
                'required' => true,
                'multiple' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank()
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er
                        ->createQueryBuilder('s')
                        ->where('s.user = :mediabuyer')
                        ->andWhere('s.is_deleted = :is_deleted')
                        ->setParameters([
                            'mediabuyer' => $this->user,
                            'is_deleted' => 0,
                        ]);
                },
                'data' => $this->sourceSelectorData(),
                'help' => ' ',
                'attr' => [
                    'class' => 'multiple-selector selected-all'
                ],
                'help_attr' => [
                    'class' => 'multiple-selector-help'
                ],
            ];
        } else {
            return ['label' => 'Источник', 'class' => Sources::class, 'choice_label' => 'title', 'disabled' => true];
        }
    }
    private function sourceSelectorData() : array{
        if(! array_has($this->form_data, 'source')) {
            return [];
        }

        $ids = array_get($this->form_data, 'source');
        return $this->sourcesRepo->getByIds($this->user, $ids);
    }

    private function newsSelectorAttrs($options)
    {
        if ($this->isAddForm($options)) {
            return [
                'label' => 'Новость',
                'class' => News::class,
                'placeholder' => 'Выберите новость',
                'required' => true,
                'multiple' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank()
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er
                        ->createQueryBuilder('n')
                        ->leftJoin(MediabuyerNews::class, 'mn', 'WITH', 'mn.news = n.id')
                        ->leftJoin(MediabuyerNewsRotation::class, 'mnr', 'WITH', 'mnr.news = n.id')
                        ->where('mn.mediabuyer = :mediabuyer')
                        ->andWhere('mnr.isRotation = :is_rotation')
                        ->andWhere('n.is_deleted = :isDeleted')
                        ->setParameters([
                            'mediabuyer' => $this->user,
                            'is_rotation' => 1,
                            'isDeleted' => 0,
                        ]);
                },
                'choice_label' => function (News $news) {
                    return "{$news->getId()}|{$news->getTitle()}";
                },
                'help' => ' ',
                'attr' => [
                    'class' => 'multiple-selector selected-all'
                ],
                'help_attr' => [
                    'class' => 'multiple-selector-help'
                ],
            ];
        } else {
            return ['label' => 'Новость', 'class' => News::class, 'choice_label' => 'title', 'disabled' => true];
        }
    }

    private function campaignSelectorAttrs($options)
    {
        $field = [
            'label' => 'Кампании (источник)',
            'required' => true,
            'mapped' => false,
            'constraints' => [
                new NotBlank()
            ],
            'help' => ' ',
        ];

        if ($this->isAddForm($options)) {
            $campaignList = ['NULL' => 'Не указан'];
            $campaigns = $this->campaignRepo->findBy(['mediabuyer' => $this->user]);
            foreach ($campaigns as $campaign) {
                if (null === $campaign->getTitle() || empty($campaign->getTitle())) {
                    continue;
                }
                $campaignList[$campaign->getTitle()] = $campaign->getTitle();
            }

            $addProps = [
                'choices' => $campaignList,
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
            ];
        } else {
            $addProps = [
                'data' => $options['data']->getCampaign(),
                'disabled' => true,
            ];
        }

        $field = array_merge($field, $addProps);

        return $field;
    }


    private function currencyInputAttrs($options)
    {
        if ($this->isAddForm($options)) {
            return [
                'label' => 'Валюта',
                'placeholder' => 'Выбрать',
                'class' => CurrencyList::class,
                'choice_label' => 'name',
                'data' => $this->currencyInputData()
            ];
        } else {
            return [
                'label' => 'Валюта',
                'class' => CurrencyList::class,
                'choice_label' => 'name',
                'disabled' => true,
            ];
        }
    }
    private function currencyInputData() {
        if(! array_has($this->form_data, 'currency')) {
            return $this->clRepo->getByIsoCode('rub');
        }

        $id = array_get($this->form_data, 'currency');
        return $this->clRepo->find($id);
    }

    private function costInputAttrs($options)
    {
        return [
            'label' => 'Расход*',
            'data' => $this->costInputValue($options),
            'attr' =>  [
                'maxlength' => self::COST_MAXLENGTH,
                'max' => 99999.9999,
            ],
            'scale' => 4,
            'constraints' => [
                new Length([
                    'max' => self::COST_MAXLENGTH,
                ]),
                new NotBlank(),
                new Range([
                    'max' => self::COST_MAXVALUE,
                    'min' => self::COST_MINVALUE,
                ]),
            ],
            'required' => false,
        ];
    }

    private function costInputValue($options)
    {
        return $options['data']->getCost() ? $options['data']->getCost() : 0;
    }
}
