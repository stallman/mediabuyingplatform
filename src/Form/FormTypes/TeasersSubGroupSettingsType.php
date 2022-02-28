<?php

namespace App\Form\FormTypes;

use App\Entity\Country;
use App\Entity\TeasersSubGroupSettings;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;

class TeasersSubGroupSettingsType extends AbstractType
{
    const LINK_MAXLENGTH = 255;
    const PERCENT_MIN = 0;
    const PERCENT_MAX = 100;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('geoCode', EntityType::class, [
                'label' => 'Гео*',
                'class' => Country::class,
                'attr' => [
                    'class' => 'geo-code-input'
                ],
                'placeholder' => 'Выберите страну'
            ])
            ->add('link', UrlType::class, [
                'label' => 'Ссылка*',
                'attr' =>  [
                    'maxlength' => self::LINK_MAXLENGTH,
                    'class' => 'tsg-link'
                ],
                'constraints' => [
                    new Length([
                        'max' => self::LINK_MAXLENGTH,
                    ]),
                ],
                'required' => true
            ])
            ->add('approveAveragePercentage', NumberType::class, [
                'label' => 'Средний % аппрува*',
                'attr' => [
                    'min' => self::PERCENT_MIN,
                    'max' => self::PERCENT_MAX,
                    'class' => 'approve-average-percentage'
                ],
                'constraints' => [
                    new Range([
                        'min' => self::PERCENT_MIN,
                        'max' => self::PERCENT_MAX,
                    ]),
                ],
                'required' => true
            ])
            ->add('isAutoCalculate', HiddenType::class, [
                'attr' =>  [
                    'class' => 'is-auto-calculate'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TeasersSubGroupSettings::class,
        ]);
    }

}
