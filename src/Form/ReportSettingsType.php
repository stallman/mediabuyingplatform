<?php

namespace App\Form;

use App\Entity\Sources;
use App\Entity\User;
use App\Repository\CampaignRepository;
use App\Repository\SourcesRepository;
use App\Traits\Dashboard\TrafficAnalysisTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReportSettingsType extends AbstractType
{
    private User $mediaBuyer;
    private SourcesRepository $sourcesRepo;
    private CampaignRepository $campaignRepo;
    private array $columnParams = [];

    public function __construct(SourcesRepository $sourcesRepo, CampaignRepository $campaignRepo)
    {
        $this->sourcesRepo = $sourcesRepo;
        $this->campaignRepo = $campaignRepo;
        foreach (TrafficAnalysisTrait::$groupColumns as $columnParam) {
            $this->columnParams[$columnParam['columnName']] = $columnParam['label'];
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->mediaBuyer = $options['user'];
        $columns = $this->columnParams;
        $columnNames = array_keys($columns);

        $sourcesList = ['NULL' => 'Не указан'];
        $sources = $this->sourcesRepo->findBy([
            'user' => $this->mediaBuyer,
            'is_deleted' => false,
        ]);
        /** @var Sources $source */
        foreach ($sources as $source) {
            $sourcesList[$source->getId()] = $source->getTitle();
        }

        $campaignList = ['NULL' => 'Не указан'];
        $campaigns = $this->campaignRepo->findBy(['mediabuyer' => $this->mediaBuyer]);
        foreach ($campaigns as $campaign) {
            if (null === $campaign->getTitle() || empty($campaign->getTitle())) {
                continue;
            }
            $campaignList[$campaign->getTitle()] = $campaign->getTitle();
        }

        $builder
            ->add('sources', ChoiceType::class, [
                'label' => 'Источники',
                'multiple' => true,
                'choices' => array_flip($sourcesList),
                'help' => ' ',
                'attr' => [
                    'class' => 'multiple-selector selected-all'
                ],
                'help_attr' => [
                    'class' => 'multiple-selector-help'
                ],
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
                'placeholder' => 'Выберите кампанию',
                'multiple' => true,
                'choices' => array_flip($campaignList),
                'attr' => [
                    'class' => 'multiple-selector selected-all'
                ],
                'help_attr' => [
                    'class' => 'multiple-selector-help'
                ],
            ])
            ->add('level1', ChoiceType::class, [
                'label' => 'Группировка',
                'choices' => $this->columnParams,
                'choice_value' => function($elem) {
                    return array_search($elem, $this->columnParams);
                },
                'choice_label' => function($elem) {
                    return $elem;
                },
                'data' => $options['data']['report_settings']['level1'] ??
                    array_shift($columnNames),
                //'placeholder' => 'Не выбрано',
                'attr' => ['class' => 'report_setting_groups'],
                'label_attr' => [
                    'style' => 'font-weight: 700'
                ],
                'required' => true,
            ])
            ->add('level2', ChoiceType::class, [
                'label' => ' ',
                'label_attr' => [
                    'style' => 'min-height: 18px'
                ],
                'choices' => $this->columnParams,
                'choice_value' => function($elem) {
                    return array_search($elem, $this->columnParams);
                },
                'choice_label' => function($elem) {
                    return $elem;
                },
                'data' => null,
                'placeholder' => 'Не выбрано',
                'attr' => ['class' => 'report_setting_groups'],
                'required' => false,
            ])
            ->add('level3', ChoiceType::class, [
                'label' => ' ',
                'label_attr' => [
                    'style' => 'min-height: 18px'
                ],
                'choices' => $this->columnParams,
                'choice_value' => function($elem) {
                    return array_search($elem, $this->columnParams);
                },
                'choice_label' => function($elem) {
                    return $elem;
                },
                'placeholder' => 'Не выбрано',
                'attr' => ['class' => 'report_setting_groups'],
                'required' => false,
            ]);

        $builder->add('save', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-danger',
                'hidden' => true
            ],
            'label' => '[icon] Обновить',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'user' => null
        ]);
    }
}
