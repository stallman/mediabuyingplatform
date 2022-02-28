<?php

namespace App\DataFixtures;

use App\Entity\Country;
use App\Entity\News;
use App\Entity\Sources;
use App\Entity\Teaser;
use App\Entity\TeasersSubGroup;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use Symfony\Component\Filesystem\Filesystem;
use App\Traits\Dashboard\UsersTrait;


class TeasersFixtures extends FakeImagesFixtures implements DependentFixtureInterface, FixtureGroupInterface
{
    use UsersTrait;

    const TEASERS_COUNT = 20;
    const RU_TEASERS_COUNT = 200;
    const MACROS_CITY = '[CITY]';
    const DROP_COUNT = 3;
    const PROBABILITY = 30;

    /** @var EntityManagerInterface */
    public $entityManager;
    public $faker;
    public $users;

    private $news;
    private $sources;
    private $subGroups;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->faker = Faker\Factory::create();
        $this->filesystem = new Filesystem();
    }

    public function load(ObjectManager $manager)
    {
        $russia = $this->entityManager->getRepository(Country::class)->getCountryByIsoCode('RU');

        foreach ($this->getMediabuyerUsers() as $user) {
            $this->news = $this->entityManager->getRepository(News::class)->getMediaBuyerNewsList($user);
            $this->sources = $this->entityManager->getRepository(Sources::class)->getMediaBuyerSourcesList($user);
            $this->subGroups = $this->entityManager->getRepository(TeasersSubGroup::class)->getUserSubGroup($user);

            for ($i = 0; $i < $this->getCountTeasers(); $i++) {
                $macrosCity = ($i % 6 == 0) ? self::MACROS_CITY : '';
                $this->createAndAddFakeTeaser($user, $macrosCity);
            }

            $this->subGroups = $this->entityManager->getRepository(TeasersSubGroup::class)->getUserCountrySubGroup($user, $russia);

            for ($i = 0; $i < self::RU_TEASERS_COUNT; $i++) {
                $macrosCity = ($i % 6 == 0) ? self::MACROS_CITY : '';
                $this->createAndAddFakeTeaser($user, $macrosCity);
            }
        };
    }

    private function createAndAddFakeTeaser($user, $macrosCity) {
        $teaser = new Teaser();
        $teaser->setUser($user)
            ->setText("{$macrosCity} {$this->faker->text($maxNbChars = 110)}")
            ->setTeasersSubGroup($this->faker->randomElement($this->subGroups))
            ->setIsActive($this->faker->boolean($chanceOfGettingTrue = 50))
            ->setIsTop($this->faker->boolean($chanceOfGettingTrue = 50))
            ->setDropNews($this->getDropItems($this->news))
            ->setDropSources($this->getDropItems($this->sources));

        $this->entityManager->persist($teaser);
        $this->entityManager->flush();
        $this->saveImages($teaser, 'teaser');
    }

    private function getDropItems($dropItemsList)
    {
        $dropItems = "";
        if (mt_rand(0, 100) <= self::PROBABILITY){
            shuffle($dropItemsList);
            $i = 1;
            foreach($dropItemsList as $item) {
                if($i >= self::DROP_COUNT) break;
                $dropItems .= "{$item->getId()},";
                $i++;
            }
        }

        return $dropItems;
    }

    private function getCountTeasers()
    {
        return self::TEASERS_COUNT * $_ENV['FIXTURE_RATIO'];
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
            SourcesFixtures::class,
            NewsFixtures::class,
            TeasersGroupsFixtures::class
        );
    }

    public static function getGroups(): array
    {
        return ['dev', 'prod', 'TeasersFixtures'];
    }
}