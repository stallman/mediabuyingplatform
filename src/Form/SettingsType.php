<?php

namespace App\Form;

use App\Entity\CurrencyList;
use App\Entity\User;
use App\Entity\UserSettings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class SettingsType extends AbstractType
{
    const TELEGRAM_MAXLENGTH = 40;
    const NICKNAME_MAXLENGTH = 40;
    const ECRM_MIN = 0;
    const ECRM_MAX = 1000000000;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nickname', TextType::class, [
                'label' => 'Никнейм',
                'attr' =>  [
                    'maxlength' => self::NICKNAME_MAXLENGTH
                ],
                'constraints' => [
                    new Length([
                        'max' => self::NICKNAME_MAXLENGTH,
                    ])
                ],
                'required' => false,
            ])
            ->add('telegram', TextType::class, array(
                'label' => 'Телеграм',
                'attr' =>  [
                    'maxlength' => self::TELEGRAM_MAXLENGTH
                ],
                'constraints' => [
                    new Length([
                        'max' => self::TELEGRAM_MAXLENGTH,
                    ])
                ],
                'required' => false,

            ))
            ->add('changed_password', PasswordType::class, [
                'label'  => 'Пароль',
                'mapped' => false,
                'required' => false,
            ])
            ->add(UserSettings::SLUG_ECRM_TEASERS_VIEW_COUNT, IntegerType::class, [
                'label' => 'Количество показов тизера для перехода на собственный eCPM',
                'attr' => [
                    'min' => self::ECRM_MIN,
                    'max' => self::ECRM_MAX,
                ],
                'constraints' => [
                    new Range([
                        'min' => self::ECRM_MIN,
                        'max' => self::ECRM_MAX,
                    ]),
                    new NotBlank()
                ],
                'required' => false,
                'mapped' => false,
            ])
            ->add(UserSettings::SLUG_ECRM_NEWS_VIEW_COUNT, IntegerType::class, [
                'label' => 'Количество показов новости для перехода на собственный eCPM',
                'attr' => [
                    'min' => self::ECRM_MIN,
                    'max' => self::ECRM_MAX,
                ],
                'constraints' => [
                    new Range([
                        'min' => self::ECRM_MIN,
                        'max' => self::ECRM_MAX,
                    ]),
                    new NotBlank()
                ],
                'required' => false,
                'mapped' => false,
            ])
            ->add(UserSettings::SLUG_DEFAULT_CURRENCY, ChoiceType::class, [
                'label' => 'Валюта по умолчанию для отчетов и статистики*',
                'choices' => $this->getCurrencyList(),
                'mapped' => false,
                'required' => true,
            ])
            ->add(UserSettings::SLUG_STATS_STORAGE_DAYS, IntegerType::class, [
                'label' => 'Хранить статистику за последние X дней (0 - не удалять статистику)',
                'attr' => [
                    'min' => 0,
                    'max' => PHP_INT_MAX,
                ],
                'constraints' => [
                    new Range([
                        'min' => 0,
                        'max' => PHP_INT_MAX,
                    ]),
                    new NotBlank()
                ],
                'required' => false,
                'mapped' => false,
            ])
            ->add('save', SubmitType::class, array(
                'label' => 'Сохранить'
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
    private function getCurrencyList()
    {
        $currencyList = [];
        $currencyListDoctrine = $this->entityManager->getRepository(CurrencyList::class)->findAll();

        foreach($currencyListDoctrine as $currency) {
            $currencyList[$currency->getName()] = $currency->getId();
        }

        return $currencyList;
    }

}
