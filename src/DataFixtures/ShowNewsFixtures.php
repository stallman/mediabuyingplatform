<?php

namespace App\DataFixtures;

use App\Entity\Algorithm;
use App\Entity\Design;
use App\Entity\MediabuyerNews;
use App\Entity\News;
use App\Entity\ShowNews;
use App\Entity\Sources;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use Ramsey\Uuid\Uuid;
use App\Traits\Dashboard\UsersTrait;

class ShowNewsFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UsersTrait;

    /** @var EntityManagerInterface */
    public $entityManager;

    public $faker;

    const PAGE_TYPE = ['FULL', 'SHORT'];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->faker = Faker\Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        $mediaBuyers = $this->getMediabuyerUsers();
        $algorithms = $this->entityManager->getRepository(Algorithm::class)->findAll();
        $designs = $this->entityManager->getRepository(Design::class)->findAll();
        $news = $this->entityManager->getRepository(News::class)->findAll();
        /** @var News $newsItem */
        foreach($news as $newsItem){
            $this->createShowNews($newsItem, $algorithms, $designs, $mediaBuyers);
        }
    }

    private function createShowNews(News $news, array $algorithms, array $designs, array $mediaBuyers)
    {
        $randomMediaBuyer = $this->faker->randomElement($mediaBuyers);
        $mediaBuyerNews = $this->entityManager->getRepository(MediabuyerNews::class)->getMediaBuyerNewsItem($randomMediaBuyer, $news);
        $dropSources = $mediaBuyerNews ? $mediaBuyerNews->getDropSources() : null;
        $sources = $this->entityManager->getRepository(Sources::class)->getMediaBuyerSources($randomMediaBuyer, $dropSources);

        $showNews = new ShowNews();
        $showNews->setNews($news)
            ->setPageType($this->faker->randomElement(self::PAGE_TYPE))
            ->setMediabuyer($randomMediaBuyer)
            ->setAlgorithm($this->faker->randomElement($algorithms))
            ->setDesign($this->faker->randomElement($designs))
            ->setSource($this->faker->randomElement($sources))
            ->setUuid(Uuid::uuid4());

        $this->entityManager->persist($showNews);
        $this->entityManager->flush();

        return $showNews;
    }

    public function getDependencies()
    {
        return array(
            SourcesFixtures::class,
            NewsFixtures::class,
//            MediaBuyerNewsFixtures::class,
            UserFixtures::class,
            DesignsFixtures::class,
            AlgorithmsFixtures::class,
        );
    }

    public static function getGroups(): array
    {
        return ['ShowNewsFixtures'];
    }
}