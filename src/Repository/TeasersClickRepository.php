<?php

namespace App\Repository;

use App\Contract\CleanableRepositoryInterface;
use App\Entity\Algorithm;
use App\Entity\Design;
use App\Entity\Teaser;
use App\Entity\TeasersClick;
use App\Entity\User;
use App\Entity\Country;
use App\Entity\News;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TeasersClick|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeasersClick|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeasersClick[]    findAll()
 * @method TeasersClick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeasersClickRepository extends ServiceEntityRepository implements CleanableRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeasersClick::class);
    }

    public function getTeaserClickCountDesign(User $user, Design $design)
    {
        $query = $this->createQueryBuilder('tc')
            ->select('count(tc.id) as count')
            ->where('tc.buyer = :user')
            ->andWhere('tc.design = :design')
            ->setParameters([
                'user' => $user,
                'design' => $design
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getTeaserClickCountAlgorithm(User $user, Algorithm $algorithm)
    {
        $query = $this->createQueryBuilder('tc')
            ->select('count(tc.id) as count')
            ->where('tc.buyer = :user')
            ->andWhere('tc.algorithm = :algorithm')
            ->setParameters([
                'user' => $user,
                'algorithm' => $algorithm
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getClickByTeasers(array $teasers)
    {
        $query = $this->createQueryBuilder('click')
            ->select('click.id')
            ->where("click.teaser IN(:teasers)")
            ->setParameter('teasers', $teasers)
            ->getQuery();

        return $query->getResult();
    }

    public function getClickByBuyer(User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('click')
            ->where("click.buyer IN(:mediaBuyer)")
            ->setParameter('mediaBuyer', $mediaBuyer)
            ->getQuery();

        return $query->getResult();
    }

    public function getByNewsAndTrafficType(News $news, User $mediaBuyer, string $trafficType, Country $country)
    {
        $query = $this->createQueryBuilder('tc')
            ->where('tc.news = :news')
            ->andWhere('tc.buyer = :mediaBuyer')
            ->andWhere('tc.trafficType = :trafficType')
            ->andWhere('tc.countryCode = :countryCode')
            ->setParameters([
                'news' => $news,
                'mediaBuyer' => $mediaBuyer,
                'trafficType' => $trafficType,
                'countryCode' => $country->getIsoCode(),
            ])
            ->getQuery();

        return $query->getResult();
    }

    public function getByTeaserAndTrafficType(Teaser $teaser, User $mediaBuyer, string $trafficType, Country $country)
    {
        $query = $this->createQueryBuilder('tc')
            ->where('tc.teaser = :teaser')
            ->andWhere('tc.buyer = :mediaBuyer')
            ->andWhere('tc.trafficType = :trafficType')
            ->andWhere('tc.countryCode = :countryCode')
            ->setParameters([
                'teaser' => $teaser,
                'mediaBuyer' => $mediaBuyer,
                'trafficType' => $trafficType,
                'countryCode' => $country->getIsoCode(),
            ])
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param string $pageType short|full|top
     */
    public function getBySourcesAndPageType(User $mediaBuyer, array $sources = [], string $pageType = 'short')
    {
        $query = $this->createQueryBuilder('tc')
            ->andWhere('tc.buyer = :mediaBuyer')
            ->andWhere('tc.pageType = :pageType')
        ;


        if ($sources) {
            $sql = 'tc.source IN (\'' . implode('\',\'', $sources) . '\')';
            if (false !== $nullValKey = array_search('NULL', $sources)) {
                unset($sources[$nullValKey]);

                $sql = 'tc.source IS NULL';
                if ($sources) {
                    $sql .= ' OR tc.source IN (\'' . implode('\',\'', $sources) . '\')';
                }
            }

            $query->andWhere($sql);
        }

        $query
            ->setParameters([
                'mediaBuyer' => $mediaBuyer,
                'pageType' => $pageType,
            ]);

        return $query->getQuery()->getResult();
    }

    public function getTeaserClickCountUuid(User $user, $uuid)
    {
        $query = $this->createQueryBuilder('tc')
            ->select('count(tc.id) as count')
            ->where('tc.buyer = :user')
            ->andWhere('tc.uuid = :uuid')
            ->setParameters([
                'user' => $user,
                'uuid' => $uuid
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getByBuyerUuids(User $user, array $uuids): array
    {
        $query = $this->createQueryBuilder('tc')
            ->where('tc.buyer = :user')
            ->andWhere('tc.uuid IN (:uuids)')
            ->setParameters([
                'user' => $user,
                'uuids' => $uuids,
            ])
            ->getQuery();

        return $query->getResult();
    }

    public function getTeaserClickCountUuidArr(User $user, array $uuid)
    {
        $query = $this->createQueryBuilder('tc')
            ->select('count(tc.id) as count')
            ->where('tc.buyer = :user')
            ->andWhere('tc.uuid IN (:uuid)')
            ->setParameters([
                                'user' => $user,
                                'uuid' => $uuid
                            ])
            ->getQuery();

        return $query->getResult();
    }

    public function getTeaserClickCountBuyer(User $user)
    {
        $query = $this->createQueryBuilder('tc')
            ->select('count(tc.id) as count')
            ->where('tc.buyer = :user')
            ->setParameters([
                'user' => $user
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getUniqueClickCount(User $user, string $uuid)
    {
        $query = $this->createQueryBuilder('tc')
            ->select('tc.id')
            ->where('tc.uuid = :uuid')
            ->andWhere('tc.buyer = :user')
            ->setParameters([
                'uuid' => $uuid,
                'user' => $user,
            ])
            ->groupBy('tc.buyer',
                'tc.source',
                'tc.teaser',
                'tc.news',
                'tc.design',
                'tc.algorithm',
                'tc.countryCode',
                'tc.trafficType',
                'tc.pageType',
                'tc.userIp',
                'tc.uuid')
            ->getQuery();

        return $query->getResult();
    }

    public function getUniqueClickCountArr(User $user, array $uuid)
    {
        $query = $this->createQueryBuilder('tc')
            ->select('tc.id')
            ->where('tc.uuid IN (:uuid)')
            ->andWhere('tc.buyer = :user')
            ->setParameters([
                                'uuid' => $uuid,
                                'user' => $user,
                            ])
            ->groupBy('tc.buyer',
                      'tc.source',
                      'tc.teaser',
                      'tc.news',
                      'tc.design',
                      'tc.algorithm',
                      'tc.countryCode',
                      'tc.trafficType',
                      'tc.pageType',
                      'tc.userIp',
                      'tc.uuid')
            ->getQuery();

        return $query->getResult();
    }

    public function queryOlderThan(User $buyer, int $days, bool $count = false) : QueryBuilder {
        $builder = $this->createQueryBuilder('tc')
            ->where('tc.buyer = :buyer')
            ->andWhere("tc.createdAt < DATE_SUB(CURRENT_DATE(), :days, 'DAY')")
            ->setParameters(compact('buyer', 'days'));

        if($count){
            $builder->select('COUNT(tc.id)');
        }else{
            $builder->delete();
        }

        return $builder;
    }
}
