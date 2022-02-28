<?php

namespace App\Form;

use App\Entity\CurrencyList;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;


class BasicSettingsType extends AbstractType
{
    CONST MAXLENGTH = 11;
    CONST EXPIRE_TIME_MIN = 1;
    CONST EXPIRE_TIME_MAX = 99999999999;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('default_currency', ChoiceType::class, [
                'label' => 'Стандартная валюта',
                'choices' => $this->getCurrencyList(),
                'required' => true,
            ])
            ->add('default_mediabuyer', ChoiceType::class, [
                'label' => 'Дефолтный медиабаер',
                'choices' => $this->getMediaBuyerUsersList(),
                'required' => true,
            ])
            ->add('session_expire_time', IntegerType::class, [
                'label' => 'Время жизни сессии (в сек.)',
                'attr' =>  [
                    'min' => self::EXPIRE_TIME_MIN,
                    'max' => self::EXPIRE_TIME_MAX
                ],
                'constraints' => [
                    new Length([
                        'max' => self::MAXLENGTH,
                    ]),
                    new NotBlank()
                ],
                'required' => false,
            ])
            ->add('cookie_expire_time', IntegerType::class, [
                'label' => 'Время жизни cookie автологина (в сек.)',
                'attr' =>  [
                    'min' => self::EXPIRE_TIME_MIN,
                    'max' => self::EXPIRE_TIME_MAX
                ],
                'constraints' => [
                    new Length([
                        'max' => self::MAXLENGTH,
                    ]),
                    new NotBlank()
                ],
                'required' => true,
            ]);

        $builder->add('save', SubmitType::class, [
            'label' => 'Сохранить'
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

    private function getMediaBuyerUsers()
    {
        $dql = "SELECT u FROM App\Entity\User u WHERE u.roles LIKE :role AND u.status = :status";

        return $this->entityManager
            ->createQuery($dql)
            ->setParameters([
                'role' => '%ROLE_MEDIABUYER%',
                'status' => 1
            ])
            ->getResult();
    }

    private function getMediaBuyerUsersList()
    {
        $mediaBuyerList = [];

        foreach($this->getMediaBuyerUsers() as $mediaBuyer) {
            $mediaBuyerList[$mediaBuyer->getEmail()] = $mediaBuyer->getEmail();
        }

        return $mediaBuyerList;
    }
}