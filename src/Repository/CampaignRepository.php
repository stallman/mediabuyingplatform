<?php

namespace App\Repository;

use App\Entity\Campaign;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Campaign|null find($id, $lockMode = null, $lockVersion = null)
 * @method Campaign|null findOneBy(array $criteria, array $orderBy = null)
 * @method Campaign[]    findAll()
 * @method Campaign[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CampaignRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campaign::class);
    }


    public function purge(User $mediabuyer = null): void
    {
        $qb = $this->createQueryBuilder('c')->delete();

        if (null !== $mediabuyer) {
            $qb->where('c.mediabuyer = :mediabuyer')
                ->setParameter('mediabuyer', $mediabuyer)
            ;
        }

        $qb->getQuery()->execute();
    }
}
