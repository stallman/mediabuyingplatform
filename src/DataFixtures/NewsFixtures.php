<?php

namespace App\DataFixtures;

use App\Entity\News;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use Symfony\Component\Filesystem\Filesystem;
use App\Traits\Dashboard\UsersTrait;


class NewsFixtures extends FakeImagesFixtures implements DependentFixtureInterface, FixtureGroupInterface
{
    use UsersTrait;
    
    const TEASER_BLOCK = "[teaser block]";
    const COUNT_OWN_NEWS_FOR_EVERY_BUYER = 40;
    const COUNT_COMMON_NEWS = 5;
    const COMMON_TYPE = 'common';
    const OWN_TYPE = 'own';

    /** @var EntityManagerInterface */
    public $entityManager;

    public $faker;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->faker = Faker\Factory::create();
        $this->filesystem = new Filesystem();
    }

    public function load(ObjectManager $manager)
    {   
        $this->loadOwnNews();
        $this->loadCommonNews();
    }

    private function loadOwnNews()
    {
        foreach($this->getMediabuyerUsers() as $buyer) {
            for($i = 1; $i <= $this->getCountOwnMediabuyerNews(); $i++) {
                $this->createNews(self::OWN_TYPE, $buyer);
            }
        }
    }

    private function loadCommonNews()
    {
        for($i = 1; $i <= $this->getCountCommonNews(); $i++) {
            $this->createNews(self::COMMON_TYPE, $this->getRandomUser());
        }
    }

    private function getRandomUser() {
        return $this->faker->randomElement($this->getUsers());
    }

    private function getUsers()
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.email LIKE :journalistNames')
            ->orWhere('u.email = :firstBuyerName')
            ->setParameters([
                'firstBuyerName' => 'buyer0@demo.com',
                'journalistNames' => 'news%',
            ])
            ->getQuery();

        return $query->getResult();
    }

    private function createNews($type, $user)
    {
        $news = new News();

        $news->setType($type);
        $news->setTitle($this->faker->sentence);
        $news->setShortDescription($this->faker->sentence);
        $news->setFullDescription($this->getText() . self::TEASER_BLOCK);
        $news->setUser($user);
        $news->setSourceLink($this->faker->url);
        $news->setIsActive($this->faker->boolean($chanceOfGettingTrue = 80));
        $news->setUpdatedAt(new DateTime());

        $this->entityManager->persist($news);
        $this->entityManager->flush();
        $this->saveImages($news, 'news');
    }

    private function getCountOwnMediabuyerNews()
    {
        return self::COUNT_OWN_NEWS_FOR_EVERY_BUYER * $_ENV['FIXTURE_RATIO'] + 50;
    }

    private function getCountCommonNews()
    {
        return self::COUNT_COMMON_NEWS * $_ENV['FIXTURE_RATIO'] + 150;
    }

    private function getText($sentencesCount = 5)
    {
        $text = '';
        $paragraphsCount = rand (7, 10);
        for($i = 1; $i <= $paragraphsCount; $i++) {
            if($text) $text .= '<br/>';
            $sentences = $this->faker->sentences($sentencesCount, $asText = false);
            foreach($sentences as $sentence) {
                $text .=  $sentence;
            }
        }

        return $text;
    }

    public function getDependencies()
    {
        return array(
            AlgorithmsFixtures::class,
            DesignsFixtures::class,
        );
    }

    public static function getGroups(): array
    {
        return ['dev', 'prod', 'NewsFixtures'];
    }
}