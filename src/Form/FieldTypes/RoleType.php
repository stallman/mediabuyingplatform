<?php


namespace App\Form\FieldTypes;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'user' => 'user'
                ]
            ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

}