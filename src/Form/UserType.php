<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    const EMAIL_MAXLENGTH = 180;
    const TELEGRAM_MAXLENGTH = 40;
    const NICKNAME_MAXLENGTH = 40;
    const PASSWORD_MINLENGTH = 5;
    const PASSWORD_MAXLENGTH = 20;
    const PASSWORD_REGEXP = "/^[A-Za-z0-9]+$/";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email*',
                'attr' =>  [
                    'maxlength' => self::EMAIL_MAXLENGTH
                ],
                'constraints' => [
                    new Length([
                        'max' => self::EMAIL_MAXLENGTH,
                    ]),
                    new NotBlank()
                ],
                'required' => false
            ])
            ->add('plain_password', PasswordType::class, [
                'label' => $options['is_password_required'] ? 'Пароль*' : 'Новый пароль',
                'constraints' => [
                    new Length([
                        'min' => self::PASSWORD_MINLENGTH,
                        'max' => self::PASSWORD_MAXLENGTH,
                    ]),
                    new Regex([
                        'pattern'=> self::PASSWORD_REGEXP,
                    ]),
                    new NotBlank($options['is_password_required'] ? null : ['allowNull' => true])
                ],
                'required' => false
            ])
            ->add('role', ChoiceType::class, [
                'choices' => ['Журналист' => 'ROLE_JOURNALIST', 'Администратор' => 'ROLE_ADMIN', 'Медиабайер' => 'ROLE_MEDIABUYER'],
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'label' => 'Группа',
            ])
            ->add('nickname', TextType::class, [
                'label' => 'Никнейм',
                'required' => false,
                'attr' =>  [
                    'maxlength' => self::NICKNAME_MAXLENGTH
                ],
                'constraints' => [
                    new Length([
                        'max' => self::NICKNAME_MAXLENGTH,
                    ])
                ]
            ])
            ->add('telegram', TextType::class, [
                'label' => 'Телеграм',
                'required' => false,
                'attr' =>  [
                    'maxlength' => self::TELEGRAM_MAXLENGTH
                ],
                'constraints' => [
                    new Length([
                        'max' => self::TELEGRAM_MAXLENGTH,
                    ])
                ]
            ])
            ->add('status', CheckboxType::class, [
                'label' => 'Пользователь активирован',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Сохранить'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_password_required' => false,
        ]);
    }
}
