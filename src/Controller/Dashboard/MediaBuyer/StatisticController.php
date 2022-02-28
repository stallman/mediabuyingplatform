<?php

namespace App\Controller\Dashboard\MediaBuyer;

use App\Controller\Dashboard\DashboardController;
use App\Entity\Sources;
use App\Form\FieldsSettingsType;
use App\Form\OtherSettingsType;
use App\Form\ReportSettingsType;
use App\Repository\NewsCategoryRepository;
use App\Service\CalculateStatistic;
use App\Service\CurrencyConverter;
use App\Service\ImageProcessor;
use App\Traits\Dashboard\NewsFinanceTrait;
use App\Traits\Dashboard\StatisticConstTrait;
use App\Traits\Dashboard\TrafficAnalysisTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Yaml\Yaml;
use App\Service\DateHelper;
use App\Repository\ConversionsRepository;
use App\Repository\CostsRepository;
use App\Repository\TeasersClickRepository;
use App\Repository\VisitsRepository;
use App\Repository\BlackListRepository;
use App\Repository\WhiteListRepository;
use App\Repository\TeasersSubGroupSettingsRepository;

class StatisticController extends DashboardController
{
    use NewsFinanceTrait;
    use StatisticConstTrait;
    use TrafficAnalysisTrait;

    public CalculateStatistic $calculateStatistic;
    public ConversionsRepository $conversionsRepo;
    public CostsRepository $costsRepo;
    public TeasersClickRepository $teasersClickRepo;
    public VisitsRepository $visitsRepo;
    public NewsCategoryRepository $newCategoryRepo;
    public BlackListRepository $blackListRepo;
    public WhiteListRepository $whiteListRepo;
    public TeasersSubGroupSettingsRepository $teasersSubGroupSettingsRepo;
    public Collection $sources;

    public function __construct(
        Yaml $yaml,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        ImageProcessor $imageProcessor,
        LoggerInterface $logger,
        CurrencyConverter $currencyConverter,
        SessionInterface $session,
        CalculateStatistic $calculateStatistic,
        ConversionsRepository $conversionsRepo,
        CostsRepository $costsRepo,
        TeasersClickRepository $teasersClickRepo,
        VisitsRepository $visitsRepo,
        NewsCategoryRepository $newCategoryRepo,
        BlackListRepository $blackListRepo,
        WhiteListRepository $whiteListRepo,
        TeasersSubGroupSettingsRepository $teasersSubGroupSettingsRepo
    )
    {
        parent::__construct($yaml, $entityManager, $slugger, $imageProcessor, $logger, $currencyConverter, $session);
        $this->calculateStatistic = $calculateStatistic;
        $this->conversionsRepo = $conversionsRepo;
        $this->costsRepo = $costsRepo;
        $this->teasersClickRepo = $teasersClickRepo;
        $this->visitsRepo = $visitsRepo;
        $this->newCategoryRepo = $newCategoryRepo;
        $this->blackListRepo = $blackListRepo;
        $this->whiteListRepo = $whiteListRepo;
        $this->teasersSubGroupSettingsRepo = $teasersSubGroupSettingsRepo;
        $this->sources = new ArrayCollection();
    }

    /**
     * @Route("/mediabuyer/statistic/traffic-analysis/list", name="mediabuyer_dashboard.statistic.traffic_analysis_list")
     */
    public function trafficAnalysisListAction()
    {
        $fieldsSettings = $this->diffSettingsFieldsData($this->getUser()->getReportFields());

        $reportSettingForm = $this->createForm(ReportSettingsType::class, null, [
            'user' => $this->getUser(),
            'attr' => [
                'id' => 'report_settings'
            ],
            'data' => [
                'report_settings' => $this->request->request->get('report_settings'),
            ],
        ])->handleRequest($this->request);
        $fieldsSettingsForm = $this->createForm(FieldsSettingsType::class, $fieldsSettings)->handleRequest($this->request);
        $otherSettingsForm = $this->createForm(OtherSettingsType::class, null, ['user' => $this->getUser(),
            'requestData' => $this->request->query->get('other_settings')
        ])->handleRequest($this->request);

        $groups = [$reportSettingForm->get('level1')->getData()];
        [$from, $to] = $this->getDateFromTo($groups);
        $columns = $this->getColumnsStatus($fieldsSettings, $groups);

        $columnNames = implode(',', array_column(TrafficAnalysisTrait::$groupColumns, 'columnName'));
        $numericColumns = implode(',', array_column(TrafficAnalysisTrait::$numericColumns, 'columnName'));

        /** @var Sources[] $sources */
        $sources = $this->entityManager->getRepository(Sources::class)->findAll();
        $sourceNames = [];
        foreach ($sources as $source) {
            $sourceNames[$source->getId()] = $source->getTitle();
        }
        $newsCategories = $this->newCategoryRepo->getNewsWithCategories();
        $trafficTypes = $this->translateTrafficType();
        $daysOfWeek = $this->translateDaysOfWeek();
        $blackList = $this->blackListRepo->getByGroupName($this->getUser());
        $whiteList = $this->whiteListRepo->getByGroupName($this->getUser());
        $costs = $this->costsRepo->getPreparedUserCosts($this->getUser());

        return $this->render('dashboard/mediabuyer/statistic/traffic-analysis/list.html.twig', [
            'columns' => $columns,
            'columnNames' => $columnNames,
            'numericColumns' => $numericColumns,
            'ajaxUrl' => $this->generateUrl('mediabuyer_dashboard.statistic.traffic_analysis_list_ajax'),
            'h1_header_text' => 'Анализ трафика',
            'periods' => $this->getPeriods(),
            'reportSettingForm' => $reportSettingForm->createView(),
            'fieldsSettingsForm' => $fieldsSettingsForm->createView(),
            'otherSettingsForm' => $otherSettingsForm->createView(),
            'from' => $from ? DateHelper::formatDefaultDate($from): null,
            'to' => $to ? DateHelper::formatDefaultDate($to) : null,
            'sourceNames' => $sourceNames,
            'newsCategories' => $newsCategories,
            'trafficTypes' => $trafficTypes,
            'daysOfWeek' => $daysOfWeek,
            'blackList' => $blackList,
            'whiteList' => $whiteList,
            'costs' => $costs,
        ]);
    }

    /**
     * @Route("/mediabuyer/statistic/traffic-analysis/list-ajax/", name="mediabuyer_dashboard.statistic.traffic_analysis_list_ajax")
     */
    public function trafficAnalysisListAjaxAction()
    {


        if ($this->container->has('session')) {
            $this->container->get('session')->save();
        }

        $fieldsSettings = $this->diffSettingsFieldsData($this->getUser()->getReportFields());

        $reportSettings = $this->request->request->get('report_settings');
        $otherSettings = $this->request->request->get('other_settings');
        $groups = [];
        $draw = $this->request->request->get('draw');

        if (
            !(isset($reportSettings['from']) && $reportSettings['from']) ||
            !(isset($reportSettings['to']) && $reportSettings['to']) ||
            !(isset($reportSettings['source']) && $reportSettings['source']) ||
            !(isset($reportSettings['campaign']) && $reportSettings['campaign'])
        ) {
            return JsonResponse::create([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => ['rows' => []],
            ], 200);
        }

        if (isset($reportSettings['level1']) && !empty($reportSettings['level1'])) {
            $groups[] = $reportSettings['level1'];
        }
        if (isset($reportSettings['level2']) && !empty($reportSettings['level2'])) {
            $groups[] = $reportSettings['level2'];
        }
        if (isset($reportSettings['level3']) && !empty($reportSettings['level3'])) {
            $groups[] = $reportSettings['level3'];
        }

        [$from, $to] = $this->getDateFromTo($reportSettings);

        $start = (int) $this->request->request->get('start');
        $length = (int) $this->request->request->get('length');

        $filteringData = [];
        $filteringData = $this->getFilteringDataByKey($filteringData, $reportSettings, 'source');
        $filteringData = $this->getFilteringDataByKey($filteringData, $reportSettings, 'campaign');

        if (array_key_exists('source', $filteringData) && array_key_exists('campaign', $filteringData)) {
            $data['rows'] = $this->getVisits($from,
                $to,
                $reportSettings,
                $otherSettings
            );

            if (in_array('news', $groups)) {
                $topTeaserRow = $this->getVisits($from,
                    $to,
                    $reportSettings,
                    $otherSettings,
                    true
                );

                $data['rows'] = array_merge($data['rows'], $topTeaserRow);
            }

            $leadIds = array_filter(array_unique(array_column($data['rows'], 'lead_ids')));
            $leadIds = implode(',', $leadIds);
            $leadsAmountRub = $this->conversionsRepo->getAmountRubByIds($leadIds);
            $leadsApproveAvgPercentages = $this->teasersSubGroupSettingsRepo->getApproveAvgPercentage($leadIds);

            $data['leads_amount_rub'] = $leadsAmountRub;
            $data['leads_approve_avg_percentages'] = $leadsApproveAvgPercentages;
        }

        $totalCount = count($data['rows']);
        $blackList = $this->blackListRepo->getByGroupName($this->getUser());
        $whiteList = $this->whiteListRepo->getByGroupName($this->getUser());

        return JsonResponse::create([
            'draw' => $draw,
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $totalCount,
            'data' => $data,
            'blackList' => $blackList,
            'whiteList' => $whiteList,
            'dateFrom' => $reportSettings['from'],
            'dateTo' => $reportSettings['to'],
        ], 200);
    }

    /**
     * @Route("/mediabuyer/statistic/traffic-analysis/settings-fields/update", name="mediabuyer_dashboard.statistic.traffic_analysis.setttings_fields_update", methods={"POST"})
     */
    public function trafficAnalysisSettingsFieldsUpdateAction()
    {
        try{
            $settingsFields = $this->transformSettingsFieldsData($this->request->request->get('settings-fields'));
            $user = $this->getUser();
            $user->setReportFields($settingsFields);
            $this->entityManager->flush();

            return JsonResponse::create('', 200);
        } catch(\Exception $exception) {
            $this->addFlash('danger', $this->getFlashMessage('user_settings_update_error'));

            return JsonResponse::create('', 500);
        }
    }

    /**
     * @Route("/mediabuyer/statistic/traffic-analysis/column-params", name="mediabuyer_dashboard.statistic.traffic_analysis.column_params", methods={"GET"})
     */
    public function trafficAnalysisColumnParams(): JsonResponse
    {
        return JsonResponse::create(TrafficAnalysisTrait::$groupColumns, 200);
    }

    /**
     * @Route("/mediabuyer/statistic/news-finance/list", name="mediabuyer_dashboard.statistic.news_finance_list")
     */
    public function newsFinanceListAction()
    {
        $reportSettingForm = $this->createForm(ReportSettingsType::class, null, ['user' => $this->getUser()])->handleRequest($this->request);

        return $this->render('dashboard/mediabuyer/statistic/news-finance/list.html.twig', [
            'columns' => $this->getNewsFinanceTableHeader($this->generateUrl('mediabuyer_dashboard.statistic.news_finance_list_ajax')),
            'h1_header_text' => 'Финансы по новостям',
            'reportSettingForm' => $reportSettingForm->createView(),
            'periods' => $this->getPeriods(),
        ]);
    }

    /**
     * @Route("/mediabuyer/statistic/news-finance/list-ajax", name="mediabuyer_dashboard.statistic.news_finance_list_ajax", methods={"GET"})
     */
    public function newsFinanceListAjaxAction()
    {
        $draw = $this->request->query->get('draw');
        $start = $this->request->query->get('start');
        $dataCount = $this->getNewsFinanceCount($this->getUser(), $this->request);
        $length = $this->request->query->get('length') == -1 ? $dataCount : $this->request->query->get('length');
        $data = $this->getNewsFinance($this->getUser(), $length, $start, $this->request);

        return JsonResponse::create([
            'draw' => $draw,
            'recordsTotal' => $dataCount,
            'recordsFiltered' => $dataCount,
            'data' => $this->getDataJson($data)
        ], 200);
    }
}
