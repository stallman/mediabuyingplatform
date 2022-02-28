<?php


namespace App\Form\FieldTypes;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntitySelectorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($options['child'], EntityType::class, [
                'class' => $options['class'],
                'choice_label' => $options['choice_label'],
                'label' => $options['field_label'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('class')
            ->setRequired('child')
            ->setRequired('choice_label')
            ->setRequired('field_label')
        ;
    }
}