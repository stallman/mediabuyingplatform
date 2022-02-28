<?php

namespace App\Repository;

use App\Entity\OtherFiltersData;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OtherFiltersData|null find($id, $lockMode = null, $lockVersion = null)
 * @method OtherFiltersData|null findOneBy(array $criteria, array $orderBy = null)
 * @method OtherFiltersData[]    findAll()
 * @method OtherFiltersData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OtherFiltersDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OtherFiltersData::class);
    }

    public function checkExists(User $mediaBuyer, string $type, string $options)
    {
        $query = $this->createQueryBuilder('ofd')
            ->select('count(ofd.id) as count')
            ->where('ofd.mediabuyer = :mediabuyer')
            ->andWhere('ofd.type = :type')
            ->andWhere('ofd.options = :options')
            ->setParameters([
                'mediabuyer' => $mediaBuyer,
                'type' => $type,
                'options' => $options,
            ])
            ->getQuery();

        return $query->getOneOrNullResult()['count'];
    }
}
