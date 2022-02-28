<?php

namespace App\Repository;

use App\Entity\Sources;
use App\Entity\Teaser;
use App\Entity\StatisticTeasers;
use App\Entity\TeasersSubGroup;
use App\Entity\User;
use App\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;
use Faker\Provider\DateTime;

/**
 * @method Teaser|null find($id, $lockMode = null, $lockVersion = null)
 * @method Teaser|null findOneBy(array $criteria, array $orderBy = null)
 * @method Teaser[]    findAll()
 * @method Teaser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeasersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Teaser::class);
    }

    /**
     * @param int $status
     * @return int|mixed|string
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTeasersByStatus(int $status)
    {
        $query = $this->createQueryBuilder('teasers')
            ->where('teasers.isActive = :status')
            ->setParameter('status', $status)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getActiveTeasers(User $user, ?string $source)
    {
        $query = $this->createQueryBuilder('teasers')
            ->select('teasers.id', 'teasers.text', 'image.filePath', 'image.fileName')
            ->leftJoin(Image::class, 'image', 'WITH', 'teasers.id = image.entityId AND image.entityFQN = :entityFQN')
            ->where('teasers.isActive = :status')
            ->andWhere('teasers.user = :user')
            ->andWhere('teasers.dropSources NOT LIKE :source')
            ->setParameters([
                'status' => 1,
                'user' => $user,
                'source' => "%$source%",
                'entityFQN' => get_class(new Teaser()),
            ])
            ->getQuery();

        return $query->getResult();
    }

    public function getMediaBuyerTeasersList(User $user)
    {
        $query = $this->createQueryBuilder('teasers')
            ->where('teasers.user = :user')
            ->andWhere('teasers.is_deleted = :isDeleted')
            ->setParameters([
                'user' => $user->getId(),
                'isDeleted' => false
            ])
            ->orderBy('teasers.id', 'DESC')
            ->getQuery();

        return $query->getResult();
    }

    public function getCountTeasers(User $user, $filterSubGroups = [], $search = null, bool $isActive = null)
    {
        $params = [
            'user' => $user->getId(),
            'is_deleted' => 0,
            'role' => '%ROLE_MEDIABUYER%'
        ];
        $query = $this->createQueryBuilder('teasers')
            ->select('count(teasers.id) as count')
            ->leftJoin(User::class, 'user', Expr\Join::WITH, 'teasers.user = user.id')
            ->where('user.roles LIKE :role')
            ->andWhere('teasers.user = :user')
            ->andWhere('teasers.is_deleted = :is_deleted');

        if(!is_null($isActive)){
            $query = $query->andWhere('teasers.isActive = :isActive');
            $params['isActive'] = $isActive;
        }
        if(!empty($filterSubGroups)) {
            $query->andWhere('teasers.teasersSubGroup IN(:teasers_sub_group_ids)');
            $params['teasers_sub_group_ids'] = $filterSubGroups;
        }
        $query = $query
            ->setParameters($params);

        if($search){
            $query = $this->searchTeasers($query, $search);
        }

        return $query->getQuery()->getSingleResult()['count'];
    }

    public function getTeasersPaginateList(User $user, array $order, $length = 20, $start = 0, $filterSubGroups = [], $search = null)
    {

        $params = [
            'user' => $user->getId(),
            'is_deleted' => 0,
            'role' => '%ROLE_MEDIABUYER%'
        ];
        $query = $this->createQueryBuilder('teasers')
            ->leftJoin(User::class, 'user', Expr\Join::WITH, 'teasers.user = user.id')
            ->leftJoin(StatisticTeasers::class, 'statistic', Expr\Join::WITH, 'statistic.teaser = teasers.id')
            ->where('user.roles LIKE :role')
            ->andWhere('teasers.user = :user')
            ->andWhere('teasers.is_deleted = :is_deleted')
            ->setFirstResult($start)
            ->setMaxResults($length);

        if(!empty($filterSubGroups)) {
            $query->andWhere('teasers.teasersSubGroup IN(:teasers_sub_group_ids)');
            $params['teasers_sub_group_ids'] = $filterSubGroups;
        }

        $query = $query
            ->setParameters($params);

        if($search){
            $query = $this->searchTeasers($query, $search);
        }

        $query = $query->orderBy('teasers.id', 'DESC')
                ->orderBy("{$order[0]['column']}", "{$order[0]['dir']}")
                ->getQuery();

        return $query->getResult();
    }


    public function getCountTeasersBySubGroup(TeasersSubGroup $subGroup)
    {
        $query = $this->createQueryBuilder('teasers')
            ->select('count(teasers.id) as count')
            ->where('teasers.teasersSubGroup = :subGroup')
            ->andWhere('teasers.is_deleted = :isDeleted')
            ->setParameters([
                'subGroup' => $subGroup,
                'isDeleted' => false
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getCountTeasersByDropSource(Sources $source)
    {
        $sourceId = strval($source->getId());
        $dropSource = serialize($sourceId);
        $query = $this->createQueryBuilder('teasers')
            ->select('count(teasers.id) as count')
            ->where('teasers.dropSources LIKE :source')
            ->andWhere('teasers.is_deleted = :isDeleted')
            ->setParameters(
                [
                    'source' => "%{$dropSource}%",
                    'isDeleted' => 0,
                ]
            )
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getTeasersForNews(User $mediaBuyer, string $source, string $news, ?string $dropTeasers)
    {
 
        $query = $this->createQueryBuilder('teasers')
            ->select('teasers.id', 'teasers.text', 'image.filePath', 'image.fileName')
            ->leftJoin(Image::class, 'image', 'WITH', 'teasers.id = image.entityId AND image.entityFQN = :entityFQN')
            ->where('teasers.user = :user')
            ->andWhere('teasers.dropSources NOT LIKE :source')
            ->andWhere('teasers.dropNews NOT LIKE :news')
            ->andWhere('teasers.id NOT IN (:dropTeasers)')
            ->andWhere('teasers.isActive = :status')
            ->andWhere('teasers.is_deleted = :isDeleted')
            ->setParameters([
                'user' => $mediaBuyer,
                'source' => "%$source%",
                'news' => "%$news%",
                'dropTeasers' => explode(",", $dropTeasers),
                'status' => 1,
                'isDeleted' => false,
                'entityFQN' => get_class(new Teaser()),
            ])
            ->getQuery();

        return $query->getResult();
    }

    public function getTeasersBySubGroup(TeasersSubGroup $subGroup)
    {
        $query = $this->createQueryBuilder('teasers')
            ->where('teasers.teasersSubGroup = :subGroup')
            ->andWhere('teasers.is_deleted = :isDeleted')
            ->setParameters([
                'subGroup' => $subGroup,
                'isDeleted' => false
            ])
            ->getQuery();

        return $query->getResult();
    }

    public function getTeasersStatisticData()
    {
        $today = new \DateTime();
        $yesterday = (new \DateTime())->modify('-1 day');

        $query = $this->createQueryBuilder('teasers')
            ->select('teasers.id, teasers.is_deleted, teasers.isActive, teasers.updatedAt')
            ->where('teasers.is_deleted = :isDeleted')
            ->andWhere('teasers.isActive = :isActive')
            ->andWhere('teasers.updatedAt BETWEEN :yesterday AND :today')
            ->setParameters([
                'isDeleted' => false,
                'isActive' => true,
                'today' => $today,
                'yesterday' => $yesterday
            ])
            ->getQuery()
        ;

        return $query->getResult();
    }

    private function searchTeasers($query, $search)
    {
        return $query->andWhere('teasers.id LIKE :search OR teasers.text LIKE :search')
            ->setParameter('search', "%$search%");
    }

    public function getCountTeasersOwnECPM(User $user, int $teaserShow)
    {
        $query = $this->createQueryBuilder('teasers')
            ->select('count(teasers.id) as count')
            ->leftJoin('teasers.topTeaser', 'topTeaser')
            ->leftJoin('teasers.statistic', 'statistic')
            ->andWhere('teasers.user = :user')
            ->andWhere('teasers.is_deleted = :is_deleted')
            ->andWhere('statistic.teaserShow >= :teaserShow')
            ->setParameters([
                'user' => $user,
                'is_deleted' => 0,
                'teaserShow' => $teaserShow,
            ]);

        return $query->getQuery()->getSingleResult()['count'];
    }
}
