<?php

namespace App\Repository;

use App\Entity\MediabuyerDesigns;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MediabuyerDesigns|null find($id, $lockMode = null, $lockVersion = null)
 * @method MediabuyerDesigns|null findOneBy(array $criteria, array $orderBy = null)
 * @method MediabuyerDesigns[]    findAll()
 * @method MediabuyerDesigns[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediabuyerDesignsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediabuyerDesigns::class);
    }

    public function getMediabuyerDesign(int $designId, User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('mbDesigns')
            ->where('mbDesigns.design = :designId')
            ->andWhere('mbDesigns.mediabuyer = :mediaBuyer')
            ->setParameters([
                'designId' => $designId,
                'mediaBuyer' => $mediaBuyer
            ])
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
