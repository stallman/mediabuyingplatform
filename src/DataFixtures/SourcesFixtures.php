<?php

namespace App\DataFixtures;

use App\Entity\CurrencyList;
use App\Entity\Sources;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use App\Traits\Dashboard\UsersTrait;


class SourcesFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UsersTrait;

    const SOURCES_COUNT = 3;
    const CURRENCY_LIST = ['usd', 'rub', 'uah', 'eur'];
    const PROBABILITY_MACROS = 50;

    /** @var EntityManagerInterface */
    public $entityManager;

    public $faker;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->faker = Faker\Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        foreach ($this->getMediabuyerUsers() as $user) {
            for ($i = 0; $i < self::SOURCES_COUNT; $i++) {
                $this->createSources($user);
            }
        };
    }

    private function createSources(User $user)
    {
        $source = new Sources();
        $source->setUser($user)
            ->setTitle($this->faker->company)
            ->setCurrency($this->getRandomCurrency())
            ->setMultiplier($this->faker->randomFloat($nbMaxDecimals = 4, $min = 0, $max = 99))
            ->setUtmCampaign($this->getMacros())
            ->setUtmTerm($this->getMacros())
            ->setUtmContent($this->getMacros())
            ->setSubid1($this->getMacros())
            ->setSubid2($this->getMacros())
            ->setSubid3($this->getMacros())
            ->setSubid4($this->getMacros())
            ->setSubid5($this->getMacros());
        $this->entityManager->persist($source);
        $this->entityManager->flush();
    }

    private function getRandomCurrency()
    {
        $randomKey = array_rand(self::CURRENCY_LIST);
        return $this->entityManager->getRepository(CurrencyList::class)->findOneBy(['iso_code' => self::CURRENCY_LIST[$randomKey]]);
    }

    private function getMacros()
    {
        $macros = null;

        if (mt_rand(0, 100) <= self::PROBABILITY_MACROS){
            $macros = "{{$this->faker->word}}";
        }

        return $macros;
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
        );
    }

    public static function getGroups(): array
    {
        return ['dev', 'prod', 'SourcesFixtures'];
    }
}