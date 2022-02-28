<?php

namespace App\Repository;

use App\Entity\Country;
use App\Entity\TeasersGroup;
use App\Entity\TeasersSubGroup;
use App\Entity\TeasersSubGroupSettings;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TeasersSubGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeasersSubGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeasersSubGroup[]    findAll()
 * @method TeasersSubGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeasersSubGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeasersSubGroup::class);
    }

    public function getUserSubGroup(User $user) {
        $query = $this->createQueryBuilder('tsg')
            ->leftJoin(TeasersGroup::class, 'tg', 'WITH', 'tsg.teaserGroup = tg.id')
            ->where('tg.user = :user')
            ->setParameter('user', $user)
            ->getQuery();

        return $query->getResult();
    }

    public function getUserCountrySubGroup(User $user, Country $country) {
        $query = $this->createQueryBuilder('tsg')
            ->leftJoin(TeasersGroup::class, 'tg', 'WITH', 'tsg.teaserGroup = tg.id')
            ->leftJoin(TeasersSubGroupSettings::class, 'tsgs', 'WITH', 'tsgs.teasersSubGroup = tsg.id')
            ->where('tg.user = :user')
            ->andWhere('tsgs.geoCode = :country')
            ->setParameters([
                'user' => $user,
                'country' => $country
            ])
            ->getQuery();

        return $query->getResult();
    }

    public function countSubGroupByParent(TeasersGroup $teasersGroup) {
        $query = $this->createQueryBuilder('tsg')
            ->select('count(tsg.id) as count')
            ->where('tsg.teaserGroup = :teaserGroup')
            ->andWhere('tsg.is_deleted = :isDeleted')
            ->setParameters([
                'teaserGroup' => $teasersGroup,
                'isDeleted' => 0
                ])
            ->getQuery();

        return $query->getOneOrNullResult()['count'];

    }
}
