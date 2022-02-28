<?php

namespace App\Repository;

use App\Entity\Country;
use App\Entity\TeasersSubGroup;
use App\Entity\TeasersSubGroupSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TeasersSubGroupSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeasersSubGroupSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeasersSubGroupSettings[]    findAll()
 * @method TeasersSubGroupSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeasersSubGroupSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeasersSubGroupSettings::class);
    }

    public function getDefaultSubGroupSettings(TeasersSubGroup $subGroup)
    {
        $query = $this->createQueryBuilder('tsgSettings')
            ->where('tsgSettings.teasersSubGroup = :subGroup')
            ->andWhere('tsgSettings.geoCode IS NULL')
            ->setParameters([
                'subGroup' => $subGroup,
            ])
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function getCountrySubGroupSettings(TeasersSubGroup $subGroup, string $isoCode)
    {
        $query = $this->createQueryBuilder('tsgSettings')
            ->leftJoin(Country::class, 'country', 'WITH', 'country.iso_code = :isoCode')
            ->where('tsgSettings.teasersSubGroup = :subGroup')
            ->andWhere('tsgSettings.geoCode = country')
            ->setParameters([
                'subGroup' => $subGroup,
                'isoCode' => $isoCode,
            ])
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function getByUnDeletedSubGroup()
    {
        $query = $this->createQueryBuilder('tsgSettings')
            ->leftJoin('tsgSettings.teasersSubGroup', 'tsg')
            ->where('tsg.is_deleted = :isDeleted')
            ->setParameter('isDeleted', false)
            ->getQuery();

        return $query->getResult();
    }

    public function getApproveAvgPercentage(string $leadIds)
    {
        $rows = [];

        if (!empty($leadIds)) {
            $conn = $this->getEntityManager()->getConnection();

            $sql = <<<SQL
                SELECT c.id, c.subgroup_id, tsgs.approve_average_percentage AS lead_percentage 
                FROM teasers_sub_group_settings tsgs
                     LEFT JOIN conversions c ON (tsgs.teasers_sub_group_id = c.subgroup_id) 
                     LEFT JOIN teasers_click tc ON (tc.id = c.teaser_click_id) 
                WHERE c.id IN ($leadIds) 
                    AND (tsgs.geo_code = tc.country_code OR tsgs.geo_code IS NULL)
                GROUP BY c.id, c.subgroup_id
            SQL;

            $stmt = $conn->executeQuery($sql);

            $rows =  $stmt->fetchAllAssociative();
        }

        return $rows;
    }
}
