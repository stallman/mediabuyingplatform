<?php

namespace App\DataFixtures;

use App\Entity\CurrencyList;
use App\Entity\CurrencyRate;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker;


class CurrencyRateFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    const RATE = [
        'usd' => 71.18,
        'uah' => 2.61,
        'eur' => 81.25
    ];
    const MIN_PERCENT = 1;
    const MAX_PERCENT = 20;

    public EntityManagerInterface $entityManager;
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
        $currencyList = $this->entityManager->getRepository(CurrencyList::class)->findAll();
        /** @var CurrencyList $currency */
        foreach($currencyList as $currency) {
            if($currency->getIsoCode() == 'rub') continue;
            for ($i = 0; $i <= 29; $i++) {
                $rate = new CurrencyRate();
                $rate->setDate($this->getDate($i))
                    ->setCurrencyCode($currency->getIsoCode())
                    ->setRate($this->getRate(self::RATE[$currency->getIsoCode()]));

                $this->entityManager->persist($rate);
                $this->entityManager->flush();
            }
        }
    }

    private function getDate(int $i)
    {
        $date = new \DateTime();

        return $i ? $date->modify('-'.$i.'day') : $date;
    }

    private function getRate(int $rate)
    {
        return $rate + $this->getPercent($rate);
    }

    private function getPercent($rate)
    {
        $percent = $this->faker->numberBetween(self::MIN_PERCENT, self::MAX_PERCENT);
        $valueByPercent = $rate / 100 * $percent;

        if($this->faker->boolean($chanceOfGettingTrue = 50)) return -$valueByPercent;
        return $valueByPercent;
    }

    public function getDependencies()
    {
        return array(
            CurrencyFixtures::class
        );
    }

    public static function getGroups(): array
    {
        return ['dev', 'prod', 'CurrencyRateFixtures'];
    }
}