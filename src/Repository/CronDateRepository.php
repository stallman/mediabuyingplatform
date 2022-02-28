<?php

namespace App\Repository;

use App\Entity\CronDate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CronDate|null find($id, $lockMode = null, $lockVersion = null)
 * @method CronDate|null findOneBy(array $criteria, array $orderBy = null)
 * @method CronDate[]    findAll()
 * @method CronDate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CronDateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CronDate::class);
    }
}
