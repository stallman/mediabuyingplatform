<?php

namespace App\Repository;

use App\Entity\DomainParking;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DomainParking|null find($id, $lockMode = null, $lockVersion = null)
 * @method DomainParking|null findOneBy(array $criteria, array $orderBy = null)
 * @method DomainParking[]    findAll()
 * @method DomainParking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DomainParkingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DomainParking::class);
    }

    public function getMediaBuyerDomainsList(User $user)
    {
        $query = $this->createQueryBuilder('domains')
            ->where('domains.user = :user')
            ->andWhere('domains.is_deleted = :is_delete')
            ->setParameters([
                'user' => $user->getId(),
                'is_delete' => 0,
            ])
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param Int|User $user
     * @return int|mixed|string
     */
    public function getMainMediaBuyerDomain($user)
    {
        $query = $this->createQueryBuilder('domains')
            ->where('domains.user = :user')
            ->andWhere('domains.is_main = :isMain')
            ->setParameters([
                'user' => $user,
                'isMain' => true
            ])
            ->getQuery();

        return $query->getResult();
    }

    public function getDomainByName(string $name)
    {
        $query = $this->createQueryBuilder('domains')
            ->where('domains.domain = :domain')
            ->setParameters([
                'domain' => $name
            ])
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function getUserDomainByName(string $name, User $buyer)
    {
        $query = $this->createQueryBuilder('domains')
            ->where('domains.domain = :domain')
            ->andWhere('domains.user = :buyer')
            ->setParameters([
                'domain' => $name,
                'buyer' => $buyer
            ])
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function getDomainsNeedCert($from, $to)
    {
        $query = $this->createQueryBuilder('domains')
            ->where('domains.certEndDate is NULL')
            ->orWhere('domains.certEndDate BETWEEN :from AND :to')
            ->andWhere('domains.is_deleted = :is_delete')
            ->setParameters([
                'from' => $from,
                'to' => $to,
                'is_delete' => 0
            ])
            ->getQuery();

        return $query->getResult();
    }
}
