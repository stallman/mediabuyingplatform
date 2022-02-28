<?php


namespace App\Traits\Dashboard;


use App\Entity\BlackList;
use App\Entity\Conversions;
use App\Entity\Sources;
use App\Entity\TeasersClick;
use App\Entity\User;
use App\Entity\WhiteList;
use App\Service\PeriodMapper\CurrentMonth;
use App\Service\PeriodMapper\CurrentWeek;
use App\Service\PeriodMapper\CurrentYear;
use App\Service\PeriodMapper\DayBeforeYesterday;
use App\Service\PeriodMapper\EmptyPeriod;
use App\Service\PeriodMapper\LastMonth;
use App\Service\PeriodMapper\LastWeek;
use App\Service\PeriodMapper\LastYear;
use App\Service\PeriodMapper\Month;
use App\Service\PeriodMapper\ThreeMonth;
use App\Service\PeriodMapper\Today;
use App\Service\PeriodMapper\TwoMonth;
use App\Service\PeriodMapper\TwoWeek;
use App\Service\PeriodMapper\Week;
use App\Service\PeriodMapper\Yesterday;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

trait TrafficAnalysisTrait
{
    private array $otherFiltreFields = [
        'no', 'utmTerm', 'utmContent', 'news', 'news_categories',
        'subid1', 'subid2', 'subid3', 'subid4', 'subid5', 'os', 'countryCode',
    ];
    private array $disabledSortableKeys = [
        //'uniq_visits',
    ];
    private array $orderedColumnKeys = [
        'click_count',
        //'total_leads', // next ordered column
    ];
    public static array $reportColumns = [
        ['disabled' => true, 'sortable' => true, 'canBlacked' => false, 'columnName' => 'source', 'label' => 'Источник'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => false, 'columnName' => 'campaign', 'label' => 'Кампании (источник)'],
    ];
    public static array $groupColumns = [
        ['disabled' => true, 'sortable' => true, 'canBlacked' => true,  'columnName' => 'utmTerm', 'label' => 'Сайты'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => false,  'columnName' => 'source', 'label' => 'Источники'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => true,  'columnName' => 'utmCampaign', 'label' => 'Кампании (источник)'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => true,  'columnName' => 'utmContent', 'label' => 'Тизеры(источник)'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => true, 'columnName' => 'teaser', 'label' => 'Тизеры(новостник)'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => false, 'columnName' => 'news', 'label' => 'Новости'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => false, 'columnName' => 'createdAt', 'label' => 'Даты'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => false, 'columnName' => 'news_category', 'label' => 'Группы новостей'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => false,  'columnName' => 'countryCode', 'label' => 'Страны'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => false, 'columnName' => 'city', 'label' => 'Города'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => false, 'columnName' => 'trafficType', 'label' => 'Десктоп/мобайл'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => false, 'columnName' => 'os', 'label' => 'ОС'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => false, 'columnName' => 'osWithVersion', 'label' => 'ОС (с версией)'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => false, 'columnName' => 'browser', 'label' => 'Браузеры'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => false, 'columnName' => 'browserWithVersion', 'label' => 'Браузеры (с версией)'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => false, 'columnName' => 'mobileBrand', 'label' => 'Моб. устройства (производители)'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => false, 'columnName' => 'mobileModel', 'label' => 'Моб. устройства (модели)'],
        //['disabled' => true, 'sortable' => true, 'canBlacked' => false, 'columnName' => 'mobileOperator', 'label' => 'Моб. операторы'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => false, 'columnName' => 'screenSize', 'label' => 'Размер экрана'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => true,  'columnName' => 'subid1', 'label' => 'SUBID 1'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => true,  'columnName' => 'subid2', 'label' => 'SUBID 2'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => true,  'columnName' => 'subid3', 'label' => 'SUBID 3'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => true,  'columnName' => 'subid4', 'label' => 'SUBID 4'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => true,  'columnName' => 'subid5', 'label' => 'SUBID 5'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => false, 'columnName' => 'timesOfDay', 'label' => 'Время суток'],
        ['disabled' => true, 'sortable' => true, 'canBlacked' => false, 'columnName' => 'dayOfWeek', 'label' => 'Дни недели'],
        //['disabled' => true, 'sortable' => true, 'canBlacked' => false, 'columnName' => 'ip', 'label' => 'IP'],
    ];
    public static array $numericColumns = [
        ['disabled' => true, 'sortable' => true, 'columnName' => 'visits', 'label' => 'Клики'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'visits_percent', 'label' => 'Клики (%)'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'uniq_visits', 'label' => 'Уники'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'uniq_visits_percent', 'label' => 'Уники (%)'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'click_count', 'label' => 'КПТ', 'title' => 'Клики по тизерам'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'percent_of_total_click_count', 'label' => 'КПТ (%)', 'title' => 'Клики по тизерам (%)'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'percent_probiv', 'label' => 'Пробив (%)'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'total_leads', 'label' => 'Лиды'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'leads_pending_count', 'label' => 'Холд'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'leads_approve_count', 'label' => 'Подтв.'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'leads_declined_count', 'label' => 'Отклон.'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'percent_leads_pending', 'label' => 'Холд (%)'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'percent_leads_declined', 'label' => 'Отклон. (%)'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'percent_leads_approve', 'label' => 'Подтв. (%)'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'cr_conversion', 'label' => 'CR'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'middle_lead', 'label' => 'Сред. лид'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'real_income', 'label' => 'Доход'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'real_epc', 'label' => 'EPC'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'lead_price', 'label' => 'Цена лида'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'real_roi', 'label' => 'ROI'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'epc_projected', 'label' => 'EPC прогн.'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'consumption', 'label' => 'Расход'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'income_projected', 'label' => 'Доход прогн.'],
        ['disabled' => true, 'sortable' => true, 'columnName' => 'roi_projected', 'label' => 'ROI прогн.'],
    ];
    private $visits;
    private $uniqVisits;
    private $totalVisitsCount;
    private $totalLeadsCount;
    private $pendingLeadsCount;
    private $declinedLeadsCount;
    private $approvedLeadsCount;
    private array $amountIncome = [];
    private $amountIncomeApproved;
    private $teaserClickCount;
    private $rawPayout;
    private $costs;
    private array $costDates = [];
    private $consumption;

    private static $nonGroupedVisits;
    private static $groupData;
    private static $uuidGroups;
    private static $sourceGroups;
    private static $newsGroups;
    private static $buyerClickCount;
    private static $amountCost;

    private static array $groups = [];
    private static array $rowsByGroups = [];
    private static array $rows = [];
    private static array $total = [];
    private static array $topVisits = [];
    private static array $topClicks = [];
    private static int $topRowNum = 0;
    private static array $blackList = [];
    private static array $whiteList = [];
    private static array $inBlack = [];
    private static array $inWhite = [];


    public function transformSettingsFieldsData(?array $settingsFields)
    {
        if(!$settingsFields) return null;

        $settingsFieldsData = $this->getSettingsFields();

        foreach($settingsFields as $key => $field) {
            foreach($field as $itemKey => $item) {
                unset($settingsFields[$key][$itemKey]);
                $settingsFields[$key][$item['value']] = $settingsFieldsData[$key][$item['value']];
            }
        }

        return $settingsFields;
    }

    public function diffSettingsFieldsData(?array $settingsFields)
    {
        if(!$settingsFields) return null;

        $settingsFieldsData = $this->getSettingsFields();
        $diff = array_diff_key($settingsFieldsData, $settingsFields);

        foreach($diff as $diffKey => $value) {
            unset($settingsFieldsData[$diffKey]);
        }

        foreach($settingsFieldsData as $key => $value) {
            $diff = array_diff_key($settingsFieldsData[$key], $settingsFields[$key]);

            foreach($diff as $diffKey => $value2) {
                unset($settingsFieldsData[$key][$diffKey]);
            }
        }

        return $settingsFieldsData;
    }

    public function getColumnsStatus($fieldsSettings, $groups)
    {
        $userFieldsSettings = [];

        foreach($fieldsSettings as $settingType => $settings) {
            $userFieldsSettings = array_merge($userFieldsSettings, array_keys($settings));
        }

        foreach (static::$numericColumns as $key => $column) {
            if (in_array($column['columnName'], $userFieldsSettings)) {
                static::$numericColumns[$key]['disabled'] = !$column['disabled'];
            }
        }

        foreach (static::$groupColumns as $key => $column) {
            if (in_array($column['columnName'], $groups)) {
                static::$groupColumns[$key]['disabled'] = !$column['disabled'];
            }
        }

        return array_merge(static::$groupColumns, static::$numericColumns);
    }

    public function getPeriod($period)
    {
        switch($period) {
            case 'today':
                return new Today();
            case 'yesterday':
                return new Yesterday();
            case 'day-before-yesterday':
                return new DayBeforeYesterday();
            case 'week':
                return new Week();
            case 'current-week':
                return new CurrentWeek();
            case 'last-week':
                return new LastWeek();
            case 'two-week':
                return new TwoWeek();
            case 'current-month':
                return new CurrentMonth();
            case 'month':
                return new Month();
            case 'last-month':
                return new LastMonth();
            case 'two-month':
                return new TwoMonth();
            case 'three-month':
                return new ThreeMonth();
            case 'current-year':
                return new CurrentYear();
            case 'last-year':
                return new LastYear();
            default:
                return new EmptyPeriod();
        }
    }

    public function convertDate($from, $to)
    {
        try{
            $from = new \DateTime($from);
        } catch(\Exception $e) {
            $from = null;
        }

        try{
            $to = new \DateTime($to);
            $to->setTime(23, 59, 59);
        } catch(\Exception $e) {
            $to = null;
        }

        return [$from, $to];
    }

    public function getVisits(?\DateTimeInterface $from,
                              ?\DateTimeInterface $to,
                              ?array $reportSettings,
                              ?array $otherSettings,
                              bool $getTopTeasers = false
    ): array {
        $filteringData = [];
        $filteringData = $this->getFilteringDataByKey($filteringData, $reportSettings, 'source');
        $filteringData = $this->getFilteringDataByKey($filteringData, $reportSettings, 'campaign');
        $otherSettings = $this->getFilteringData($otherSettings, $otherSettings);

        $results = $this->visitsRepo->getTrafficAnalysis(
            $this->getUser(),
            $this->getGroupData($reportSettings),
            $from,
            $to,
            $filteringData,
            $otherSettings,
            $getTopTeasers
        );

        return $results;
    }

    private function getBLackListGroups(): array
    {
        $blackListGroups = [];
        foreach (static::$groupColumns as $columnParam) {
            if ($columnParam['canBlacked']) {
                $blackListGroups[] = $columnParam['columnName'];
            }
        }
        return $blackListGroups;
    }

    private function getFilteringData(array $filteringData, ?array $otherSettings): array
    {
        for($i = 1; $i < 4; $i++) {
            if (isset($otherSettings['otherFilterParams' . $i])) {
                if (!empty($otherSettings['otherFilterParams' . $i]) && isset($otherSettings['otherFilterValues' . $i]) && !empty($otherSettings['otherFilterValues' . $i])) {
                    $filteringData[$this->otherFiltreFields[$otherSettings['otherFilterParams' . $i]]] = $otherSettings['otherFilterValues' . $i];
                }
            }
            unset($filteringData['otherFilterParams' . $i], $filteringData['otherFilterValues' . $i]);
        }

        return $filteringData;
    }

    private function getDropList(array $otherSettings): array
    {
        $dropList = [];

        if(isset($otherSettings['blackListParams']) && !empty($otherSettings['blackListParams'])
            && isset($otherSettings['dropTrafficByBl'])){
            $dropList = [
                'groupName' => $otherSettings['blackListParams'],
                'values' => array_filter(explode(',', trim($otherSettings['dropTrafficByBl']))),
            ];

            if (!$dropList['values']) {
                $blackListItems = $this->blackListRepo->findBy(['buyer' => $this->getUser(), 'groupName' => $dropList['groupName']]);

                /** @var BlackList $blackListItem */
                foreach ($blackListItems as $blackListItem) {
                    $dropList['values'][] = $blackListItem->getGroupId();
                }
            }
        }

        return $dropList;
    }

    private function getDateFromTo(?array $reportSettings)
    {
        if(isset($reportSettings['period']) && !empty($reportSettings['period'])){
            $period = $this->getPeriod($reportSettings['period']);
            [$from, $to] = $period->getDateBetween();
        } elseif(isset($reportSettings['from']) && !empty($reportSettings['from']) && isset($reportSettings['to']) && !empty($reportSettings['from'])) {
            [$from, $to] = $this->convertDate($reportSettings['from'], $reportSettings['to']);
        } else {
            $from = null;
            $to = null;
        }

        return [$from, $to];
    }

    private function getFilteringDataByKey(array $filteringData, ?array $reportSettings, string $key)
    {
        if(isset($reportSettings[$key]) && !empty($reportSettings[$key])){
            $filteringData[$key] = $reportSettings[$key];
        }
        return $filteringData;
    }

    private function getGroupData($reportSettings = [])
    {
        if (isset(static::$groupData)) {
            return static::$groupData;
        }

        $groupParams = [];

        if(isset($reportSettings['level1']) && $reportSettings['level1'] != ''){
            $groupParams[] = $reportSettings['level1'];
        }
        if(isset($reportSettings['level2']) && $reportSettings['level2'] != ''){
            $groupParams[] = $reportSettings['level2'];
        }
        if(isset($reportSettings['level3']) && $reportSettings['level3'] != ''){
            $groupParams[] = $reportSettings['level3'];
        }

        static::$groupData = $groupParams;

        return static::$groupData;
    }

    private function initBlackWhiteLists(string $groupDelimeter): void
    {
        $sortedGroups = self::$groups;
        sort($sortedGroups);
        $stringGroups = implode(',', $sortedGroups);
        $parameters = ['buyer' => $this->getUser(), 'field' => $stringGroups];

        $blackList = $this->entityManager->getRepository(BlackList::class)->findBy($parameters);

        /** @var BlackList $item */
        foreach ($blackList as $item) {
            $key = $item->getGroupName() . $groupDelimeter . $item->getGroupId();
            self::$blackList[$key] = $item;
        }

        $whiteList = $this->entityManager->getRepository(WhiteList::class)->findBy($parameters);

        /** @var WhiteList $item */
        foreach ($whiteList as $item) {
            $key = $item->getGroupName() . $groupDelimeter . $item->getGroupId();
            self::$whiteList[$key] = $item;
        }
    }

    private function initTotal(array $columns, array $numericColumnNames): void
    {
        self::$total = ['id' => '<b>ИТОГО</b>'];

        foreach($columns as $column) {
            $cn = $column['columnName'];
            self::$total[$cn] = '';

            if (in_array($cn, $numericColumnNames)) {
                self::$total[$cn] = 0;
            }
        }

        foreach ($numericColumnNames as $cn) {
            if (!isset(self::$total[$cn])) {
                self::$total[$cn] = 0;
            }
        }
    }

    private function initRow(int $i, array $groups, string $groupDelimeter, string $groupsKey, array $columns, array $numericColumnNames): void
    {
        self::$rows[$i] = ['id' => []];
        $groupValues = explode($groupDelimeter, $groupsKey);

        foreach ($groups as $index => $group) {
            self::$rows[$i]['id'][$group] = $groupValues[$index];

            $key = $group . $groupDelimeter . $groupValues[$index];

            if (isset(self::$blackList[$key])) {
                /** @var BlackList $blackListItem */
                $blackListItem = self::$blackList[$key];

                self::$rows[$i]['in_black_list'][] = [
                    'groupName' => $blackListItem->getGroupName(),
                    'groupId' => $blackListItem->getGroupId(),
                ];
            }

            if (isset(self::$whiteList[$key])) {
                /** @var WhiteList $whiteListItem */
                $whiteListItem = self::$whiteList[$key];

                self::$rows[$i]['in_white_list'][] = [
                    'groupName' => $whiteListItem->getGroupName(),
                    'groupId' => $whiteListItem->getGroupId(),
                ];
            }

            //if (isset(self::$whiteList[$key])) {
            //    self::$rows[$i]['in_white_list'] = 'in_white_list';
            //}
        }

        foreach($columns as $column) {
            $cn = $column['columnName'];
            self::$rows[$i][$cn] = '';

            if (in_array($cn, $numericColumnNames)) {
                self::$rows[$i][$cn] = 0;
            }
        }

        foreach ($numericColumnNames as $cn) {
            if (!in_array($cn, self::$rows[$i])) {
                self::$rows[$i][$cn] = 0;
            }
        }
    }

    private function inBlack(int $i, string $excludedGroup, array $dropGroupIds): void
    {
        $inBlackList = self::$rows[$i]['in_black_list'] ?? null;

        if (null !== $inBlackList && $excludedGroup &&
            in_array($excludedGroup, array_column($inBlackList, 'groupName'))
        ) {
            if (empty($dropGroupIds)) {
                self::$inBlack[] = $i;
            } else {
                foreach ($dropGroupIds as $groupId) {
                    if (in_array($groupId, array_column($inBlackList, 'groupId'))) {
                        self::$inBlack[] = $i;
                    }
                }
            }
        }
    }

    private function generateRow(array $filteringData, array $visitsByGroups, array $sources, array $columns, array $trafficTypes, array $daysOfWeek,
                                 \DateTime $from, \DateTime $to , int $i
    ): void {
        $this->setTrafficAnalysisVariables($filteringData, $visitsByGroups, $sources, $from, $to, $i);

        foreach($columns as $column) {
            $cn = $column['columnName'];

            switch($cn) {
                case 'source':
                        $source = null;
                        if (isset(self::$rows[$i]['id'][$cn]) && 'null' !== self::$rows[$i]['id'][$cn]) {
                            /** @var Sources|null $source */
                            $source = $this->sources->get(intval(self::$rows[$i]['id'][$cn]));
                        }
                        $title = null !== $source ? $source->getTitle() : 'Не указан';

                        self::$rows[$i][$cn] = $title;
                    break;
                case 'utmCampaign':
                        self::$rows[$i][$cn] = self::$rows[$i]['id'][$cn] ?? 'Не указан';
                    break;
                case 'trafficType':
                        self::$rows[$i][$cn] = isset(self::$rows[$i]['id'][$cn]) ? $trafficTypes[self::$rows[$i]['id'][$cn]] : '';
                    break;
                case 'dayOfWeek':
                        self::$rows[$i][$cn] = isset(self::$rows[$i]['id'][$cn]) ? $daysOfWeek[self::$rows[$i]['id'][$cn]] : '';
                    break;
                case 'visits':
                        self::$rows[$i][$cn] = $this->visits;
                        self::$total[$cn] += $this->visits;
                    break;
                case 'uniq_visits':
                        self::$rows[$i][$cn] = $this->uniqVisits;
                        self::$total[$cn] += $this->uniqVisits;
                    break;
                case 'click_count':
                        self::$rows[$i][$cn] = $this->teaserClickCount;
                        self::$total[$cn] += $this->teaserClickCount;
                    break;
                case 'uniq_visits_percent':
                        self::$rows[$i][$cn] = $this->getPercent($this->visits, $this->uniqVisits);
                    break;
                case 'total_leads':
                        self::$rows[$i][$cn] = $this->totalLeadsCount;
                        self::$total[$cn] += $this->totalLeadsCount;
                    break;
                case 'leads_pending_count':
                        self::$rows[$i][$cn] = $this->pendingLeadsCount;
                        self::$total[$cn] += $this->pendingLeadsCount;
                    break;
                case 'percent_leads_declined':
                        self::$rows[$i][$cn] = $this->getPercent($this->totalLeadsCount,$this->declinedLeadsCount);
                    break;
                case 'leads_approve_count':
                        self::$rows[$i][$cn] = $this->approvedLeadsCount;
                        self::$total[$cn] += $this->approvedLeadsCount;
                    break;
                case 'percent_leads_pending':
                        self::$rows[$i][$cn] = $this->getPercent($this->totalLeadsCount,$this->pendingLeadsCount);
                    break;
                case 'percent_leads_approve':
                        self::$rows[$i][$cn] = $this->getPercent($this->totalLeadsCount,$this->approvedLeadsCount);
                    break;
                case 'leads_declined_count':
                        self::$rows[$i][$cn] = $this->declinedLeadsCount;
                        self::$total[$cn] += $this->declinedLeadsCount;
                    break;
                case 'cr_conversion':
                        $crConversion = $this->calculateStatistic->calculateCR($this->totalLeadsCount, $this->uniqVisits);
                        self::$rows[$i][$cn] = $crConversion;
                        self::$total[$cn] += $crConversion;
                    break;
                case 'middle_lead':
                        $middle_lead = $this->divisionByZero($this->amountIncome[$i], $this->totalLeadsCount);
                        self::$rows[$i][$cn] = $middle_lead;
                        self::$total[$cn] += $middle_lead;
                    break;
                case 'real_income':
                        self::$rows[$i][$cn] = $this->amountIncomeApproved;
                        self::$total[$cn] += $this->amountIncomeApproved;
                    break;
                case 'real_epc':
                        $realEpc = $this->calculateStatistic->calculateEPC($this->amountIncomeApproved, $this->uniqVisits);
                        self::$rows[$i][$cn] = $realEpc;
                    break;
                case 'lead_price':
                        self::$rows[$i][$cn] = $this->totalLeadsCount;
                    break;
                case 'epc_projected':
                        $epcProjected = $this->calculateStatistic->calculateEPC($this->rawPayout, $this->uniqVisits);
                        self::$rows[$i][$cn] = $epcProjected;
                        self::$total[$cn] += $epcProjected;
                    break;
                case 'consumption':
                        self::$rows[$i][$cn] = $this->consumption;
                        self::$total[$cn] += $this->consumption;
                    break;
                case 'income_projected':
                        self::$rows[$i][$cn] = $this->rawPayout;
                        self::$total[$cn] += $this->rawPayout;
                    break;
                default:
                    self::$rows[$i][$cn] = self::$rows[$i]['id'][$cn] ?? '';
            }
        }

        self::$rows[$i]['id'] = $this->getBulkCheckBoxGroups(self::$rows[$i]['id']);
    }

    private function getTrafficAnalysis(array $visits, array $groups, array $filteringData, array $otherSettings,
                                        array $columns, \DateTime $from, \DateTime $to)
    {
        self::$groups = $groups;
        $columnNames = array_column($columns, 'columnName');
        $trafficTypes = $this->translateTrafficType();
        $daysOfWeek = $this->translateDaysOfWeek();
        $numericColumnNames = array_column(static::$numericColumns, 'columnName');
        $groupDelimeter = ':::';

        $sources = $filteringData['source'] ?? [];
        /** @var string|null $source */
        foreach ($sources as $key => $source) {
            $sources[$key] = 'NULL' === $source ? null : intval($source);
        }
        /** @var Collection $sourceEntities */
        $sourceEntities = $this->sources->matching(Criteria::create()->where(Criteria::expr()->in('id', $sources)));
        if (false !== array_search(null, $sources)) {
            $sourceEntities->set(null, null);
        }
        $sources = $sourceEntities->getValues();


        $this->initBlackWhiteLists($groupDelimeter);
        $excludedGroup = $otherSettings['blackListParams'];
        $dropGroupIds = explode(',', $otherSettings['dropTrafficByBl']);
        $dropGroupIds = array_filter($dropGroupIds);

        $this->initTotal($columns, $numericColumnNames);

        foreach ($visits as $visit) {
            $keys = [];
            foreach (self::$groups as $group) {
                switch ($visit[$group]) {
                    case null: $keys[] = 'null'; break;
                    case '': $keys[] = 'empty'; break;
                    default: $keys[] = $visit[$group];
                }
            }
            self::$rowsByGroups[ implode($groupDelimeter, $keys) ][$visit['uuid']] = $visit;
        }

        $i = self::$topRowNum + 1;
        foreach(self::$rowsByGroups as $groupsKey => $visitsByGroups) {
            $this->initRow($i++, self::$groups, $groupDelimeter, $groupsKey, $columns, $numericColumnNames);
        }

        $i = self::$topRowNum + 1;
        foreach(self::$rowsByGroups as $groupsKey => $visitsByGroups) {
            $this->inBlack($i, $excludedGroup, $dropGroupIds);
            $this->generateRow($filteringData, $visitsByGroups, $sources, $columns, $trafficTypes, $daysOfWeek, $from, $to, $i++);
        }

        /** generate top visits for news group */
        if (!empty(self::$topVisits)) { // todo check blacklist for top visits
            $i = self::$topRowNum;
            $keys = [];
            foreach (self::$groups as $group) {
                switch (true) {
                    case $group === 'news': $keys[] = 'Teasers'; break;
                    case $visit[$group] === null: $keys[] = 'null'; break;
                    case $visit[$group] === '': $keys[] = 'empty'; break;
                    default: $keys[] = $visit[$group];
                }
            }

            $groupsKey = implode($groupDelimeter, $keys);
            $this->initRow($i, self::$groups, $groupDelimeter, $groupsKey, $columns, $numericColumnNames);

            $this->generateRow($filteringData, self::$topVisits, $sources, $columns, $trafficTypes, $daysOfWeek, $from, $to, $i);
        }

        if (empty(self::$rows)) {
            return ['rows' => self::$rows, 'total_row' => self::$total, 'total_count' => count(self::$rows)];
        }

        $extraParams = [];

        // remove banned rows
        $this->removeBannedRows();

        // recalc results
        $this->trafficAnalysisRecalc('visits_percent', $extraParams, function ($idx, $cn) {
            $result = 100 * $this->divisionByZero(
                self::$rows[$idx]['visits'],
                self::$total['visits']
            );
            self::$rows[$idx][$cn] = $result;
            self::$total[$cn] += $result;
        });

        $this->trafficAnalysisRecalc('percent_of_total_click_count', $extraParams, function ($idx, $cn) {
            $result = 100 * $this->divisionByZero(
                self::$rows[$idx]['click_count'],
                self::$total['click_count']
            );
            self::$rows[$idx][$cn] = $result;
            self::$total[$cn] += $result;
        });

        $this->trafficAnalysisRecalc('percent_probiv', $extraParams, function ($idx, $cn) {
            $result = $this->divisionByZero(
                self::$rows[$idx]['click_count'],
                self::$rows[$idx]['uniq_visits']
            ) * 100;
            self::$rows[$idx][$cn] = $result;
            self::$total[$cn] += $result;
        });

        $this->trafficAnalysisRecalc('real_roi', $extraParams, function ($idx, $cn) {
            $result = $this->divisionByZero(
                100 * (self::$rows[$idx]['real_income'] - self::$rows[$idx]['consumption']),
                self::$rows[$idx]['consumption']
            );
            self::$rows[$idx][$cn] = $result;
        });

        $this->trafficAnalysisRecalc('roi_projected', $extraParams, function ($idx, $cn) {
            $result = $this->divisionByZero(
                100 * (self::$rows[$idx]['income_projected'] - self::$rows[$idx]['consumption']),
                self::$rows[$idx]['consumption']
            );
            self::$rows[$idx][$cn] = $result;
        });

        $this->trafficAnalysisRecalc('lead_price', $extraParams, function ($idx, $cn) {
            $result = $this->divisionByZero(self::$rows[$idx]['consumption'], self::$rows[$idx][$cn]);
            self::$rows[$idx][$cn] = $result;
            self::$total[$cn] += $result;
        });

        // calculate total row
        $totalRow = [];
        foreach (self::$total as $cn => $value) {
            switch ($cn) {
                case 'uniq_visits_percent':
                    $totalRow[$cn] = $this->getPercent(self::$total['visits'], self::$total['uniq_visits']);
                    break;
                case 'percent_leads_declined':
                    $totalRow[$cn] = $this->getPercent(self::$total['total_leads'], self::$total['leads_declined_count']);
                    break;
                case 'percent_leads_pending':
                    $totalRow[$cn] = $this->getPercent(self::$total['total_leads'], self::$total['leads_pending_count']);
                    break;
                case 'percent_leads_approve':
                    $totalRow[$cn] = $this->getPercent(self::$total['total_leads'], self::$total['leads_approve_count']);
                    break;
                case 'percent_probiv':
                    $totalRow[$cn] = $this->divisionByZero(self::$total['click_count'], self::$total['uniq_visits']) * 100;
                    break;
                case 'cr_conversion':
                    $totalRow[$cn] = $this->divisionByZero(self::$total['total_leads'], self::$total['uniq_visits']) * 100;
                    break;
                case 'lead_price':
                    $totalRow[$cn] = $this->divisionByZero(self::$total['consumption'], self::$total['total_leads']);
                    break;
                case 'middle_lead':
                    $cnt = 0;
                    array_walk(self::$rows, function ($row) use (&$cnt) {
                        $cnt += (isset($row['middle_lead']) && $row['middle_lead'] > 0) ? 1 : 0;
                    });
                    $cnt = $cnt ? $cnt : 1;
                    $totalRow[$cn] = self::$total[$cn] / $cnt;
                    break;
                case 'roi_projected':
                    $totalRow[$cn] = $this->divisionByZero(100 * (self::$total['income_projected'] - self::$total['consumption']), self::$total['consumption']);
                    break;
                case 'real_roi':
                    $totalRow[$cn] = $this->divisionByZero(100 * (self::$total['real_income'] - self::$total['consumption']), self::$total['consumption']);
                    break;
                case 'epc_projected':
                    $totalRow[$cn] = $this->divisionByZero(self::$total['income_projected'], self::$total['uniq_visits']);
                    break;
                case 'real_epc':
                    $totalRow[$cn] = $this->divisionByZero(self::$total['real_income'], self::$total['uniq_visits']);
                    break;
                default:
                    $totalRow[$cn] = $value;
            }
        }

        // round columns after calc all columns
        $roundColumns = ['visits_percent','lead_price','consumption','income_projected','real_epc','uniq_visits_percent','percent_leads_declined','percent_leads_pending','percent_leads_approve','middle_lead','real_income','epc_projected','percent_of_total_click_count','percent_probiv'];
        self::$rows = $this->roundRows(self::$rows, $roundColumns, 2);

        // round columns without precision after calc all columns
        $roundColumns = ['real_roi','roi_projected'];
        self::$rows = $this->roundRows(self::$rows, $roundColumns);

        // percentify columns after calc all columns
        $percentifyColumns = ['visits_percent','cr_conversion','uniq_visits_percent','percent_leads_declined','percent_leads_pending','percent_leads_approve','percent_of_total_click_count','percent_probiv','real_roi','roi_projected'];
        self::$rows = $this->percentifyRows(self::$rows, $percentifyColumns);

        // round total columns after calc all columns
        $roundTotalColumns = ['visits_percent','uniq_visits_percent','percent_leads_declined','percent_leads_pending','percent_leads_approve','percent_of_total_click_count','percent_probiv','cr_conversion','lead_price','middle_lead','real_income','consumption','income_projected','epc_projected','real_epc'];
        $totalRow = $this->roundRow($totalRow, $roundTotalColumns, 2);

        // round total columns without precision after calc all columns
        $roundTotalColumns = ['real_roi','roi_projected'];
        $totalRow = $this->roundRow($totalRow, $roundTotalColumns);

        // percentify total columns after calc all columns
        $percentifyTotalColumns = ['visits_percent','uniq_visits_percent','percent_leads_declined','percent_leads_pending','percent_leads_approve', 'percent_of_total_click_count','percent_probiv','cr_conversion','roi_projected','real_roi'];
        $totalRow = $this->percentifyRow($totalRow, $percentifyTotalColumns);

        $trafficAnalysis = [
            'rows' => self::$rows,
            'total_row' => $totalRow,
            'total_count' => count(self::$rows),
        ];

        return $trafficAnalysis;
    }

    private function removeBannedRows(): void
    {
        foreach (self::$inBlack as $i) {
            $bannedRow = self::$rows[$i];
            unset(self::$rows[$i]);

            // need substract affected values
            foreach (self::$total as $cn => $row) {
                if (is_numeric($row) && $row > 0) {
                    self::$total[$cn] = $row - $bannedRow[$cn];
                }
            }
        }

        self::$rows = array_values(self::$rows);
    }

    private function getPercent(?int $totalCount, ?int $statusCount)
    {
        return $totalCount ? $statusCount / ($totalCount / 100) : 0;
    }

    private function divisionByZero($left, $right)
    {
        return $right ? $left / $right : 0;
    }

    private function getRawPayoutArr(User $mediaBuyer, array $uuids, array $sources, \DateTime $from, \DateTime $to)
    {
        $leads = $this->conversionsRepo->getMediaBuyerConversionsByUuidArr($mediaBuyer, $uuids, $sources, $from, $to);
        $rawPayout = 0;
        /** @var Conversions $lead */
        foreach($leads as $lead) {
            $tsgSetting = $this->teasersSubGroupSettingsRepo->getCountrySubGroupSettings($lead->getSubgroup(), $lead->getTeaserClick()->getCountryCode());
            if ($tsgSetting) {
                $percentApprove = $tsgSetting->getApproveAveragePercentage();
            } else {
                $defaultSubGroupSettings = $this->teasersSubGroupSettingsRepo->getDefaultSubGroupSettings($lead->getSubgroup());
                if($defaultSubGroupSettings){
                    $percentApprove = $defaultSubGroupSettings->getApproveAveragePercentage();
                } else {
                    $percentApprove = 0;
                }
            }
            $rawPayout += $lead->getAmountRub() * $percentApprove / 100;
        }

        return $rawPayout;
    }

    private function resetVars(array $filteringData): void
    {
        $this->visits = 0;
        $this->uniqVisits = 0;
        $this->totalVisitsCount = 0;
        $this->totalLeadsCount = 0;
        $this->pendingLeadsCount = 0;
        $this->declinedLeadsCount = 0;
        $this->approvedLeadsCount = 0;
        $this->amountIncomeApproved = 0;
        $this->teaserClickCount = 0;
        $this->rawPayout = 0;
        $this->costs = 0;
        $this->consumption = 0;
    }

    private function setTrafficAnalysisVariables(array $filteringData, array $visitsByGroups, array $sources, \DateTime $from, \DateTime $to , int $i): self
    {
        $this->resetVars($filteringData);

        $teaserClicksWithoutTop = [];

        if ($i === self::$topRowNum) {
            $teaserClicks = self::$topClicks;
        } else {
            $teaserClicks = $this->teasersClickRepo->getByBuyerUuids($this->getUser(), array_keys($visitsByGroups));
        }

        // с группировкой по новостям выносим визиты с топа тизеров в отдельный массив, таска #106
        if ($i !== self::$topRowNum && in_array('news', self::$groups)) {
            /** @var TeasersClick $teaserClick */
            foreach ($teaserClicks as $tcRow => $teaserClick) {
                if ('top' === $teaserClick->getPageType()) {
                    $topUuid = $teaserClick->getUuid()->toString();
                    self::$topClicks[] = $teaserClick;

                    if (!isset(self::$topVisits[$topUuid]) && isset($visitsByGroups[$topUuid])) {
                        self::$topVisits[$topUuid] = $visitsByGroups[$topUuid];
                    }

                    unset($visitsByGroups[$topUuid]);
                    continue;
                }

                $teaserClicksWithoutTop[] = $teaserClick;
            }

            $teaserClicks = $teaserClicksWithoutTop;
        }

        $this->teaserClickCount += count($teaserClicks);
        $createdAt = self::$rows[$i]['createdAt'] ?? null;
        $this->visits = count($visitsByGroups);
        $ips = array_column($visitsByGroups, 'ip');
        $this->uniqVisits = count(array_unique($ips));
        $this->totalVisitsCount += count($visitsByGroups);

        $uuids = [];
        /** @var TeasersClick $teaserClick */
        foreach($teaserClicks as $teaserClick){
            $uuids[] = $teaserClick->getUuid()->toString();
        }

        $this->totalLeadsCount +=$this->conversionsRepo->getTotalLeadsCountArr($this->getUser(), $uuids, $sources, $from, $to, null, $createdAt);
        $this->pendingLeadsCount +=$this->conversionsRepo->getTotalLeadsCountArr($this->getUser(), $uuids, $sources, $from, $to, 'pending', $createdAt);
        $this->declinedLeadsCount +=$this->conversionsRepo->getTotalLeadsCountArr($this->getUser(), $uuids, $sources, $from, $to, 'declined', $createdAt);
        $this->approvedLeadsCount +=$this->conversionsRepo->getTotalLeadsCountArr($this->getUser(), $uuids, $sources, $from, $to, 'approved', $createdAt);

        $amountApproved = 0;
        foreach($this->conversionsRepo->getAmountIncomeArr($this->getUser(), $uuids, 'approved') as $conv){
            $amountApproved += $conv['amount'];
        }
        $this->amountIncomeApproved = $this->currencyConverter->convertRubleToUserCurrency($amountApproved, $this->getUser());

        $amount = 0;
        foreach($this->conversionsRepo->getAmountIncomeArr($this->getUser(), $uuids) as $conv){
            $amount += $conv['amount'];
        }
        $this->amountIncome[$i] = $this->currencyConverter->convertRubleToUserCurrency($amount, $this->getUser());

        $this->rawPayout = $this->getRawPayoutArr($this->getUser(), $uuids, $sources, $from, $to);

        $campaigns = array_unique(array_column($visitsByGroups, 'utmCampaign'));

        $costs = $this->costsRepo->getTrafficAnalysisCostsArr($this->getUser(), $sources, $campaigns, $from, $to);
        $this->costDates = [];
        foreach($costs as $cost){
            $this->costs += $cost->getCostRub();
            $this->costDates[] = $cost->getDate()->format('Y-m-d');
            $this->costDates = array_unique($this->costDates);
        }

        $this->consumption = $this->costs;

        return $this;
    }

    private function trafficAnalysisRecalc(string $column, array $params, callable $callback): void
    {
        if (isset($params['columnNames']) && !in_array($column, $params['columnNames'])) {
            return;
        }

        foreach (self::$rows as $idx => $row) {
            foreach ($row as $columnName => $value) {
                switch ($columnName) {
                    case $column:
                        $callback($idx, $columnName, $value, $params);
                        break;
                }
            }
        }
    }

    private function roundRows(array $rows, array $roundedKeys, int $precision = 0): array
    {
        foreach ($rows as $idx => $row) {
            $rows[$idx] = $this->roundRow($row, $roundedKeys, $precision);
        }

        return $rows;
    }

    private function roundRow(array $row, array $roundedKeys, int $precision = 0): array
    {
        foreach ($row as $columnName => $value) {
            if (in_array($columnName, $roundedKeys)) {
                $row[$columnName] = round($value, $precision);
            }
        }

        return $row;
    }

    private function percentifyRows(array $rows, array $percentifiedKeys): array
    {
        foreach ($rows as $idx => $row) {
            $rows[$idx] = $this->percentifyRow($row, $percentifiedKeys);
        }

        return $rows;
    }

    private function percentifyRow(array $row, array $percentifiedKeys): array
    {
        foreach ($row as $columnName => $value) {
            if (in_array($columnName, $percentifiedKeys)) {
                $row[$columnName] = $value . '%';
            }
        }

        return $row;
    }
}
