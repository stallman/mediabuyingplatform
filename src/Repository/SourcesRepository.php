<?php

namespace App\Repository;

use App\Entity\Sources;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sources|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sources|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sources[]    findAll()
 * @method Sources[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SourcesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sources::class);
    }

    public function getMediaBuyerSource(User $user, int $id)
    {
        $query = $this->createQueryBuilder('sources')
            ->where('sources.user = :user')
            ->andWhere('sources.is_deleted = :is_deleted')
            ->andWhere('sources.id = :id')
            ->setParameters([
                'id' => $id,
                'user' => $user->getId(),
                'is_deleted' => 0
            ])
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function getMediaBuyerSourcesList(User $user)
    {
        //TODO в будущем в этот метод будет добавлен join к тизерам
        $query = $this->createQueryBuilder('sources')
            ->where('sources.user = :user')
            ->andWhere('sources.is_deleted = :is_deleted')
            ->setParameters([
                'user' => $user->getId(),
                'is_deleted' => 0
            ])
            ->getQuery();

        return $query->getResult();
    }

    public function getMediaBuyerSources(User $mediaBuyer, ?string $dropSources)
    {
        $query = $this->createQueryBuilder('sources')
            ->where('sources.user = :mediaBuyer')
            ->andWhere('sources.is_deleted != :is_deleted')
            ->andWhere('sources.id NOT IN (:dropSources)')
            ->setParameters([
                'mediaBuyer' => $mediaBuyer->getId(),
                'is_deleted' => 1,
                'dropSources' => $dropSources
            ])
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Get media buyer sources by source IDs, If IDs is null will return all entities
     *
     * @param User $mediaBuyer
     * @param array|null $ids
     * @return int|mixed|string
     */
    public function getByIds(User $mediaBuyer, array $ids = null)
    {
        $builder = $this->createQueryBuilder('sources')
                    ->where('sources.user = :mediabuyer')
                    ->andWhere('sources.is_deleted = :is_deleted');
        $parameters = [
            'mediabuyer' => $mediaBuyer,
            'is_deleted' => 0,
        ];
        if($ids !== null) {
            $parameters['ids'] = $ids;

            $builder->andWhere('sources.id IN (:ids)');
        }

        return $builder
            ->setParameters($parameters)
            ->getQuery()
            ->getResult();
    }
}
