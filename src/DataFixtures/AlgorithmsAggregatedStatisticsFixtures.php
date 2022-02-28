<?php

namespace App\DataFixtures;

use App\Entity\Algorithm;
use App\Entity\AlgorithmsAggregatedStatistics;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use App\Traits\Dashboard\UsersTrait;


class AlgorithmsAggregatedStatisticsFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UsersTrait;

    const MIN_NUMBER = 0;
    const MAX_NUMBER = 1000;
    const NB_MAX_DECIMALS = 7.4;
    const NB_MAX_DECIMALS_1 = 8.4;

    /** @var EntityManagerInterface */
    public $entityManager;
    /** @var Faker\ */
    public $faker;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->faker = Faker\Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        foreach ($this->getMediabuyerUsers() as $user) {
            $this->createArggregatedStatistics($user);
        };
    }

    private function createArggregatedStatistics(User $user) {
        $algorithms = $this->entityManager->getRepository(Algorithm::class)->findAll();

        /** @var Algorithm $algorithm */
        foreach($algorithms as $algorithm){
            $statistic = new AlgorithmsAggregatedStatistics();
            $statistic->setMediabuyer($user)
                ->setAlgorithm($algorithm)
                ->setCTR($this->faker->randomFloat(self::NB_MAX_DECIMALS, self::MIN_NUMBER, self::MAX_NUMBER))
                ->setConversion($this->faker->numberBetween(self::MIN_NUMBER, self::MAX_NUMBER))
                ->setApproveConversion($this->faker->numberBetween(self::MIN_NUMBER, self::MAX_NUMBER))
                ->setECPM($this->faker->randomFloat(self::NB_MAX_DECIMALS, self::MIN_NUMBER, self::MAX_NUMBER))
                ->setEPC($this->faker->randomFloat(self::NB_MAX_DECIMALS_1, self::MIN_NUMBER, self::MAX_NUMBER))
                ->setCR($this->faker->randomFloat(self::NB_MAX_DECIMALS_1, self::MIN_NUMBER, self::MAX_NUMBER))
                ->setROI($this->faker->randomFloat(self::NB_MAX_DECIMALS, self::MIN_NUMBER, self::MAX_NUMBER));

            $this->entityManager->persist($statistic);
            $this->entityManager->flush();
        }
    }

    public function getDependencies()
    {
        return array(
            AlgorithmsFixtures::class,
            UserFixtures::class,
        );
    }

    public static function getGroups(): array
    {
        return ['AlgorithmsAggregatedStatisticsFixtures'];
    }
}