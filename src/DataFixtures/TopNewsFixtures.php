<?php

namespace App\DataFixtures;

use App\Entity\Country;
use App\Entity\News;
use App\Entity\TopNews;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use App\Traits\Dashboard\UsersTrait;


class TopNewsFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
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
        $news = $this->entityManager->getRepository(News::class)->findAll();
        $mediaBuyers = $this->getMediabuyerUsers();
        $countries = $this->entityManager->getRepository(Country::class)->findAll();
        /** @var News $newsItem */
        foreach($news as $newsItem) {
            /** @var User $mediaBuyer */
            foreach($mediaBuyers as $mediaBuyer) {
                $randomCountry = $this->faker->randomElement($countries);

                $topNews = new TopNews();
                $topNews->setNews($newsItem)
                    ->setMediabuyer($mediaBuyer)
                    ->setTrafficType($this->faker->randomElement(self::TRAFFIC_TYPE))
                    ->setGeoCode($randomCountry->getIsoCode())
                    ->setECPM($this->faker->randomFloat(self::NB_MAX_DECIMALS, self::MIN_NUMBER, self::MAX_NUMBER));

                $this->entityManager->persist($topNews);
                $this->entityManager->flush();
            }
        }
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
            NewsFixtures::class,
            CountryFixtures::class
        );
    }

    public static function getGroups(): array
    {
        return ['TopNewsFixtures'];
    }
}