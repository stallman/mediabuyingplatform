<?php

namespace App\Repository;

use App\Entity\StatisticTeasers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StatisticTeasers|null find($id, $lockMode = null, $lockVersion = null)
 * @method StatisticTeasers|null findOneBy(array $criteria, array $orderBy = null)
 * @method StatisticTeasers[]    findAll()
 * @method StatisticTeasers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatisticTeasersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatisticTeasers::class);
    }
}
