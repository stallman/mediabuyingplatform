<?php

namespace App\Repository;

use App\Entity\NewsClickShortToFull;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NewsClickShortToFull|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewsClickShortToFull|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewsClickShortToFull[]    findAll()
 * @method NewsClickShortToFull[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsClickShortToFullRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsClickShortToFull::class);
    }

    public function getCountClick(int $newsId)
    {
        $query = $this->createQueryBuilder('ncsf')
            ->select('count(ncsf.id) as count')
            ->where('ncsf.news = :news')
            ->setParameters([
                'news' => $newsId
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getCountBuyerClick(int $newsId, User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('ncsf')
            ->select('count(ncsf.id) as count')
            ->where('ncsf.news = :news')
            ->andWhere('ncsf.buyer = :mediaBuyer')
            ->setParameters([
                'news' => $newsId,
                'mediaBuyer' => $mediaBuyer
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }
}
