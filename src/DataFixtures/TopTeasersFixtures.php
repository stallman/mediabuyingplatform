<?php

namespace App\DataFixtures;

use App\Entity\Country;
use App\Entity\Teaser;
use App\Entity\TopTeasers;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use App\Traits\Dashboard\UsersTrait;


class TopTeasersFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UsersTrait;

    const MIN_NUMBER = 0;
    const MAX_NUMBER = 1000;
    const NB_MAX_DECIMALS = 8;
    const TRAFFIC_TYPE = ['desktop', 'mobile', 'tablet'];

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
        $teasers = $this->entityManager->getRepository(Teaser::class)->findAll();
        $mediaBuyers = $this->getMediabuyerUsers();
        $countries = $this->entityManager->getRepository(Country::class)->findAll();
        /** @var Teaser $teaser */
        foreach($teasers as $teaser) {
            /** @var User $mediaBuyer */
            foreach($mediaBuyers as $mediaBuyer) {
                $randomCountry = $this->faker->randomElement($countries);

                $topTeasers = new TopTeasers();
                $topTeasers->setTeaser($teaser)
                    ->setMediabuyer($mediaBuyer)
                    ->setTrafficType($this->faker->randomElement(self::TRAFFIC_TYPE))
                    ->setGeoCode($randomCountry->getIsoCode())
                    ->setECPM($this->faker->randomFloat(self::NB_MAX_DECIMALS, self::MIN_NUMBER, self::MAX_NUMBER));

                $this->entityManager->persist($topTeasers);
                $this->entityManager->flush();
            }
        }
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
            TeasersFixtures::class,
            CountryFixtures::class
        );
    }

    public static function getGroups(): array
    {
        return ['TopTeasersFixtures'];
    }
}