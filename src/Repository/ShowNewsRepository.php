<?php

namespace App\Repository;

use App\Entity\News;
use App\Entity\ShowNews;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShowNews|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShowNews|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShowNews[]    findAll()
 * @method ShowNews[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShowNewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShowNews::class);
    }

    public function getShowNews(News $news, User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('sn')
            ->select('count(sn.id) as count')
            ->where('sn.news = :news')
            ->andWhere('sn.mediabuyer = :mediaBuyer')
            ->setParameters([
                'news' => $news,
                'mediaBuyer' => $mediaBuyer,
            ])
            ->getQuery();

        return $query->getOneOrNullResult()['count'];
    }
}
