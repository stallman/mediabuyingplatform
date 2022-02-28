<?php

namespace App\Repository;

use App\Entity\Design;
use App\Entity\News;
use App\Entity\User;
use App\Entity\Visits;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

/**
 * @method Visits|null find($id, $lockMode = null, $lockVersion = null)
 * @method Visits|null findOneBy(array $criteria, array $orderBy = null)
 * @method Visits[]    findAll()
 * @method Visits[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VisitsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visits::class);
    }

    public function getVisitsByDate(Carbon $date)
    {
        $query = $this->createQueryBuilder('visits')
            ->where('visits.createdAt > :date')
            ->setParameter('date', $date)
            ->getQuery();

        return $query->getResult();
    }

    public function getVisitsByBuyer(User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('visits')
            ->where('visits.mediabuyer = :mediabuyer')
            ->setParameter('mediabuyer', $mediaBuyer)
            ->getQuery();

        return $query->getResult();
    }

    public function getVisitByUuid(UuidInterface $uuid)
    {
        $query = $this->createQueryBuilder('visits')
            ->where('visits.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    private function toSqlCN(string $dqlColumnName, bool $flip = false): string
    {
        $columns = [
            'source' => 'source_id',
            'campaign' => 'utm_campaign',
            'utmCampaign' => 'utm_campaign',
            'mediabuyer' => 'mediabuyer_id',
            'utmTerm' => 'utm_term',
            'utmContent' => 'utm_content',
            'teaser' => 'teaser_id',
            'news' => 'news_id',
            'createdAt' => 'created_at',
            'news_category' => 'news_category',
            'countryCode' => 'country_code',
            'trafficType' => 'traffic_type',
            'osWithVersion' => 'os_with_version',
            'browserWithVersion' => 'browser_with_version',
            'mobileBrand' => 'mobile_brand',
            'mobileModel' => 'mobile_model',
            'mobileOperator' => 'mobile_operator',
            'screenSize' => 'screen_size',
            'timesOfDay' => 'times_of_day',
            'dayOfWeek' => 'day_of_week',
        ];

        if ($flip) {
            $columns = array_flip($columns);
        }

        return isset($columns[$dqlColumnName]) ? $columns[$dqlColumnName] : $dqlColumnName;
    }

    private function replaceArrayColumns(array $columns, bool $fromKey = false, bool $flip = false)
    {
        $newArr = [];

        foreach ($columns as $key => $value) {
            if ($fromKey) {
                $newArr[$this->toSqlCN($key, $flip)] = $value;
            } else {
                $newArr[$key] = $this->toSqlCN($value, $flip);
            }
        }

        return $newArr;
    }

    public function getTrafficAnalysis(User $mediaBuyer,
                                       array $groupParams,
                                       ?\DateTime $from = null,
                                       ?\DateTime $to = null,
                                       array $filteringData = [],
                                       array $otherSettings = [],
                                       bool $getTopTeasers = false
    ) {

        $groupParams = $this->replaceArrayColumns($groupParams);
        $filteringData = $this->replaceArrayColumns($filteringData, true);

        $blackListGroup = $otherSettings['blackListParams'];
        $blackListIds = $otherSettings['dropTrafficByBl'];
        //dump($groupParams);
        //dump($filteringData);
        //dump($otherSettings);

        $selects = $groups = [];
        // skip blacked rows if not in groups
        $columnName = $this->toSqlCN($blackListGroup);
        if (!in_array($columnName, $groupParams)) {
            $blackListGroup = $blackListIds = '';
        }

        $selects = $groups = $joins = $wheres = [];
        foreach($groupParams as $i => $groupParam) {
            switch ($groupParam) {
                case 'created_at':
                    $selects[] = 'DATE(v.created_at) AS createdAt';
                    $groups[] = 'date(v.created_at)';
                    break;
                case 'news_id':
                    if ($getTopTeasers) {
                        $selects[] = "'Teasers' AS news";
                    } else {
                        $selects[] = 'v.news_id AS news';
                        $groups[] = "v.$groupParam";
                    }
                    break;
                case 'teaser_id':
                    $selects[] = 'tc.teaser_id AS teaser';
                    $groups[] = 'tc.teaser_id';
                    break;
                case 'news_category':
                    $selects[] = 'ncr.news_category_id as news_category';
                    $groups[] = 'ncr.news_category_id';
                    break;
                default:
                    $selects[] = "v.$groupParam";
                    $groups[] = "v.$groupParam";
            }
        }

        // costs requireds
        $selects[] = 'v.source_id AS c_source';
        $selects[] = 'v.utm_campaign AS c_campaign';
        $selects[] = 'date(v.created_at) AS c_date';
        $selects[] = 'v.ip AS c_ip';
        $groups[] = "v.source_id";
        $groups[] = "v.utm_campaign";
        $groups[] = "date(v.created_at)";
        $groups[] = "v.ip";

        $selects[] = 'COUNT(DISTINCT v.uuid) AS visits';
        //$selects[] = 'COUNT(DISTINCT v.ip) AS uniq_visits'; // todo group by and merge in front
        $selects[] = 'COUNT(DISTINCT tc.id) AS click_count';
        $selects[] = 'COUNT(DISTINCT c.id) AS total_leads';
        $selects[] = 'GROUP_CONCAT(DISTINCT c.id) AS lead_ids';
        $selects[] = 'SUM(c.status_id = 1) AS leads_approve_count';
        $selects[] = 'SUM(c.status_id = 2) AS leads_pending_count';
        $selects[] = 'SUM(c.status_id = 3) AS leads_declined_count';
        $selects[] = 'SUM(c.amount_leads) AS middle_lead';
        $selects[] = 'SUM(c.amount_approve_leads) AS real_income';

        $joins = [];
        if (in_array('news_category', $groupParams)) {
            $joins[] = 'LEFT JOIN news_categories_relations ncr ON (ncr.news_id = v.news_id)';
        }

        $joins[] = 'LEFT JOIN teasers_click tc ON (tc.uuid = v.uuid)';
        $joins[] = 'LEFT JOIN (
            SELECT id, teaser_click_id, status_id, amount_rub AS amount_leads,
                CASE WHEN status_id = 1 THEN amount_rub END AS amount_approve_leads
            FROM conversions
        ) c ON tc.id = c.teaser_click_id';

        $wheres = [];
        if (in_array('news_category', $groupParams)) {
            $joins[] = 'LEFT JOIN news_categories_relations ncr ON (ncr.news_id = v.news_id)';
        }

        if ($blackListGroup) {
            $columnName = $this->toSqlCN($blackListGroup);
            $prefix = $blackListGroup === 'teaser' ? 'tc' : 'v';
            $joins[] = "LEFT JOIN black_list bl ON (bl.group_name = '$blackListGroup' AND bl.group_id = $prefix.$columnName)";
        }

        $wheres[] = 'v.mediabuyer_id = ' . $mediaBuyer->getId();
        foreach ($filteringData as $fieldName => $data) {
            if ($fieldName == 'created_at') continue;

            if (false !== $nullValKey = array_search('NULL', $data)) {
                unset($data[$nullValKey]);

                $sql = '((v.' . $fieldName . ' IS NULL OR v.' . $fieldName . ' = "")';
                if ($data) {
                    $sql .= ' OR (v.' . $fieldName . ' IN (\'' . implode('\',\'', $data) . '\'))';
                }
                $sql .= ')';

                $wheres[] = $sql;

                continue;
            }

            $wheres[] = 'v.' . $fieldName . ' IN (\'' . implode('\',\'', $data) . '\')';
        }
        if ($from && $to) {
            $wheres[] = 'v.created_at BETWEEN \'' . $from->format('Y-m-d H:i:s') . '\' AND \'' . $to->format('Y-m-d H:i:s') . '\'';
        }

        if (in_array('news_id', $groupParams)) {
            if ($getTopTeasers) {
                $wheres[] = "tc.page_type = 'top'";
            } else {
                $wheres[] = "tc.page_type != 'top'";
            }
        }

        if ($blackListGroup) {
            $blackListIds = array_filter(explode(',', $blackListIds));

            if ($blackListIds) {
                $wheres[] = '(bl.group_id NOT IN (\'' . implode('\',\'', $blackListIds) . '\') OR bl.group_id IS NULL)';
            } else {
                $wheres[] = 'bl.group_id IS NULL';
            }
        }

        $select = implode(' , ', $selects);
        $join = implode(' ', $joins);
        $where = implode(' AND ', $wheres);
        $group = implode(' , ', $groups);
        $sql = "SELECT $select FROM visits v $join WHERE $where";
        if ($group) {
            $sql .= " GROUP BY $group";
        }

        //dump($sql);

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAllAssociative();

        foreach ($results as $key => $result) {
            $results[$key] = $this->replaceArrayColumns($result, true, true);
        }

        //dump($results);

        return $results;
    }

    public function utmCampaignList(User $mediaBuyer)
    {
        $query = $this->createQueryBuilder('visits')
            ->select('visits.utmCampaign')
            ->where('visits.mediabuyer = :mediabuyer')
            ->andWhere('visits.utmCampaign IS NOT NULL')
            ->setParameters([
                'mediabuyer' => $mediaBuyer,
            ])
            ->distinct('visits.utmCampaign')
        ;

        $campaigns = [];
        foreach ($query->getQuery()->getResult() as $item) {
            $campaigns[] = $item['utmCampaign'];
        }

        return array_filter($campaigns);
    }

    public function getUniqueVisitsCount(User $mediabuyer, array $uuids)
    {
        $query = $this->createQueryBuilder('v')
            ->select('v.uuid')
            ->where('v.uuid IN (:uuids)')
            ->andWhere('v.mediabuyer = :mediabuyer')
            ->setParameters([
                'uuids' => $uuids,
                'mediabuyer' => $mediabuyer,
            ])
            ->groupBy('v.ip')
            ->getQuery();

        return $query->getResult();
    }

    public function getUniqueVisitsCountNews(News $news, ?User $mediabuyer = null)
    {
        $query = $this->createQueryBuilder('v')
            ->select('v.uuid')
            ->where('v.news = :news')
        ;

        $parameters['news'] = $news;

        if (null !== $mediabuyer) {
            $query->andWhere('v.mediabuyer = :mediabuyer');
            $parameters['mediabuyer'] = $mediabuyer;
        }

        $query->setParameters($parameters)
            ->groupBy('v.ip');

        $res = $query->getQuery()->getResult();

        return count($res);
    }

    public function getUniqueVisitsCountDesign(User $mediabuyer, Design $design)
    {
        $query = $this->createQueryBuilder('v')
            ->select('v.uuid')
            ->where('v.mediabuyer = :mediabuyer')
            ->andWhere('v.design = :design')
            ->setParameters([
                'mediabuyer' => $mediabuyer,
                'design' => $design,
            ])
            ->groupBy('v.ip')
        ;

        $uuids = $query->getQuery()->getResult();

        return count($uuids);
    }
}

