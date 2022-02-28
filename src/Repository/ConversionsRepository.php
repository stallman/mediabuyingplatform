<?php

namespace App\Repository;

use App\Contract\CleanableRepositoryInterface;
use App\Entity\Algorithm;
use App\Entity\Conversions;
use App\Entity\ConversionStatus;
use App\Entity\Design;
use App\Entity\Country;
use App\Entity\Teaser;
use App\Entity\TeasersClick;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Conversions|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conversions|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conversions[]    findAll()
 * @method Conversions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConversionsRepository extends ServiceEntityRepository implements CleanableRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversions::class);
    }

    public function getUnDeletedConversionsList($length = 20, $start = 0)
    {
        $query = $this->createQueryBuilder('conversions')
            ->andWhere('conversions.is_deleted = :is_deleted')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->setParameters([
                'is_deleted' => false
            ])
            ->orderBy('conversions.createdAt','DESC')
            ->getQuery();

        return $query->getResult();
    }

    public function getUnDeletedConversionsListByDate($from, $to, $length = 20, $start = 0)
    {
        $query = $this->createQueryBuilder('conversions')
            ->where('conversions.is_deleted = :is_deleted')
            ->andWhere('conversions.createdAt BETWEEN :from AND :to')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->setParameters([
                'is_deleted' => false,
                'from' => $from,
                'to' => $to
            ])
            ->orderBy('conversions.createdAt','DESC')
            ->getQuery();

        return $query->getResult();
    }

    public function getUnDeletedConversionsCount()
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('count(conversions.id) as count')
            ->where('conversions.is_deleted = :is_deleted')
            ->setParameters([
                'is_deleted' => false,
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getUnDeletedConversionsCountByDate($from, $to)
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('count(conversions.id) as count')
            ->where('conversions.is_deleted = :is_deleted')
            ->andWhere('conversions.createdAt BETWEEN :from AND :to')
            ->setParameters([
                'is_deleted' => false,
                'from' => $from,
                'to' => $to
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getMediaBuyerConversionsList(User $user, $length = 20, $start = 0, $search = null)
    {
        $query = $this->createQueryBuilder('conversions')
            ->where('conversions.mediabuyer = :user')
            ->andWhere('conversions.is_deleted = :is_deleted')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->setParameters([
                'user' => $user->getId(),
                'is_deleted' => false
            ])
            ->orderBy('conversions.createdAt','DESC');

        return $query->getQuery()->getResult();
    }

    public function getMediaBuyerConversionsCount(User $user)
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('count(conversions.id) as count')
            ->where('conversions.mediabuyer = :user')
            ->andWhere('conversions.is_deleted = :is_deleted')
            ->setParameters([
                'user' => $user->getId(),
                'is_deleted' => false
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getMediaBuyerConversionsCountUuid(User $user, $uuid)
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('count(conversions.id) as count')
            ->where('conversions.mediabuyer = :user')
            ->andWhere('conversions.is_deleted = :is_deleted')
            ->andWhere('conversions.uuid = :uuid')
            ->setParameters([
                                'user' => $user->getId(),
                                'is_deleted' => false,
                                'uuid' => $uuid,
                            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }


    public function getMediaBuyerConversionsCountUuidArr(User $user, array $uuid)
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('count(conversions.id) as count')
            ->where('conversions.mediabuyer = :user')
            ->andWhere('conversions.is_deleted = :is_deleted')
            ->andWhere('conversions.uuid IN (:uuid)')
            ->setParameters([
                                'user' => $user->getId(),
                                'is_deleted' => false,
                                'uuid' => $uuid,
                            ])
            ->getQuery();

        return $query->getResult();
    }

    public function getMediaBuyerConversionsCountByDate(User $user, $from, $to)
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('count(conversions.id) as count')
            ->where('conversions.mediabuyer = :user')
            ->andWhere('conversions.is_deleted = :is_deleted')
            ->andWhere('conversions.createdAt BETWEEN :from AND :to')
            ->setParameters([
                'user' => $user->getId(),
                'is_deleted' => false,
                'from' => $from,
                'to' => $to
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getMediaBuyerConversionsListByDate(User $user, $length = 20, $start = 0, $from, $to)
    {
        $query = $this->createQueryBuilder('conversions')
            ->where('conversions.mediabuyer = :user')
            ->andWhere('conversions.is_deleted = :is_deleted')
            ->andWhere('conversions.createdAt BETWEEN :from AND :to')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->setParameters([
                'user' => $user->getId(),
                'is_deleted' => false,
                'from' => $from,
                'to' => $to
            ])
            ->orderBy('conversions.createdAt','DESC')
            ->getQuery();

        return $query->getResult();
    }

    public function getMediaBuyerConversionsCountByDesign(User $user, Design $design)
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('count(conversions.id) as count')
            ->where('conversions.mediabuyer = :user')
            ->andWhere('conversions.design = :design')
            ->andWhere('conversions.is_deleted = :is_deleted')
            ->setParameters([
                'user' => $user,
                'design' => $design,
                'is_deleted' => false,
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getMediaBuyerConversionsCountByAlgorithm(User $user, Algorithm $algorithm)
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('count(conversions.id) as count')
            ->where('conversions.mediabuyer = :user')
            ->andWhere('conversions.algorithm = :algorithm')
            ->andWhere('conversions.is_deleted = :is_deleted')
            ->setParameters([
                'user' => $user,
                'algorithm' => $algorithm,
                'is_deleted' => false,
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getConversionsCountByTeasersClick(array $teasersClickIdList)
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('count(conversions.id) as count')
            ->where("conversions.teaserClick IN(:teasers_click)")
            ->andWhere('conversions.is_deleted = :is_deleted')
            ->setParameters([
                'teasers_click' => $teasersClickIdList,
                'is_deleted' => false
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getMediaBuyerApproveConversionsCountByDesign(User $user, Design $design)
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('count(conversions.id) as count')
            ->leftJoin('conversions.status', 'status')
            ->where('conversions.mediabuyer = :user')
            ->andWhere('conversions.design = :design')
            ->andWhere('status.label_en = :status')
            ->andWhere('conversions.is_deleted = :is_deleted')
            ->setParameters([
                'user' => $user,
                'design' => $design,
                'status' => 'approved',
                'is_deleted' => false,
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getMediaBuyerApproveConversionsCountByAlgorithm(User $user, Algorithm $algorithm)
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('count(conversions.id) as count')
            ->leftJoin('conversions.status', 'status')
            ->where('conversions.mediabuyer = :user')
            ->andWhere('conversions.algorithm = :algorithm')
            ->andWhere('status.label_en = :status')
            ->andWhere('conversions.is_deleted = :is_deleted')
            ->setParameters([
                'user' => $user,
                'algorithm' => $algorithm,
                'status' => 'approved',
                'is_deleted' => false,
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getConversionsCountByTeasersClickAndStatus(array $teasersClickIdList, string $status)
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('count(conversions.id) as count')
            ->where("conversions.teaserClick IN(:teasers_click)")
            ->andWhere('conversions.is_deleted = :is_deleted')
            ->andWhere('conversions.status = :status')
            ->setParameters([
                'teasers_click' => $teasersClickIdList,
                'is_deleted' => false,
                'status' => $status,
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    public function getIncomeRub(TeasersClick $click, Country $country, User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('SUM(conversions.amountRub) as sum')
            ->where('conversions.mediabuyer = :user')
            ->andWhere('conversions.country = :country')
            ->andWhere('conversions.teaserClick = :teaserClick')
            ->andWhere('conversions.is_deleted = :is_deleted')
            ->setParameters([
                'user' => $mediaBuyer,
                'country' => $country,
                'teaserClick' => $click,
                'is_deleted' => 0,
            ])
            ->getQuery();

        return $query->getOneOrNullResult()['sum'];
    }

    /**
     * @param int $teaserId
     * @param bool $countApproved
     * @return array|int|string
     */
    public function countConversionsByTeaserId(int $teaserId, bool $countApproved = false)
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('conversions.id')
            ->leftJoin(TeasersClick::class, 'teaser_click', 'WITH', 'teaser_click.id = conversions.teaserClick')
            ->leftJoin(Teaser::class, 'teaser', 'WITH', 'teaser_click.teaser = teaser.id')
            ->where('teaser.id = :teaserId')
            ->setParameter('teaserId', $teaserId);

        if ($countApproved) {
            $query
                ->andWhere('conversions.status = :status')
                ->setParameter('status', 200)
                ;
        }

        $query->getQuery();

        return $query->getQuery()->getScalarResult();
    }

    /**
     * @param int $teaserId
     * @return array|int|string
     */
    public function countApprovedConversionsId(int $teaserId)
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('conversions.id')
            ->leftJoin(ConversionStatus::class, 'conversion_status', 'WITH', 'conversions.status = conversion_status.id')
            ->leftJoin(TeasersClick::class, 'teaser_click', 'WITH', 'teaser_click.id = conversions.teaserClick')
            ->leftJoin(Teaser::class, 'teaser', 'WITH', 'teaser_click.teaser = teaser.id')
            ->where('conversion_status.code = :code')
            ->andWhere('teaser.id = :teaserId')
            ->setParameter('code', 200)
            ->setParameter('teaserId', $teaserId)
            ->getQuery()
        ;

        return $query->getResult();
    }

    public function getAmountIncomeByTeaser(int $teaserId)
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('sum(conversions.amountRub) as amount')
            ->leftJoin('conversions.status', 'status')
            ->leftJoin(TeasersClick::class, 'teaser_click', 'WITH', 'teaser_click.id = conversions.teaserClick')
            ->leftJoin(Teaser::class, 'teaser', 'WITH', 'teaser_click.teaser = teaser.id')
            ->where('teaser.id = :teaser')
            ->andWhere('status.label_en = :status')
            ->andWhere('conversions.is_deleted = :is_deleted')
            ->setParameters([
                'teaser' => $teaserId,
                'status' => 'approved',
                'is_deleted' => false,
            ])
            ->getQuery();

        return $query->getSingleResult()['amount'];
    }

    public function getAmountIncomeByDesign(User $user, Design $design)
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('sum(conversions.amountRub) as amount')
            ->leftJoin('conversions.status', 'status')
            ->where('conversions.mediabuyer = :user')
            ->andWhere('conversions.design = :design')
            ->andWhere('status.label_en = :status')
            ->andWhere('conversions.is_deleted = :is_deleted')
            ->setParameters([
                'user' => $user,
                'design' => $design,
                'status' => 'approved',
                'is_deleted' => false,
            ])
            ->getQuery();

        return $query->getSingleResult()['amount'];
    }

    public function getAmountIncomeByAlgorithm(User $user, Algorithm $algorithm)
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('sum(conversions.amountRub) as amount')
            ->leftJoin('conversions.status', 'status')
            ->where('conversions.mediabuyer = :user')
            ->andWhere('conversions.algorithm = :algorithm')
            ->andWhere('status.label_en = :status')
            ->andWhere('conversions.is_deleted = :is_deleted')
            ->setParameters([
                'user' => $user,
                'algorithm' => $algorithm,
                'status' => 'approved',
                'is_deleted' => false,
            ])
            ->getQuery();

        return $query->getSingleResult()['amount'];
    }

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function getTotalLeadsCountArr(User $mediaBuyer, array $uuids, array $sources, \DateTime $from, \DateTime $to, ?string $statusLabel = '', ?string $createdAt = ''): int
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('conversions.id as id, date(conversions.createdAt) as ymd');

        if ($statusLabel) {
            $query->leftJoin(ConversionStatus::class, 'status', 'WITH', 'conversions.status = status.id');
        }

        $criteria = Criteria::create();
        $expr = Criteria::expr();

        $criteria->where($expr->eq('is_deleted', false))
            ->andWhere($expr->eq('mediabuyer', $mediaBuyer))
            ->andWhere($expr->in('uuid', $uuids))
            ->andWhere($expr->gte('createdAt', $from))
            ->andWhere($expr->lte('createdAt', $to))
        ;

        if ($statusLabel) {
            $criteria->andWhere($expr->eq('status.label_en', $statusLabel));
        }

        if ($createdAt) {
            $criteria->andWhere($expr->contains('conversions.createdAt', $createdAt));
        }

        $exprCommon = $criteria->getWhereExpression();

        $criteria->andWhere($expr->in('conversions.source', $sources));

        if (in_array(null, $sources)) {
            $criteria->orWhere($expr->andX(
                $exprCommon,
                $expr->isNull('conversions.source')
            ));
        }

        $query->addCriteria($criteria);

        $results = $query->getQuery()->getResult();

        return count($results);
    }

    public function getAmountIncomeArr(User $user, array $uuid, string $status = '')
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('sum(conversions.amountRub) as amount');

        if ($status) {
            $query->leftJoin('conversions.status', 'status');
        }

        $query->where('conversions.mediabuyer = :user')
            ->andWhere('conversions.uuid IN (:uuid)')
            ->andWhere('conversions.is_deleted = :is_deleted');

        $parameters = [
            'user' => $user,
            'uuid' => $uuid,
            'is_deleted' => false,
        ];

        if ($status) {
            $query->andWhere('status.label_en = :status');
            $parameters['status'] = $status;
        }

        $query->setParameters($parameters);

        return $query->getQuery()->getResult();
    }

    public function getMediaBuyerConversionsByUuidArr(User $mediaBuyer, array $uuids, array $sources, \DateTime $from, \DateTime $to)
    {
        $query = $this->createQueryBuilder('conversions')
            ->where('conversions.is_deleted = :is_deleted')
            ->andWhere('conversions.mediabuyer = :mediabuyer')
            ->andWhere('conversions.uuid IN (:uuids)')
            ->andWhere('conversions.source IN (:source)')
            ->andWhere('conversions.createdAt BETWEEN :from AND :to')
            ->setParameters([
                'is_deleted' => false,
                'mediabuyer' => $mediaBuyer,
                'uuids' => $uuids,
                'source' => $sources,
                'from' => $from->format('Y-m-d H:i:s'),
                'to' => $to->format('Y-m-d H:i:s'),
            ])
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param string $pageType short|full|top
     */
    public function countConversionsByPageType(User $mediabuyer = null, string $pageType = 'short'): int
    {
        $query = $this->createQueryBuilder('conversions')
            ->select('count(conversions.id) as count')
            ->leftJoin(TeasersClick::class, 'teaser_click', 'WITH', 'teaser_click.id = conversions.teaserClick')
            ->andWhere('teaser_click.pageType = :pageType')
            ->andWhere('conversions.is_deleted = :is_deleted')
        ;

        $parameters = [
            'pageType' => $pageType,
            'is_deleted' => false,
        ];

        if (null !== $mediabuyer) {
            $query->andWhere('conversions.mediabuyer = :mediabuyer');
            $parameters['mediabuyer'] = $mediabuyer;
        }

        $query->setParameters($parameters);

        return $query->getQuery()->getSingleResult()['count'];
    }

    public function getAmountRubByIds(string $leadIds)
    {
        $rows = [];

        if (!empty($leadIds)) {
            $conn = $this->getEntityManager()->getConnection();

            $stmt = $conn->executeQuery("SELECT id, amount_rub FROM conversions c WHERE id IN ($leadIds) GROUP BY c.id");

            $rows =  $stmt->fetchAllAssociative();
        }

        return $rows;
    }

    public function queryOlderThan(User $mediabuyer, int $days, bool $count = false) : QueryBuilder {
        $builder = $this->createQueryBuilder('c')
            ->where('c.mediabuyer = :mediabuyer')
            ->andWhere("c.updatedAt < DATE_SUB(CURRENT_DATE(), :days, 'DAY')")
            ->setParameters(compact('mediabuyer', 'days'));

        if($count){
            $builder->select('COUNT(c.id)');
        }else{
            $builder->delete();
        }

        return $builder;
    }
}
