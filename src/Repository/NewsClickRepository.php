<?php

namespace App\Repository;

use App\Contract\CleanableRepositoryInterface;
use App\Entity\NewsClick;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NewsClick|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewsClick|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewsClick[]    findAll()
 * @method NewsClick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsClickRepository extends ServiceEntityRepository implements CleanableRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsClick::class);
    }

    public function getCountClick(int $newsId)
    {
        $query = $this->createQueryBuilder('nc')
            ->select('count(nc.id) as count')
            ->where('nc.news = :news')
            ->setParameters([
                'news' => $newsId
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getCountBuyerClick(int $newsId, User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('nc')
            ->select('count(nc.id) as count')
            ->where('nc.news = :news')
            ->andWhere('nc.buyer = :mediaBuyer')
            ->setParameters([
                'news' => $newsId,
                'mediaBuyer' => $mediaBuyer
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function queryOlderThan(User $buyer, int $days, bool $count = false) : QueryBuilder {
        $builder = $this->createQueryBuilder('nc')
            ->where('nc.buyer = :buyer')
            ->andWhere("nc.createdAt < DATE_SUB(CURRENT_DATE(), :days, 'DAY')")
            ->setParameters(compact('buyer', 'days'));

        if($count){
            $builder->select('COUNT(nc.id)');
        }else{
            $builder->delete();
        }

        return $builder;
    }
}
