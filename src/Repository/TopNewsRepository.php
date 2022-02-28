<?php

namespace App\Repository;

use App\Entity\TopNews;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TopNews|null find($id, $lockMode = null, $lockVersion = null)
 * @method TopNews|null findOneBy(array $criteria, array $orderBy = null)
 * @method TopNews[]    findAll()
 * @method TopNews[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TopNewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TopNews::class);
    }
}
