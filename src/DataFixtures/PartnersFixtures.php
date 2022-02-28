<?php

namespace App\DataFixtures;

use App\Entity\CurrencyList;
use App\Entity\Partners;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use App\Traits\Dashboard\UsersTrait;


class PartnersFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UsersTrait;

    const PARTNERS_COUNT = 3;
    const CURRENCY_LIST = ['usd', 'rub', 'uah', 'eur'];

    /** @var EntityManagerInterface */
    public $entityManager;

    public $faker;

    public $users;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->faker = Faker\Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        foreach ($this->getMediabuyerUsers() as $user) {
            for ($i = 0; $i < self::PARTNERS_COUNT; $i++) {
                $this->createPartners($user);
            }
        };
    }

    private function createPartners(User $user)
    {
        $source = new Partners();
        $source->setUser($user)
            ->setTitle($this->faker->company)
            ->setPostback($this->faker->domainName)
            ->setCurrency($this->getRandomCurrency())
            ->setStatusDeclined($this->faker->text($maxNbChars = 5))
            ->setStatusPending($this->faker->text($maxNbChars = 5))
            ->setStatusApproved($this->faker->text($maxNbChars = 5))
            ->setMacrosUniqClick($this->faker->optional(50)->text($maxNbChars = 5))
            ->setMacrosPayment($this->faker->optional(50)->text($maxNbChars = 5))
            ->setMacrosStatus($this->faker->optional(50)->text($maxNbChars = 5));

        $this->entityManager->persist($source);
        $this->entityManager->flush();
    }

    private function getRandomCurrency()
    {
        $randomKey = array_rand(self::CURRENCY_LIST);
        return $this->entityManager->getRepository(CurrencyList::class)->findOneBy(['iso_code' => self::CURRENCY_LIST[$randomKey]]);
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
        );
    }

    public static function getGroups(): array
    {
        return ['dev', 'prod', 'PartnersFixtures'];
    }
}
