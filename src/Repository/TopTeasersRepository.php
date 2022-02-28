<?php

namespace App\Repository;

use App\Entity\TopTeasers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TopTeasers|null find($id, $lockMode = null, $lockVersion = null)
 * @method TopTeasers|null findOneBy(array $criteria, array $orderBy = null)
 * @method TopTeasers[]    findAll()
 * @method TopTeasers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TopTeasersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TopTeasers::class);
    }
}
