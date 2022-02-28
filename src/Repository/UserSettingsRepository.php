<?php

namespace App\Repository;

use App\Entity\UserSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSettings[]    findAll()
 * @method UserSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSettings::class);
    }

    public function getUserSetting(int $userId, string $slugSettings)
    {
        $query = $this->createQueryBuilder('us')
            ->select('us.value')
            ->where('us.user = :userId')
            ->andWhere('us.slug = :slugSettings')
            ->setParameters([
                'userId' => $userId,
                'slugSettings' => $slugSettings
            ])
            ->getQuery();

        return $query->getOneOrNullResult()['value'];
    }
}
