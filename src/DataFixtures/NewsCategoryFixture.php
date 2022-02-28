<?php


namespace App\DataFixtures;


use App\Entity\News;
use App\Entity\NewsCategory;
use App\Entity\TeasersSubGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class NewsCategoryFixture extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{

    public const CATEGORIES_NAMES = [
        'podarki' => 'Подарки',
        'elektronika' => 'Электроника',
        'lifestyle' => 'Лайфстайл',
        'goroskopy' => 'Гороскопы',
        'zdorove-i-medicina' => 'Здоровье и медицина',
        'krasota-i-moda' => 'Красота и мода',
        'kino' => 'Кино',
        'mir' => 'Мир',
        'avto' => 'Авто'
    ];
    public const ACTIVE_CATEGORIES_COUNT = 6;

    public $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager)
    {
        return $this
            ->addNewsCategories()
            ->relateNewsAndCategories()
            ->relateTeasersSubGroupAndCategories();
    }

    private function addNewsCategories()
    {
        $i = 0;
        foreach (self::CATEGORIES_NAMES as $slug => $category) {
            $newsCategory = new NewsCategory();
            $newsCategory
                ->setTitle($category)
                ->setSlug($slug)
                ->setIsEnabled(($i < self::ACTIVE_CATEGORIES_COUNT) ? true : false);
            $this->entityManager->persist($newsCategory);
            $i++;
        }

        $this->entityManager->flush();

        return $this;
    }

    private function relateNewsAndCategories()
    {
        $news = $this->entityManager->getRepository(News::class)->findAll();

        /** @var News $newsItem */
        foreach ($news as $newsItem) {
            $newsCategories = $this->entityManager->getRepository(NewsCategory::class)
                ->createQueryBuilder('q')
                ->where('q.isEnabled = 1')
                ->addSelect('RAND() as HIDDEN rand')
                ->addOrderBy('rand')
                ->setMaxResults(rand(1, 3))
                ->getQuery()
                ->getResult();

            /** @var NewsCategory $newsCategory */
            foreach ($newsCategories as $newsCategory) {
                $newsItem->addCategory($newsCategory);
            }
        }

        $this->entityManager->flush();

        return $this;
    }

    private function relateTeasersSubGroupAndCategories()
    {
        $teasersSubGroups = $this->entityManager->getRepository(TeasersSubGroup::class)->findAll();

        /** @var TeasersSubGroup $teaserSubGroup */
        foreach ($teasersSubGroups as $teaserSubGroup) {
            $newsCategories = $this->entityManager->getRepository(NewsCategory::class)
                ->createQueryBuilder('q')
                ->where('q.isEnabled = 1')
                ->addSelect('RAND() as HIDDEN rand')
                ->addOrderBy('rand')
                ->setMaxResults(rand(1, 3))
                ->getQuery()
                ->getResult();

            /** @var NewsCategory $newsCategory */
            foreach ($newsCategories as $newsCategory) {
                $teaserSubGroup->addNewsCategory($newsCategory);
            }
        }

        $this->entityManager->flush();

        return $this;
    }

    public function getDependencies()
    {
        return array(
            TeasersGroupsFixtures::class,
        );
    }

    public static function getGroups(): array
    {
        return ['dev', 'prod', 'NewsCategoryFixture'];
    }
}