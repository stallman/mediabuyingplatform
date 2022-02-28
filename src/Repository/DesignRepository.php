<?php

namespace App\Repository;

use App\Entity\Design;
use App\Entity\MediabuyerDesigns;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Design|null find($id, $lockMode = null, $lockVersion = null)
 * @method Design|null findOneBy(array $criteria, array $orderBy = null)
 * @method Design[]    findAll()
 * @method Design[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DesignRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Design::class);
    }

    public function getIsActiveForBuyer(Design $design, User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('design')
            ->select('mbDesign.id')
            ->leftJoin(MediabuyerDesigns::class, 'mbDesign', 'WITH', 'design.id = mbDesign.design AND mbDesign.mediabuyer = :mediabuyer')
            ->where('design.id = :designId')
            ->setParameters([
                'mediabuyer' => $mediaBuyer,
                'designId' => $design->getId()
            ])
            ->getQuery();

        return $query->getOneOrNullResult()['id'];
    }

    public function getDesignForBuyer(User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('design')
            ->innerJoin(MediabuyerDesigns::class, 'mbDesign', 'WITH', 'design.id = mbDesign.design AND mbDesign.mediabuyer = :mediabuyer')
            ->where('design.isActive = :isActive')
            ->setParameters([
                'mediabuyer' => $mediaBuyer,
                'isActive' => true
            ])
            ->getQuery();

        return $query->getResult();
    }

    public function getDesignWithStatistic(User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('design')
            ->select('design.id, design.name, designStat.probiv, designStat.CTR, designStat.conversion, designStat.approveConversion,
             designStat.EPC, designStat.CR, designStat.ROI, mbDesign.id as is_active')
            ->leftJoin('design.designsAggregatedStatistics', 'designStat', 'WITH', 'designStat.mediabuyer = :mediabuyer')
            ->leftJoin(MediabuyerDesigns::class, 'mbDesign', 'WITH', 'design.id = mbDesign.design AND mbDesign.mediabuyer = :mediabuyer')
            ->where('design.isActive = :isActive')
            ->setParameters([
                'mediabuyer' => $mediaBuyer,
                'isActive' => true
            ])
            ->getQuery();
        return $query->getResult();
    }
}
