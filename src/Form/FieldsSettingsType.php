<?php

namespace App\Form;

use App\Traits\Dashboard\StatisticConstTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class FieldsSettingsType extends AbstractType
{
    use StatisticConstTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('traffic', ChoiceType::class, [
                'label' => 'Трафик',
                'label_attr' => [
                    'class' => 'switch-custom',
                    'style' => 'font-weight: 700'
                ],
                'choices' => $this->getTraffic(),
                'choice_value' => function($elem) {
                    return array_search($elem, $this->getTraffic());
                },
                'choice_label' => function($elem) {
                    return $elem;
                },
                'expanded' => true,
                'multiple' => true,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('leads', ChoiceType::class, [
                'label' => 'Лиды',
                'label_attr' => [
                    'class' => 'switch-custom',
                    'style' => 'font-weight: 700'
                ],
                'choices' => $this->getLeads(),
                'choice_value' => function($elem) {
                    return array_search($elem, $this->getLeads());
                },
                'choice_label' => function($elem) {
                    return $elem;
                },
                'expanded' => true,
                'multiple' => true,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('finances', ChoiceType::class, [
                'label' => 'Финансы',
                'label_attr' => [
                    'class' => 'switch-custom',
                    'style' => 'font-weight: 700'
                ],
                'choices' => $this->getFinances(),
                'choice_value' => function($elem) {
                    return array_search($elem, $this->getFinances());
                },
                'choice_label' => function($elem) {
                    return $elem;
                },
                'expanded' => true,
                'multiple' => true,
                'required' => true,
                'constraints' => [
                    new NotBlank()
                ],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'user' => null,
        ]);
    }
}
