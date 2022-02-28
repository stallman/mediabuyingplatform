<?php

namespace App\Repository;

use App\Entity\MediabuyerNews;
use App\Entity\MediabuyerNewsRotation;
use App\Entity\News;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MediabuyerNews|null find($id, $lockMode = null, $lockVersion = null)
 * @method MediabuyerNews|null findOneBy(array $criteria, array $orderBy = null)
 * @method MediabuyerNews[]    findAll()
 * @method MediabuyerNews[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediabuyerNewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediabuyerNews::class);
    }

    public function getMediaBuyerNewsItem(User $user, News $news)
    {
        $query = $this->createQueryBuilder('mbn')
            ->where('mbn.mediabuyer = :user')
            ->andWhere('mbn.news = :news')
            ->setParameters([
                'user' => $user,
                'news' => $news
            ])
            ->getQuery();

        return $query->getOneOrNullResult();
    }


    public function getMediaBuyerNewsRotation(User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('mbn')
            ->where('mbn.mediabuyer = :mediaBuyer')
            ->leftJoin(MediabuyerNewsRotation::class, 'mbnr', 'WITH', 'mbn.news = mbnr.news AND mbn.mediabuyer = mbnr.mediabuyer')
            ->andWhere('mbnr.isRotation = :isRotation')
            ->setParameters([
                'mediaBuyer' => $mediaBuyer->getId(),
                'isRotation' => 1,
            ])
            ->getQuery();

        return $query->getResult();
    }
}