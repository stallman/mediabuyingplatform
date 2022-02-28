<?php

namespace App\Repository;

use App\Entity\News;
use App\Entity\NewsCategory;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;

/**
 * @method NewsCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewsCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewsCategory[]    findAll()
 * @method NewsCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsCategory::class);
    }

    public function getActiveCategoryNews(NewsCategory $newsCategory)
    {
        $query = $this->createQueryBuilder('nc')
            ->select('nw.title', 'nw.id')
            ->leftJoin('nc.news', 'nw')
            ->where('nw.isActive = :isActive')
            ->andWhere('nc.id = :categoryId')
            ->setParameter('isActive', 1)
            ->setParameter('categoryId', $newsCategory->getId())
            ->getQuery();

        return $query->getResult();
    }

    public function getCategoriesWithCountNews()
    {
        $query = $this->createQueryBuilder('nc')
            ->select('nc as category')
            ->leftJoin('nc.news', 'nw', 'WITH', 'nw.is_deleted = :isDeleted')
            ->addSelect('count(nw.id) as news_count')
            ->setParameter('isDeleted', false)
            ->groupBy('nc.id')
            ->getQuery();

        return $query->getResult();
    }

    public function getActiveCategoryNewsByCountry(NewsCategory $newsCategory, $countryCode, User $user, $source)
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT news.id, news.title, news.createdAt, image.filePath, image.fileName
              FROM App\Entity\NewsCategory as category
              JOIN category.news as news
              JOIN news.countries as country
              JOIN App\Entity\Image as image WITH news.id = image.entityId AND image.entityFQN = :entityFQN
              LEFT JOIN App\Entity\MediabuyerNews as mediaBuyerNews WITH mediaBuyerNews.news = news.id AND mediaBuyerNews.mediabuyer = news.user
              WHERE
                category.id = :categoryId
              AND
                news.isActive = :isActive
              AND
               news.user = :user
              AND
               mediaBuyerNews.dropSources NOT LIKE :source
              AND
                country.iso_code = :iso_code
          ')
            ->setParameters([
                'isActive' => 1,
                'categoryId' => $newsCategory->getId(),
                'user' => $user,
                'source' => "%$source%",
                'iso_code' => $countryCode,
                'entityFQN' => get_class(new News()),
            ]);

        return $query->getResult();
    }

    public function getEnabledCategories()
    {
        $query = $this->createQueryBuilder('categories')
            ->where('categories.isEnabled = :isEnabled')
            ->setParameter('isEnabled', true)
            ->getQuery();

        return $query->getResult();
    }

    public function getNewsWithCategories()
    {
        $conn = $this->getEntityManager()->getConnection();

        $stmt = $conn->executeQuery('SELECT id,title FROM news_categories');

        $rows = $stmt->fetchAllAssociative();

        $newsCategories = [];
        foreach ($rows as $row) {
            $newsCategories[intval($row['id'])] = $row['title'];
        }

        return $newsCategories;
    }
}
