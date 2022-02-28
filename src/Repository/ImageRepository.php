<?php

namespace App\Repository;

use App\Entity\EntityInterface;
use App\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Image|null find($id, $lockMode = null, $lockVersion = null)
 * @method Image|null findOneBy(array $criteria, array $orderBy = null)
 * @method Image[]    findAll()
 * @method Image[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Image::class);
    }

    /**
     * @param EntityInterface $entity
     * @return int|mixed|string|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getEntityImage(EntityInterface $entity)
    {
        $query = $this->createQueryBuilder('image')
            ->where('image.entityFQN = :entityFQN')
            ->andWhere('image.entityId = :entityId')
            ->setParameters([
                'entityFQN' => get_class($entity),
                'entityId' => $entity->getId(),
            ])
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function getByClassAndId(string $class, int $id)
    {
        $query = $this->createQueryBuilder('image')
            ->where('image.entityFQN = :entityFQN')
            ->andWhere('image.entityId = :entityId')
            ->setParameters([
                'entityFQN' => $class,
                'entityId' => $id,
            ])
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
