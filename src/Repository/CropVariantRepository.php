<?php

namespace App\Repository;

use App\Entity\CropVariant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CropVariant|null find($id, $lockMode = null, $lockVersion = null)
 * @method CropVariant|null findOneBy(array $criteria, array $orderBy = null)
 * @method CropVariant[]    findAll()
 * @method CropVariant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CropVariantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CropVariant::class);
    }

    public function getCropVariantsWithNewsBlockDimensions()
    {
        $query = $this->createQueryBuilder('crop_variant')
            ->select('crop_variant.width_news_block', 'crop_variant.height_news_block', 'crop_variant.id')
            ->where('crop_variant.height_news_block IS NOT NULL')
            ->andWhere('crop_variant.width_news_block IS NOT NULL')
            ->getQuery();

        return $query->getResult();
    }
}
