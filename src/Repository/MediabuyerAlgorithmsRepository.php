<?php

namespace App\Repository;

use App\Entity\MediabuyerAlgorithms;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MediabuyerAlgorithms|null find($id, $lockMode = null, $lockVersion = null)
 * @method MediabuyerAlgorithms|null findOneBy(array $criteria, array $orderBy = null)
 * @method MediabuyerAlgorithms[]    findAll()
 * @method MediabuyerAlgorithms[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediabuyerAlgorithmsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediabuyerAlgorithms::class);
    }

    public function getMediabuyerAlgorithm(int $algorithmId, User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('mbAlgorithms')
            ->where('mbAlgorithms.algorithm = :algorithmId')
            ->andWhere('mbAlgorithms.mediabuyer = :mediaBuyer')
            ->setParameters([
                'algorithmId' => $algorithmId,
                'mediaBuyer' => $mediaBuyer
            ])
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
