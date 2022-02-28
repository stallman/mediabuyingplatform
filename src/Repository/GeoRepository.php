<?php

namespace App\Repository;

use App\Entity\Geo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Geo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Geo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Geo[]    findAll()
 * @method Geo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GeoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Geo::class);
    }

    public function getCityTranslate($country, $city)
    {
        $query = $this->createQueryBuilder('geo')
            ->where('geo.countryName = :countryName')
            ->andWhere('geo.cityName = :cityName')
            ->setParameters([
                'countryName' => $country,
                'cityName' => $city
            ])
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
