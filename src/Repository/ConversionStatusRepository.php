<?php

namespace App\Repository;

use App\Entity\ConversionStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConversionStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConversionStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConversionStatus[]    findAll()
 * @method ConversionStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConversionStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConversionStatus::class);
    }

    // /**
    //  * @return ConversionStatus[] Returns an array of ConversionStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ConversionStatus
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
