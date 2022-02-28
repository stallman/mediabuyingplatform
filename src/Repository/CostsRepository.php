<?php

namespace App\Repository;

use App\Contract\CleanableRepositoryInterface;
use App\Entity\Costs;
use App\Entity\News;
use App\Entity\Sources;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Costs|null find($id, $lockMode = null, $lockVersion = null)
 * @method Costs|null findOneBy(array $criteria, array $orderBy = null)
 * @method Costs[]    findAll()
 * @method Costs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CostsRepository extends ServiceEntityRepository implements CleanableRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Costs::class);
    }

    public function getCostsCount(User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('costs')
            ->select('count(costs.id) as count')
            ->where('costs.mediabuyer = :mediabuyer')
            ->setParameters([
                'mediabuyer' => $mediaBuyer
            ])
            ->getQuery();

        return $query->getSingleResult()['count'];
    }

    /**
     * @param User $mediaBuyer
     * @param int $length
     * @param int $start
     * @param array $order
     * @return
     */
    public function getCostsPaginateList(string $candidate, User $mediaBuyer, $length = 20, $start = 0, array $order)
    {
        $query = $this->createQueryBuilder('costs')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->where('costs.mediabuyer = :mediabuyer')
            ->setParameters([
                'mediabuyer' => $mediaBuyer
            ]);

        switch ($order[0]['column']) {
            case 'date':
                $query = $this->orderByDate($query, $order[0]['dir']);
                break;
            case 'source':
                $query = $this->orderBySource($query, $order[0]['dir']);
                break;
            case 'news':
                $query = $this->orderByNews($query, $order[0]['dir']);
                break;
            case 'campaign':
                $query = $this->orderByCampaign($query, $order[0]['dir']);
                break;
            default:
                $query = $this->orderByDate($query);
                break;
        }
        $query->addOrderBy('costs.id', 'DESC');

        return $query->getQuery()->getResult();
    }

    public function orderByDate($query, $order = "DESC")
    {
        return $query->leftJoin('costs.source', 'source')
            ->leftJoin('costs.news', 'news')
            ->addOrderBy("costs.date", $order)
            //->addOrderBy("source.title", "ASC")
            //->addOrderBy("costs.campaign", "ASC")
            //->addOrderBy("news.title", "ASC")
        ;
    }

    public function orderBySource($query, $order)
    {
        return $query ->leftJoin('costs.source', 'source')
            ->leftJoin('costs.news', 'news')
            ->addOrderBy("source.title", $order)
            ->addOrderBy("costs.date", 'DESC')
            //->addOrderBy("costs.campaign", "ASC")
            //->addOrderBy("news.title", "ASC")
        ;
    }

    public function orderByNews($query, $order)
    {
        return $query->leftJoin('costs.news', 'news')
            ->leftJoin('costs.source', 'source')
            ->addOrderBy("news.title", $order)
            ->addOrderBy("costs.date", 'DESC')
            //->addOrderBy("source.title", "ASC")
            //->addOrderBy("costs.campaign", "ASC")
        ;
    }

    public function orderByCampaign($query, $order)
    {
        return $query->leftJoin('costs.source', 'source')
            ->addOrderBy("costs.campaign", $order)
            ->addOrderBy("costs.date", 'DESC')
            //->addOrderBy("source.title", "ASC")
        ;
    }

    public function getAmountCost(User $user)
    {
        $query = $this->createQueryBuilder('costs')
            ->select('sum(costs.costRub) as amount')
            ->where('costs.mediabuyer = :user')
            ->setParameters([
                'user' => $user
            ])
            ->getQuery();

        return $query->getSingleResult()['amount'];
    }

    public function getTrafficAnalysisCosts(User $mediaBuyer, ?string $source, ?string $campaign, \DateTime $from, \DateTime $to)
    {
        $query = $this->createQueryBuilder('costs')
            ->select('sum(costs.costRub) as sum')
            ->where('costs.mediabuyer = :mediabuyer')
            ->andWhere('costs.source = :source')
            ->andWhere('costs.campaign = :campaign')
            ->andWhere('costs.date BETWEEN :from AND :to')
            ->setParameters([
                'mediabuyer' => $mediaBuyer,
                'source' => $source,
                'campaign' => $campaign,
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ])
            ->getQuery();

        return $query->getSingleResult()['sum'];
    }

    public function getTrafficAnalysisCostsArr(User $mediaBuyer, array $sources, array $campaigns, \DateTime $from, \DateTime $to)
    {
        $query = $this->createQueryBuilder('costs')
            ->where('costs.mediabuyer = :mediabuyer')
            ->andWhere('costs.source IN (:sources)')
            ->andWhere('costs.campaign IN (:campaigns)')
            ->andWhere('costs.date BETWEEN :from AND :to')
        ;

        $parameters = [
            'mediabuyer' => $mediaBuyer,
            'sources' => $sources,
            'campaigns' => $campaigns,
            'from' => $from->format('Y-m-d H:i:s'),
            'to' => $to->format('Y-m-d H:i:s'),
        ];

        $query->setParameters($parameters);

        return $query->getQuery()->getResult();
    }

    public function getCostsByIsFinal(bool $isFinal)
    {
        $query = $this->createQueryBuilder('costs')
            ->where('costs.isFinal = :isFinal')
            ->orWhere('costs.cost = :zero')
            ->setParameters([
                'isFinal' => $isFinal,
                'zero' => 0.0000,
            ])
            ->getQuery();

        return $query->getResult();
    }

    public function queryOlderThan(User $mediabuyer, int $days, bool $count = false) : QueryBuilder {
        $builder = $this->createQueryBuilder('c')
            ->where('c.mediabuyer = :mediabuyer')
            ->andWhere("c.date < DATE_SUB(CURRENT_DATE(), :days, 'DAY')")
            ->setParameters(compact('mediabuyer', 'days'));

        if($count){
            $builder->select('COUNT(c.id)');
        }else{
            $builder->delete();
        }

        return $builder;
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getPreparedUserCosts(User $mediaBuyer): array
    {
        $id = $mediaBuyer->getId();
        $conn = $this->getEntityManager()->getConnection();

        $sql = <<<SQL
            SELECT source_id AS source,campaign,date,SUM(cost_rub) AS cost 
            FROM costs WHERE mediabuyer_id = $id
            GROUP BY source_id,campaign,date
        SQL;

        $stmt = $conn->executeQuery($sql);
        return $stmt->fetchAllAssociative();
    }
}
