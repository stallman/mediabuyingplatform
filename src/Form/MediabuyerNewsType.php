<?php

namespace App\Form;

use App\Entity\MediabuyerNews;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class MediabuyerNewsType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dropTeasers', TextType::class, [
                'label' => 'Запрет на тизеры',
                'help' => 'Введите ID тизеров через запятую',
                'required' => false
            ])
            ->add('dropSources', TextType::class, [
                'label' => 'Запрет на источники',
                'help' => 'Введите ID источников через запятую',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('show_type_selector');

        $resolver->setDefaults([
            'data_class' => MediabuyerNews::class,
        ]);
    }

}
