<?php

namespace App\Repository;

use App\Entity\Design;
use App\Entity\DesignsAggregatedStatistics;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DesignsAggregatedStatistics|null find($id, $lockMode = null, $lockVersion = null)
 * @method DesignsAggregatedStatistics|null findOneBy(array $criteria, array $orderBy = null)
 * @method DesignsAggregatedStatistics[]    findAll()
 * @method DesignsAggregatedStatistics[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DesignsAggregatedStatisticsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DesignsAggregatedStatistics::class);
    }

    public function getDesignBuyerStatistic(Design $design, User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('stat')
            ->where('stat.design = :design')
            ->andWhere('stat.mediabuyer = :mediaBuyer')
            ->setParameters([
                'design' => $design,
                'mediaBuyer' => $mediaBuyer
            ])
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
