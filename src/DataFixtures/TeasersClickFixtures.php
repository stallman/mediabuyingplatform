<?php

namespace App\DataFixtures;

use App\Entity\Algorithm;
use App\Entity\Country;
use App\Entity\Design;
use App\Entity\News;
use App\Entity\Sources;
use App\Entity\Teaser;
use App\Entity\TeasersClick;
use App\Entity\User;
use App\Entity\Visits;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Traits\Dashboard\UsersTrait;
use Faker;
use Ramsey\Uuid\Uuid;

class TeasersClickFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    const TRAFFIC_TYPE = ['desktop', 'mobile', 'tablet'];
    const PAGE_TYPE = ['full', 'short', 'top'];
    const TEASER_CLICK_COUNT = 20;

    use UsersTrait;

    public EntityManagerInterface $entityManager;
    public Faker\Generator $faker;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->faker = Faker\Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        for($i = 0; $i < $this->getCountTeaserClick(); $i++) {
            $this->createTeaserClick();

        }
    }

    private function createTeaserClick()
    {
        /** @var User $mediaBuyer */
        foreach($this->getMediabuyerUsers() as $mediaBuyer) {
            /** @var Visits $visit */
            foreach($this->entityManager->getRepository(Visits::class)->getVisitsByBuyer($mediaBuyer) as $visit) {
                $country = $this->faker->randomElement($this->entityManager->getRepository(Country::class)->findAll());

                $teaserClick = new TeasersClick();
                $teaserClick->setId(Uuid::uuid4())
                    ->setBuyer($mediaBuyer)
                    ->setSource($this->getRandomElement($this->entityManager->getRepository(Sources::class)->getMediaBuyerSourcesList($mediaBuyer)))
                    ->setTeaser($this->faker->randomElement($this->entityManager->getRepository(Teaser::class)->getMediaBuyerTeasersList($mediaBuyer)))
                    ->setNews($this->faker->randomElement($this->entityManager->getRepository(News::class)->getMediaBuyerNewsList($mediaBuyer)))
                    ->setDesign($this->faker->randomElement($this->entityManager->getRepository(Design::class)->findAll()))
                    ->setAlgorithm($this->faker->randomElement($this->entityManager->getRepository(Algorithm::class)->findAll()))
                    ->setCountryCode($country->getIsoCode())
                    ->setTrafficType($this->faker->randomElement(self::TRAFFIC_TYPE))
                    ->setPageType($this->faker->randomElement(self::PAGE_TYPE))
                    ->setUserIp($this->faker->ipv4)
                    ->setUuid($visit->getUuid());

                $this->entityManager->persist($teaserClick);
                $this->entityManager->flush();
            }
        }
    }

    private function getRandomElement(array $collection)
    {
        if($this->faker->boolean($chanceOfGettingTrue = 50)) return $this->faker->randomElement($collection);

        return null;
    }

    private function getCountTeaserClick()
    {
        return self::TEASER_CLICK_COUNT * $_ENV['FIXTURE_RATIO'];
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
            SourcesFixtures::class,
            TeasersFixtures::class,
            NewsFixtures::class,
            VisitFixtures::class,
        );
    }

    public static function getGroups(): array
    {
        return ['TeasersClickFixtures'];
    }
}