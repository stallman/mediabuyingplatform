<?php

namespace App\Repository;

use App\Entity\Partners;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Partners|null find($id, $lockMode = null, $lockVersion = null)
 * @method Partners|null findOneBy(array $criteria, array $orderBy = null)
 * @method Partners[]    findAll()
 * @method Partners[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PartnersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Partners::class);
    }

    public function getMediaBuyerPartnersList(User $user)
    {
        $query = $this->createQueryBuilder('partner')
            ->where('partner.user = :user')
            ->andWhere('partner.is_deleted = :isDeleted')
            ->setParameters([
                'user' => $user->getId(),
                'isDeleted' => false
            ])
            ->getQuery();

        return $query->getResult();
    }
}
