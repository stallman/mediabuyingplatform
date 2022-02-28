<?php

namespace App\Repository;

use App\Entity\MediabuyerNewsRotation;
use App\Entity\News;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MediabuyerNewsRotation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MediabuyerNewsRotation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MediabuyerNewsRotation[]    findAll()
 * @method MediabuyerNewsRotation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediabuyerNewsRotationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediabuyerNewsRotation::class);
    }

    public function getMediaBuyerNewsRotationItem(User $user, News $news)
    {
        $query = $this->createQueryBuilder('mbnr')
            ->where('mbnr.mediabuyer = :user')
            ->andWhere('mbnr.news = :news')
            ->setParameters([
                'user' => $user,
                'news' => $news
            ])
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function getMediaBuyerNewsRotationItemById(User $user, int $id)
    {
        $query = $this->createQueryBuilder('mbnr')
            ->where('mbnr.mediabuyer = :user')
            ->andWhere('mbnr.news = :news')
            ->setParameters([
                'user' => $user,
                'news' => $id
            ])
            ->getQuery();

        return $query->getOneOrNullResult();
    }

}
