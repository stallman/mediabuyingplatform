<?php

namespace App\Repository;

use App\Entity\Algorithm;
use App\Entity\MediabuyerAlgorithms;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Algorithm|null find($id, $lockMode = null, $lockVersion = null)
 * @method Algorithm|null findOneBy(array $criteria, array $orderBy = null)
 * @method Algorithm[]    findAll()
 * @method Algorithm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlgorithmRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Algorithm::class);
    }

    public function getWhereNotByName(string $name)
    {
        $query = $this->createQueryBuilder('algorithm')
            ->where('algorithm.name != :name')
            ->setParameter('name', $name)
            ->getQuery();

        return $query->getResult();
    }

    public function getWhereNotByNameWithStat(string $name, User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('a')
            ->select('a.id, a.name, aStat.CTR, aStat.conversion, aStat.approveConversion, aStat.eCPM, aStat.EPC,
             aStat.CR, aStat.ROI, mbA.id as is_active')
            ->leftJoin('a.algorithmsAggregatedStatistics', 'aStat', 'WITH', 'aStat.mediabuyer = :mediabuyer')
            ->leftJoin(MediabuyerAlgorithms::class, 'mbA', 'WITH', 'a.id = mbA.algorithm AND mbA.mediabuyer = :mediabuyer')
            ->where('a.name != :name')
            ->setParameters([
                'name' => $name,
                'mediabuyer' => $mediaBuyer,
            ])
            ->getQuery();

        return $query->getResult();
    }

    public function getWithStat(User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('a')
            ->select('a.id, a.name, aStat.CTR, aStat.conversion, aStat.approveConversion, aStat.eCPM, aStat.EPC,
             aStat.CR, aStat.ROI, mbA.id as is_active')
            ->leftJoin('a.algorithmsAggregatedStatistics', 'aStat', 'WITH', 'aStat.mediabuyer = :mediabuyer')
            ->leftJoin(MediabuyerAlgorithms::class, 'mbA', 'WITH', 'a.id = mbA.algorithm AND mbA.mediabuyer = :mediabuyer')
            ->setParameters([
                'mediabuyer' => $mediaBuyer,
            ])
            ->orderBy('a.id', 'asc')
            ->getQuery();

        return $query->getResult();
    }

    public function getIsActiveForBuyer(Algorithm $algorithm, User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('algorithm')
            ->select('mbAlgorithm.id')
            ->leftJoin(MediabuyerAlgorithms::class, 'mbAlgorithm', 'WITH', 'algorithm.id = mbAlgorithm.algorithm AND mbAlgorithm.mediabuyer = :mediabuyer')
            ->where('algorithm.id = :algorithmId')
            ->setParameters([
                'mediabuyer' => $mediaBuyer,
                'algorithmId' => $algorithm->getId()
            ])
            ->getQuery();

        return $query->getOneOrNullResult()['id'];
    }

    public function getAlgorithmForBuyer(User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('algorithm')
            ->innerJoin(MediabuyerAlgorithms::class, 'mbAlgorithm', 'WITH', 'algorithm.id = mbAlgorithm.algorithm AND mbAlgorithm.mediabuyer = :mediabuyer')
            ->where('algorithm.isActive = :isActive')
            ->setParameters([
                'mediabuyer' => $mediaBuyer,
                'isActive' => true
            ])
            ->getQuery();

        return $query->getResult();
    }

    public function getDefaultAlgorithm(User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('algorithm')
            ->where('algorithm.isDefault = :isDefault')
            ->setParameter('isDefault', true)
            ->getQuery();

        return $query->getResult();
    }
}
