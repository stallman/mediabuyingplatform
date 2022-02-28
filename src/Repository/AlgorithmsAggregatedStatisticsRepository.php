<?php

namespace App\Repository;

use App\Entity\Algorithm;
use App\Entity\AlgorithmsAggregatedStatistics;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AlgorithmsAggregatedStatistics|null find($id, $lockMode = null, $lockVersion = null)
 * @method AlgorithmsAggregatedStatistics|null findOneBy(array $criteria, array $orderBy = null)
 * @method AlgorithmsAggregatedStatistics[]    findAll()
 * @method AlgorithmsAggregatedStatistics[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlgorithmsAggregatedStatisticsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AlgorithmsAggregatedStatistics::class);
    }

    public function getAlgorithmBuyerStatistic(Algorithm $algorithm, User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('stat')
            ->where('stat.algorithm = :algorithm')
            ->andWhere('stat.mediabuyer = :mediaBuyer')
            ->setParameters([
                'algorithm' => $algorithm,
                'mediaBuyer' => $mediaBuyer
            ])
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
