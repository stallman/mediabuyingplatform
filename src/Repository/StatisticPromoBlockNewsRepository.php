<?php

namespace App\Repository;

use App\Entity\News;
use App\Entity\StatisticPromoBlockNews;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StatisticPromoBlockNews|null find($id, $lockMode = null, $lockVersion = null)
 * @method StatisticPromoBlockNews|null findOneBy(array $criteria, array $orderBy = null)
 * @method StatisticPromoBlockNews[]    findAll()
 * @method StatisticPromoBlockNews[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatisticPromoBlockNewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatisticPromoBlockNews::class);
    }

    public function getNewsShowCountByNews(News $news, User $mediaBuyer, string $countryCode, string $trafficType)
    {
        $query = $this->createQueryBuilder('statNPB')
            ->select('count(statNPB.id) as count')
            ->where('statNPB.news = :news')
            ->andWhere('statNPB.countryCode = :countryCode')
            ->andWhere('statNPB.mediabuyer = :mediaBuyer')
            ->andWhere('statNPB.trafficType = :trafficType')
            ->setParameters([
                'news' => $news,
                'mediaBuyer' => $mediaBuyer,
                'countryCode' => $countryCode,
                'trafficType' => $trafficType,
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }
}
