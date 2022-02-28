<?php

namespace App\Repository;

use App\Entity\Algorithm;
use App\Entity\Design;
use App\Entity\StatisticPromoBlockTeasers;
use App\Entity\Teaser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StatisticPromoBlockTeasers|null find($id, $lockMode = null, $lockVersion = null)
 * @method StatisticPromoBlockTeasers|null findOneBy(array $criteria, array $orderBy = null)
 * @method StatisticPromoBlockTeasers[]    findAll()
 * @method StatisticPromoBlockTeasers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatisticPromoBlockTeasersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatisticPromoBlockTeasers::class);
    }

    public function getTeaserShowCountByDesign(User $user, Design $design)
    {
        $query = $this->createQueryBuilder('statTPB')
            ->select('count(statTPB.id) as count')
            ->where('statTPB.mediabuyer = :user')
            ->andWhere('statTPB.design = :design')
            ->setParameters([
                'user' => $user,
                'design' => $design
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getTeaserShowCountByAlgorithm(User $user, Algorithm $algorithm)
    {
        $query = $this->createQueryBuilder('statTPB')
            ->select('count(statTPB.id) as count')
            ->where('statTPB.mediabuyer = :user')
            ->andWhere('statTPB.algorithm = :algorithm')
            ->setParameters([
                'user' => $user,
                'algorithm' => $algorithm
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getTeaserShowCountByTeaser(Teaser $teaser, string $countryCode, string $trafficType)
    {
        $query = $this->createQueryBuilder('statTPB')
            ->select('count(statTPB.id) as count')
            ->where('statTPB.teaser = :teaser')
            ->andWhere('statTPB.countryCode = :countryCode')
            ->andWhere('statTPB.trafficType = :trafficType')
            ->setParameters([
                'teaser' => $teaser,
                'countryCode' => $countryCode,
                'trafficType' => $trafficType,
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function countById(int $teaserId)
    {
        $query = $this->createQueryBuilder('statTPB')
            ->select('count(statTPB.id) as count')
            ->where('statTPB.teaser = :teaser')
            ->setParameters([
                'teaser' => $teaserId,
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }
}
