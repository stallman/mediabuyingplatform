<?php

namespace App\Repository;

use App\Entity\CurrencyList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CurrencyList|null find($id, $lockMode = null, $lockVersion = null)
 * @method CurrencyList|null findOneBy(array $criteria, array $orderBy = null)
 * @method CurrencyList[]    findAll()
 * @method CurrencyList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CurrencyListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CurrencyList::class);
    }

    public function getByIsoCode(string $code)
    {
        $query = $this->createQueryBuilder('currency')
            ->where('currency.iso_code = :code')
            ->setParameter('code', $code)
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
