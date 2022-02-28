<?php


namespace App\Traits\Dashboard;

use App\Entity\Conversions;
use App\Entity\Costs;
use App\Entity\User;
use App\Entity\ConversionStatus;
use PhpParser\Node\Stmt\Break_;

trait NewsFinanceTrait
{
    public function getNewsFinanceTableHeader($ajaxUrl)
    {
        return [
            [
                'label' => 'Источник',
                'pagingServerSide' => true,
                'ajaxUrl' => $ajaxUrl,
                'searching' => false,
                'sortable' => false
            ],
            [
                'label' => 'Новость',
                'sortable' => false
            ],
            [
                'label' => 'Подтвержденные лиды, количество',
                'sortable' => false
            ],
            [
                'label' => 'Подтвержденные лиды, %',
                'sortable' => false
            ],
            [
                'label' => 'Лиды холд, количество',
                'sortable' => false
            ],
            [
                'label' => 'Лиды холд, %',
                'sortable' => false
            ],
            [
                'label' => 'Отмененные лиды, количество',
                'sortable' => false
            ],
            [
                'label' => 'Отмененные лиды, %',
                'sortable' => false
            ],
            [
                'label' => 'Расход',
                'sortable' => false
            ],
            [
                'label' => 'Доход реальный',
                'sortable' => false
            ],
            [
                'label' => 'Доход ожидаемый',
                'sortable' => false
            ],
            [
                'label' => 'Прибыль реальная',
                'sortable' => false
            ],
            [
                'label' => 'Прибыль прогнозируемая',
                'sortable' => false
            ],
            [
                'label' => 'ROI реальный, %',
                'sortable' => false
            ],
            [
                'label' => 'ROI прогнозируемое, %',
                'sortable' => false
            ]
        ];
    }

    public function getDataJson(array $data)
    {
        $dataJson = [];

        /** @var array $dataItem */
        foreach($data as $dataItem) {
            $dataJson[] = [
                $dataItem['s_title'],
                $dataItem['n_id'] . '|' . $dataItem['n_title'],
                round($dataItem['approved_conversion']),
                round($dataItem['approved_conversion_percent']),
                round($dataItem['declined_conversion']),
                round($dataItem['declined_conversion_percent']),
                round($dataItem['pending_conversion']),
                round($dataItem['pending_conversion_percent']),
                round($dataItem['costs']),
                round($dataItem['revenue_real']),
                round($dataItem['revenue_forecasted']),
                round($this->calculateStatistic->calculateProfit($dataItem['revenue_real'], $dataItem['costs'])),
                round($dataItem['profit_forecasted']),
                round($this->calculateStatistic->calculateROI($dataItem['revenue_real'], $dataItem['costs'])),
                round($dataItem['roi_forecasted']),
            ];
        }

        return $dataJson;
    }

    public function getNewsFinance(User $mediaBuyer, $length = 20, $start = 0, $request)
    {
        $dql = "SELECT n.id as n_id, n.title as n_title, s.id as s_id, s.title as s_title FROM App\Entity\News n, App\Entity\Sources s
            WHERE n.isActive = :isActive 
            AND n.is_deleted = :isDeleted  
            AND n.user = :user  
            AND s.user = :user 
            AND s.is_deleted = :isDeleted
        ";
        
        [$dql, $params] = $this->getParams($dql, $mediaBuyer, $request);

        $dql .= "ORDER BY n.id DESC, s.id DESC";
        $newsFinanceRow = $this->entityManager
            ->createQuery($dql)
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->setParameters($params)
            ->getResult();

        $newsFinanceRow = $this->distributeLeadsCount($newsFinanceRow, $mediaBuyer, $request, 'approved');
        $newsFinanceRow = $this->distributeLeadsCount($newsFinanceRow, $mediaBuyer, $request, 'declined');
        $newsFinanceRow = $this->distributeLeadsCount($newsFinanceRow, $mediaBuyer, $request, 'pending');
        $newsFinanceRow = $this->distributeCosts($newsFinanceRow, $mediaBuyer, $request);
        $newsFinanceRow = $this->distributeRevenue($newsFinanceRow, $mediaBuyer, $request);
        $newsFinanceRow = $this->distributeRevenueForecasted($newsFinanceRow, $mediaBuyer, $request);
        $newsFinanceRow = $this->distributeProfitForecasted($newsFinanceRow, $mediaBuyer, $request);
        $newsFinanceRow = $this->distributeROIForecasted($newsFinanceRow, $mediaBuyer, $request);

        return $newsFinanceRow;
    }

    private function getParams($dql, $mediaBuyer, $request)
    {
        $params = [
            'isActive' => 1,
            'user' => $mediaBuyer,
            'isDeleted' => 0,
        ];

        $reportSettingsSources = $request->query->has('reportSettingsSources') ? $request->query->get('reportSettingsSources') : null;
        $reportSettingsNews = $request->query->has('reportSettingsNews') ? $request->query->get('reportSettingsNews') : null;

        if(!is_null($reportSettingsSources)) {
            $dql .= "AND s.id IN(:source_ids)";
            if(is_array($reportSettingsSources)){
                $params['source_ids'] = $reportSettingsSources;
            } else {
                $params['source_ids'] = [$reportSettingsSources];
            }
        }
        if(!is_null($reportSettingsNews)) {
            $dql .= "AND n.id IN(:news_ids)";
            if(is_array($reportSettingsNews)){
                $params['news_ids'] = $reportSettingsNews;
            } else {
                $params['news_ids'] = [$reportSettingsNews];
            }
        }

        return [$dql, $params];
    }

    private function distributeLeadsCount($newsFinanceRow, $mediaBuyer, $request, $statusName)
    {
        $sources = [];   
        $news = [];
        foreach ($newsFinanceRow as $newsFinanceRowItem) {
            $sources[] = $newsFinanceRowItem['s_id'];
            $news[] = $newsFinanceRowItem['n_id'];
        }

        $sql = $this->buildConversionsCountSql($sources, $news, $mediaBuyer, $request, $statusName);
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();
        $conversions = $stmt->fetchAll();
        
        foreach ($newsFinanceRow as $i => $newsFinanceRowItem) {
            $newsFinanceRow[$i][$statusName . '_conversion'] = 0;
            $newsFinanceRow[$i][$statusName . '_conversion_percent'] = 0;
            foreach ($conversions as $conversionsItem) {
                if ($newsFinanceRowItem['n_id'] == $conversionsItem['news_id'] && $newsFinanceRowItem['s_id'] == $conversionsItem['source_id']) {
                    $newsFinanceRow[$i][$statusName . '_conversion'] = $conversionsItem['conv_count'];
                    $newsFinanceRow[$i][$statusName . '_conversion_percent'] = $this->calculateApproveConversionsPersent($conversionsItem['conv_count'], $mediaBuyer);
                    break;
                }
            }
        }

        return $newsFinanceRow;
    }

    private function distributeCosts($newsFinanceRow, $mediaBuyer, $request)
    {
        $sql = $this->buildCostsSql($newsFinanceRow, $mediaBuyer, $request);
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();
        $costs = $stmt->fetchAll();

        foreach ($newsFinanceRow as $i => $item) {
            $newsFinanceRow[$i]['costs'] = 0;
            foreach ($costs as $cost) {
                if ($cost['news_id'] == $item['n_id'] && $cost['source_id'] == $item['s_id']) {
                    $newsFinanceRow[$i]['costs'] = $cost['cost_rub'];
                    break;
                }
            }
        }

        return $newsFinanceRow;
    }

    private function calculateApproveConversionsPersent($approvedCountForRow, $mediaBuyer)
    {
        $query = $this->entityManager->createQueryBuilder('c')
            ->select('count(c.id) as all_conv_count')
            ->from(Conversions::class, 'c')
            ->where('c.mediabuyer = :mediabuyer')
            ->setParameters([
                'mediabuyer' => $mediaBuyer
            ])
            ->getQuery();
        
        $allConversionsCount = $query->getSingleScalarResult();

        if ($allConversionsCount > 0) {
            return $approvedCountForRow / $allConversionsCount * 100;
        }

        return 0;
    }

    private function buildConversionsCountSql($sources, $news, $mediaBuyer, $request, $statusName)
    {
        $status = $this->entityManager->getRepository(ConversionStatus::class)
            ->findOneBy(['label_en' => $statusName]);

        $sql = "SELECT count(c.id) as conv_count, c.news_id, c.source_id
            FROM conversions as c
            WHERE c.mediabuyer_id = " . $mediaBuyer->getId();

        $sql = $this->addNewsAndSourcesArrsToSql($sql, $sources, $news);
            
        $sql .= " AND c.status_id = '" . $status->getId() . "'";

        $sql = $this->addDateRangeFilterToSql($request, $sql, "created_at");
              
        $sql .= " GROUP BY c.news_id, c.source_id;";

        return $sql;
    }

    private function buildCostsSql($newsFinanceRow, $mediaBuyer, $request)
    {
        $sources = [];
        $news = [];
        foreach ($newsFinanceRow as $newsFinanceRowItem) {
            $sources[] = $newsFinanceRowItem['s_id'];
            $news[] = $newsFinanceRowItem['n_id'];
        }

        $sql = "SELECT c.cost_rub, c.news_id, c.source_id
            FROM costs as c
            WHERE c.mediabuyer_id = " . $mediaBuyer->getId();
        
        $sql = $this->addNewsAndSourcesArrsToSql($sql, $sources, $news);

        $sql = $this->addDateRangeFilterToSql($request, $sql, "date_set_data");
              
        $sql .= " GROUP BY c.news_id, c.source_id;";

        return $sql;
    }

    public function getNewsFinanceCount(User $mediaBuyer, $request)
    {
        $dql = "SELECT COUNT(n.id) as count FROM App\Entity\News n, App\Entity\Sources s
            WHERE n.isActive = :isActive 
            AND n.is_deleted = :isDeleted  
            AND n.user = :user  
            AND s.user = :user 
            AND s.is_deleted = :isDeleted  
            ";
        [$dql, $params] = $this->getParams($dql, $mediaBuyer, $request);

        return $this->entityManager
            ->createQuery($dql)
            ->setParameters($params)
            ->getOneOrNullResult()['count'];
    }

    public function distributeRevenue($newsFinanceRow, $mediaBuyer, $request)
    {
        $sources = [];   
        $news = [];
        foreach ($newsFinanceRow as $newsFinanceRowItem) {
            $sources[] = $newsFinanceRowItem['s_id'];
            $news[] = $newsFinanceRowItem['n_id'];
        }

        $sql = $this->buildRevenueSql($mediaBuyer, $news, $sources, $request);
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();
        $conversions = $stmt->fetchAll();

        foreach ($newsFinanceRow as $i => $item) {
            $newsFinanceRow[$i]['revenue_real'] = 0;
            foreach ($conversions as $conversion) {
                if ($conversion['news_id'] == $item['n_id'] && $conversion['source_id'] == $item['s_id']) {
                    $newsFinanceRow[$i]['revenue'] = $conversion['revenue_real'];
                    break;
                }
            }
        }

        return $newsFinanceRow;
    }

    public function buildRevenueSql($mediaBuyer, $news, $sources, $request)
    {
        $status = $this->entityManager->getRepository(ConversionStatus::class)
            ->findOneBy(['label_en' => 'approved']);

        $sql = "SELECT news_id, source_id, SUM(`amount_rub`) as revenue_real FROM conversions as c
            WHERE mediabuyer_id = " . $mediaBuyer->getId();

        $sql = $this->addNewsAndSourcesArrsToSql($sql, $sources, $news);
        
        $sql .= " AND c.status_id = '" . $status->getId() . "'";
        
        $sql = $this->addDateRangeFilterToSql($request, $sql, "created_at");
                  
        $sql .= " GROUP BY c.news_id, c.source_id;";

        return $sql;
    }

    private function distributeRevenueForecasted($newsFinanceRow, $mediaBuyer, $request)
    {
        $sources = [];   
        $news = [];
        foreach ($newsFinanceRow as $newsFinanceRowItem) {
            $sources[] = $newsFinanceRowItem['s_id'];
            $news[] = $newsFinanceRowItem['n_id'];
        }
        
        $sql = $this->buildRevenueForecastedSql($mediaBuyer, $news, $sources, $request);
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();
        $conversions = $stmt->fetchAll();

        foreach ($newsFinanceRow as $i => $item) {
            $newsFinanceRow[$i]['revenue_forecasted'] = 0;
            foreach ($conversions as $conversion) {
                if ($conversion['news_id'] == $item['n_id'] && $conversion['source_id'] == $item['s_id']) {
                    $newsFinanceRow[$i]['revenue_forecasted'] = $conversion['revenue_forecasted'];
                    break;
                }
            }
        }

        return $newsFinanceRow;
    }

    private function buildRevenueForecastedSql($mediaBuyer, $news, $sources, $request)
    {
        $sql = "SELECT c.news_id, c.source_id, sum(c.amount_rub * tss.approve_average_percentage) as revenue_forecasted
            FROM conversions as c 
            LEFT JOIN teasers_sub_group_settings as tss ON c.subgroup_id = tss.teasers_sub_group_id 
            WHERE mediabuyer_id = " . $mediaBuyer->getId();
        
        $sql = $this->addNewsAndSourcesArrsToSql($sql, $sources, $news);
        
        $sql = $this->addDateRangeFilterToSql($request, $sql, "created_at");
                
        $sql .= " GROUP BY c.news_id, c.source_id;";

        return $sql;  
    }

    private function distributeProfitForecasted($newsFinanceRow, $mediaBuyer, $request)
    {
        $sources = [];   
        $news = [];
        foreach ($newsFinanceRow as $newsFinanceRowItem) {
            $sources[] = $newsFinanceRowItem['s_id'];
            $news[] = $newsFinanceRowItem['n_id'];
        }
        
        $sql = $this->buildProfitForecastedSql($mediaBuyer, $news, $sources, $request);
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();
        $conversions = $stmt->fetchAll();

        foreach ($newsFinanceRow as $i => $item) {
            $newsFinanceRow[$i]['profit_forecasted'] = 0;
            foreach ($conversions as $conversion) {
                if ($conversion['news_id'] == $item['n_id'] && $conversion['source_id'] == $item['s_id']) {
                    $newsFinanceRow[$i]['profit_forecasted'] = $conversion['profit_forecasted'];
                    break;
                }
            }
        }

        return $newsFinanceRow;
    }

    private function buildProfitForecastedSql($mediaBuyer, $news, $sources, $request)
    {
        $sql = "SELECT c.news_id, c.source_id, sum(c.amount_rub * tss.approve_average_percentage) - ct.cost_rub  as profit_forecasted
        FROM conversions as c 
        LEFT JOIN teasers_sub_group_settings as tss ON c.subgroup_id = tss.teasers_sub_group_id 
        LEFT JOIN costs as ct ON c.mediabuyer_id = ct.mediabuyer_id AND c.source_id = ct.source_id AND c.news_id = ct.news_id
        WHERE c.mediabuyer_id = " . $mediaBuyer->getId();

        $sql = $this->addNewsAndSourcesArrsToSql($sql, $sources, $news);

        $sql = $this->addDateRangeFilterToSql($request, $sql, "created_at");
                
        $sql .= " GROUP BY c.news_id, c.source_id;";

        return $sql;  
    }

    private function distributeROIForecasted($newsFinanceRow, $mediaBuyer, $request)
    {
        $sources = [];   
        $news = [];
        foreach ($newsFinanceRow as $newsFinanceRowItem) {
            $sources[] = $newsFinanceRowItem['s_id'];
            $news[] = $newsFinanceRowItem['n_id'];
        }
        
        $sql = $this->buildROIForecastedSql($mediaBuyer, $news, $sources, $request);
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();
        $conversions = $stmt->fetchAll();

        foreach ($newsFinanceRow as $i => $item) {
            $newsFinanceRow[$i]['roi_forecasted'] = 0;
            foreach ($conversions as $conversion) {
                if ($conversion['news_id'] == $item['n_id'] && $conversion['source_id'] == $item['s_id']) {
                    $newsFinanceRow[$i]['roi_forecasted'] = $conversion['roi_forecasted'];
                    break;
                }
            }
        }

        return $newsFinanceRow;
    }

    private function buildROIForecastedSql($mediaBuyer, $news, $sources, $request)
    {
        $sql = "SELECT c.news_id, c.source_id, sum(c.amount_rub / ct.cost_rub) * 100 - 100  as roi_forecasted
        FROM conversions as c 
        LEFT JOIN teasers_sub_group_settings as tss ON c.subgroup_id = tss.teasers_sub_group_id 
        LEFT JOIN costs as ct ON c.mediabuyer_id = ct.mediabuyer_id AND c.source_id = ct.source_id AND c.news_id = ct.news_id
        WHERE c.mediabuyer_id = " . $mediaBuyer->getId();

        $sql = $this->addNewsAndSourcesArrsToSql($sql, $sources, $news);

        $sql = $this->addDateRangeFilterToSql($request, $sql, "created_at");
                
        $sql .= " GROUP BY c.news_id, c.source_id;";

        return $sql;  
    }

    private function addNewsAndSourcesArrsToSql($sql, $sources, $news) {
        if (!empty($sources)) {
            $sql .= " AND c.source_id IN (" . implode(',', $sources) . ")";
        }

        if (!empty($news)) {
            $sql .= " AND c.news_id IN (" . implode(',', $news) . ")";
        }

        return $sql;
    }

    private function addDateRangeFilterToSql($request, $sql, $dateFieldName)
    {
        if (!empty($request->query->get('from'))) {
            $from = new \DateTime($request->query->get('from'));
            $sql .= " AND c." . $dateFieldName . " >= '" . $from->format('Y-m-d 00:00:00') . "'";
        }

        if (!empty($request->query->get('to'))) {
            $to = new \DateTime($request->query->get('to'));
            $sql .= " AND c." . $dateFieldName . " <= '" . $to->format('Y-m-d 23:59:59') . "'";
        }

        return $sql;
    }
}