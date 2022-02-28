<?php

namespace App\Repository;

use App\Entity\TeasersGroup;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TeasersGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeasersGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeasersGroup[]    findAll()
 * @method TeasersGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeasersGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeasersGroup::class);
    }

    public function getUserTeasersGroupList(User $user)
    {
        $query = $this->createQueryBuilder('tg')
            ->where('tg.user = :user')
            ->andWhere('tg.is_deleted = :isDeleted')
            ->setParameters([
                'user' => $user->getId(),
                'isDeleted' => 0,
            ])
            ->getQuery();

        return $query->getResult();
    }

    public function getUserTeasersGroupSubgroup(User $user)
    {
        $query = $this->createQueryBuilder('tg')
            ->select('partial tg.{id,name}')
            ->leftJoin('tg.teasersSubGroup', 'tsg')
            ->addSelect('partial tsg.{id,name}')
            ->where('tg.user = :user')
            ->andWhere('tg.is_deleted = :isDeleted')
            ->andWhere('tsg.is_deleted = :isDeleted')
            ->andWhere('tg.isActive = :isActive')
            ->andWhere('tsg.isActive = :isActive')
            ->setParameters([
                'user' => $user->getId(),
                'isDeleted' => 0,
                'isActive' => 1,
            ])
            ->getQuery();

        return $query->getArrayResult();
    }
}
