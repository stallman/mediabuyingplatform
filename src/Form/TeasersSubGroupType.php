<?php

namespace App\Form;

use App\Entity\Country;
use App\Entity\NewsCategory;
use App\Entity\TeasersSubGroup;
use App\Entity\TeasersSubGroupSettings;
use App\Form\FormTypes\TeasersSubGroupSettingsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TeasersSubGroupType extends AbstractType
{

    const NAME_MAXLENGTH = 80;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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
                'required' => false
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Группа активна',
                'required' => false
            ])

            ->add('teasersSubGroupSettings', CollectionType::class, [
                'entry_type' => TeasersSubGroupSettingsType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'label' => false,
                'attr' => [
                    'class' => 'collection-field',
                ]
            ])

            ->add('newsCategories', EntityType::class, [
                'label' => 'Группы новостей*',
                'class' => NewsCategory::class,
                'choice_label' => 'title',
                'multiple' => true,
                'required' => true,
            ]);

        $builder->add('save', SubmitType::class, [
            'label' => 'Сохранить'
        ]);


        $listener = function (FormEvent $event) {

            /** @var TeasersSubGroup $teaserSubGroup */
            $teaserSubGroup = $event->getForm()->getData();
            $teasersSubGroupSettings = $event->getData()['teasersSubGroupSettings'];


            if ($teaserSubGroup->getId()) {

                foreach ($teaserSubGroup->getTeasersSubGroupSettings() as $subGroupSetting) {
                    $this->entityManager->remove($subGroupSetting);
                    $this->entityManager->flush();
                }

                foreach ($teasersSubGroupSettings as $teasersSubGroupSettingItem) {

                    if (isset($teasersSubGroupSettingItem['geoCode'])) {
                        /** @var Country $geoCode */
                        $geoCode = $this->entityManager->getRepository(Country::class)->findOneBy(['id' => $teasersSubGroupSettingItem['geoCode']]);
                    } else {
                        $geoCode = null;
                    }

                    $teasersSubGroupSetting = new TeasersSubGroupSettings();
                    $approveAveragePercent = str_replace(",", ".", $teasersSubGroupSettingItem['approveAveragePercentage']);
                    $teasersSubGroupSetting
                        ->setLink($teasersSubGroupSettingItem['link'])
                        ->setGeoCode($geoCode)
                        ->setApproveAveragePercentage(floatval($approveAveragePercent))
                        ;
                }
            }

        };
        $builder->addEventListener(FormEvents::PRE_SUBMIT, $listener);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TeasersSubGroup::class,
        ]);
    }
}
