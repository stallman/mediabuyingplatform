<?php

namespace App\DataFixtures;

use App\Entity\DomainParking;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use App\Traits\Dashboard\UsersTrait;


class DomainParkingFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UsersTrait;
    const DOMAIN_PARKING_COUNT = 2;

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
            for ($i = 0; $i < self::DOMAIN_PARKING_COUNT; $i++) {
                $this->createDomainParking($user,
                    $this->faker->domainName,
                    $this->faker->dateTimeBetween($startDate = '+1 month', $endDate = '+3 month', $timezone = null),
                    $this->faker->domainName
                );
            }

            if ($user->getEmail() == "buyer0@demo.com") {
                $this->createDomainParking($user,"cp.0315.devsit.ru");
            }
        };
    }

    private function createDomainParking(User $user, $domainName, $date = null)
    {
        $domainParking = new DomainParking();
        $domainParking->setUser($user)
            ->setDomain($domainName)
            ->setSendPulseId($this->generateFakeSendPulseId(30))
            ->setCertEndDate($date);

        $this->entityManager->persist($domainParking);
        $this->entityManager->flush();
    }

    private function generateFakeSendPulseId($length) {
        if ($this->faker->boolean($chanceOfGettingTrue = 80)) {
            return rtrim(strtr(base64_encode(random_bytes($length)), '+/', '-_'), '=');
        }
        return "";
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
        );
    }

    public static function getGroups(): array
    {
        return ['dev', 'DomainParkingFixtures'];
    }
}