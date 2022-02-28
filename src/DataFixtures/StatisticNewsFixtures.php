<?php

namespace App\DataFixtures;

use App\Entity\StatisticNews;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker;


class StatisticNewsFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    const MIN_NUMBER = 0;
    const MAX_NUMBER = 1000;
    const NB_MAX_DECIMALS = 7;

    /** @var EntityManagerInterface */
    public $entityManager;

    public Faker\Generator $faker;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->faker = Faker\Factory::create();
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $statisticNews = $this->entityManager->getRepository(StatisticNews::class)->findAll();

        /** @var StatisticNews $statNewsItem */
        foreach ($statisticNews as $statNewsItem) {
            $statNewsItem->setInnerShow($this->faker->numberBetween(self::MIN_NUMBER, self::MAX_NUMBER))
                ->setInnerClick($this->faker->numberBetween(self::MIN_NUMBER, self::MAX_NUMBER))
                ->setInnerCTR($this->faker->randomFloat(self::NB_MAX_DECIMALS, self::MIN_NUMBER, self::MAX_NUMBER))
                ->setInnerECPM($this->faker->randomFloat(self::NB_MAX_DECIMALS, self::MIN_NUMBER, self::MAX_NUMBER))
                ->setClick($this->faker->numberBetween(self::MIN_NUMBER, self::MAX_NUMBER))
                ->setClickOnTeaser($this->faker->numberBetween(self::MIN_NUMBER, self::MAX_NUMBER))
                ->setProbiv($this->faker->randomFloat(self::NB_MAX_DECIMALS, self::MIN_NUMBER, self::MAX_NUMBER))
                ->setConversion($this->faker->numberBetween(self::MIN_NUMBER, self::MAX_NUMBER))
                ->setApproveConversion($this->faker->numberBetween(self::MIN_NUMBER, self::MAX_NUMBER))
                ->setApprove($this->faker->numberBetween(self::MIN_NUMBER, self::MAX_NUMBER))
                ->setInvolvement($this->faker->randomFloat(self::NB_MAX_DECIMALS, self::MIN_NUMBER, self::MAX_NUMBER))
                ->setEPC($this->faker->randomFloat(self::NB_MAX_DECIMALS, self::MIN_NUMBER, self::MAX_NUMBER))
                ->setCR($this->faker->randomFloat(self::NB_MAX_DECIMALS, self::MIN_NUMBER, self::MAX_NUMBER));

            $this->entityManager->flush();
        }
    }

    public function getDependencies()
    {
        return array(
            NewsFixtures::class
        );
    }

    public static function getGroups(): array
    {
        return ['StatisticNewsFixtures'];
    }
}