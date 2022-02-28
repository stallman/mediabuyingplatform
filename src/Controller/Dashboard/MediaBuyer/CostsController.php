<?php

namespace App\Controller\Dashboard\MediaBuyer;

use App\Controller\Dashboard\DashboardController;
use App\Controller\Dashboard\Traits\BulkActionsTrait;
use App\Entity\Costs;
use App\Entity\CurrencyList;
use App\Entity\CurrencyRate;
use App\Repository\CurrencyRateRepository;
use App\Service\CurrencyConverter;
use App\Service\ImageProcessor;
use App\Traits\Dashboard\CostsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Carbon\CarbonPeriod;
use Exception;
use App\Service\CostDistributor;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Yaml\Yaml;

class CostsController extends DashboardController
{
    use CostsTrait;
    use BulkActionsTrait;

    public string $candidate = 'campaign'; // news/campaign
    private ValidatorInterface $validator;
    private CurrencyRateRepository $crRepo;

    protected const SESSION_FORM_DATA = 'form_data';

    public function __construct(Yaml $yaml, EntityManagerInterface $entityManager, SluggerInterface $slugger, ImageProcessor $imageProcessor, LoggerInterface $logger, CurrencyConverter $currencyConverter, SessionInterface $session, ValidatorInterface $validator, CurrencyRateRepository $crRepo)
    {
        parent::__construct($yaml, $entityManager, $slugger, $imageProcessor, $logger, $currencyConverter, $session);
        $this->validator = $validator;
        $this->crRepo = $crRepo;

    }

    /**
     * @Route("/mediabuyer/costs/list", name="mediabuyer_dashboard.costs_list")
     */
    public function listAction()
    {
        return $this->render('dashboard/mediabuyer/costs/list.html.twig', [
            'currencyList' => $this->entityManager->getRepository(CurrencyList::class)->findAll(),
            'columns' => $this->getCostsTableHeader(),
            'h1_header_text' => 'Все расходы',
            'new_button_label' => 'Добавить расход',
            'new_button_action_link' => $this->generateUrl('mediabuyer_dashboard.cost_add'),
        ]);
    }

    /**
     * @Route("/mediabuyer/cost/add", name="mediabuyer_dashboard.cost_add")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws Exception
     */
    public function addAction(Request $request)
    {
        $form = $this->createCostsForm($this->candidate, null, ['form_data' => $this->pullTempFormData()]);

        if($form->isSubmitted() && $form->isValid()){
            try{
                /** @var Costs $formData */
                $formData = $form->getData();

                if ($form->get('date_from')->getData() > $form->get('date_to')->getData()) {
                    throw new Exception($this->getFlashMessage('cost_add_date_from_greater_than_date_to_error'));
                }

                $period = CarbonPeriod::create(
                    $form->get('date_from')->getData(),
                    $form->get('date_to')->getData()
                );

                if (count($period) > 31) {
                    throw new Exception($this->getFlashMessage('cost_add_over_date_range_error'));
                }

                $costs_added_count = 0;
                $costs_total_count = 0;

                //На каждый день, новость/кампания и источник должна быть отдельная запись
                foreach ($period->toArray() as $dateItem) {
                    foreach ($form->get($this->candidate)->getData() as $candidateItem) {
                        foreach ($form->get('source')->getData() as $sourceItem) {
                            $costs_total_count++;
                            $cost = $this->prepareCost($this->candidate, $candidateItem, $sourceItem, $formData, $dateItem);

                            $violations = $this->validator->validate($cost, [
                                new UniqueEntity([
                                    'fields' => [$this->candidate, 'source', 'date'],
                                ])
                            ]);
                            if($violations->count() == 0){
                                $this->entityManager->persist($cost);
                                $this->entityManager->flush();
                                $costs_added_count++;
                            } else {
                                $candidateTitle = $this->candidate == 'news' ? $cost->getNews()->getTitle() : $cost->getCampaign() ;
                                $this->addFlash('error', $this->getFlashMessage('cost_exists', [
                                    $candidateTitle,
                                    $cost->getSource()->getTitle(),
                                    $cost->getDate()->format('d.m.Y')
                                ]));
                            }
                        }
                    }

                }

                //if successful show the message and clean up the form data fields
                if($costs_added_count) {
                    $this->addFlash('success', $this->getFlashMessage('cost_add_added', [$costs_added_count, $costs_total_count]));
                }
                $this->pushTempFormData($request, ['costs._token', 'costs.save', 'costs.campaign', 'costs.cost']);

                return $this->redirectToRoute('mediabuyer_dashboard.cost_add', []);
            } catch(\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }

        }

        return $this->render('dashboard/mediabuyer/costs/form.html.twig', [
            'h1_header_text' => 'Добавить расход',
            'form' => $form->createView(),
        ]);
    }

    private function prepareCost($candidate, $candidateItem, $sourceItem, $formData, $dateItem) {
        if ($candidate == 'news') {
            $newsItem = $candidateItem;
            $campaignItem = null;
        } else {
            $newsItem = null;
            $campaignItem = $candidateItem;
        }

        /**
         * update currency rate first
         */
        $costRub = $this->getCostAmountRub($formData, $dateItem->toDateString());

        /**
         * convert usd to rub, if usd choosen
         */
        $costVal = $formData->getCost();


        $cost = new Costs();
        $cost->setMediabuyer($this->getUser())
            ->setNews($newsItem)
            ->setCampaign($campaignItem)
            ->setSource($sourceItem)
            ->setCurrency($formData->getCurrency())
            ->setDate($dateItem)
            ->setCost($costVal)
            ->setCostRub($costRub);

        return $cost;
    }

    private function getCostAmountRub(Costs $cost, string $date): float
    {
        if($cost->getCurrency()->getIsoCode() == 'rub') return $cost->getCost();

        $rate = [
            'usd' => $this->entityManager->getRepository(CurrencyRate::class)->getRateByCode('usd', $date),
            'uah' => $this->entityManager->getRepository(CurrencyRate::class)->getRateByCode('uah', $date),
            'eur' => $this->entityManager->getRepository(CurrencyRate::class)->getRateByCode('eur', $date),
        ];

        return floatval($cost->getCost() * $rate[$cost->getCurrency()->getIsoCode()]);
    }

    private function getCostAmountCode(Costs $cost, string $date, string $code = 'rub'): string
    {

        $rate = [
            'rub' => 1.0000,
            'usd' => $this->entityManager->getRepository(CurrencyRate::class)->getRateByCode('usd', $date),
            'uah' => $this->entityManager->getRepository(CurrencyRate::class)->getRateByCode('uah', $date),
            'eur' => $this->entityManager->getRepository(CurrencyRate::class)->getRateByCode('eur', $date),
        ];

        if (!isset($rate[$code])) {
            return 0.0000;
        }

        // eur/uah
        $baseRate = $rate[$cost->getCurrency()->getIsoCode()];
        $targetRate = $rate[$code];
        $codeRate = $baseRate / $targetRate;

        return number_format(((float)$cost->getCost()) * $codeRate, 4, '.', '');
    }

    /**
     * @param Costs $cost
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/mediabuyer/costs/edit/{id}", name="mediabuyer_dashboard.costs_edit")
     */
    public function editAction(Costs $cost)
    {
        $form = $this->createCostsForm($this->candidate, $cost);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->changeIsFinal($form->getData());
                $this->entityManager->flush();
                $this->addFlash('success', $this->getFlashMessage('costs_edit'));

                return $this->redirectToRoute('mediabuyer_dashboard.costs_list', []);
            } else {
                $this->addFlash('error', $this->getFlashMessage('costs_edit_error'));
            }
        }

        return $this->render('dashboard/mediabuyer/costs/form.html.twig', [
            'h1_header_text' => 'Редактировать расход',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mediabuyer/costs/list-ajax", name="mediabuyer_dashboard.costs_list_ajax", methods={"GET"})
     */
    public function listAjaxAction()
    {
        $draw = $this->request->query->get('draw');
        $start = $this->request->query->get('start');
        $costsCount = $this->entityManager->getRepository(Costs::class)->getCostsCount($this->getUser());
        $length = $this->request->query->get('length') == -1 ? $costsCount : $this->request->query->get('length');
        $order = $this->request->query->get('order');

        return JsonResponse::create([
            'draw' => $draw,
            'recordsTotal' => $costsCount,
            'recordsFiltered' => $costsCount,
            'data' => $this->getCostsList($this->entityManager->getRepository(Costs::class)->getCostsPaginateList($this->candidate, $this->getUser(), $length, $start, $order))
        ], 200);
    }

    /**
     * @Route("/mediabuyer/costs/mass-edit", name="mediabuyer_dashboard.costs_mass_edit", methods={"POST"})
     */
    public function costsMassEdit()
    {
        $summ = $this->request->request->get('summ');
        $ids = $this->request->request->get('ids');
        $currency = $this->entityManager->getRepository(CurrencyList::class)->find($this->request->request->get('currency'));
        $newsSourcesDates = $this->getNewsSourcesDates($ids);

        //Предварительно обнуляем данные для тех расходов, у которых отсутствуют визиты ( у остальных расходы будут переписаны с помошью сервиса)
        $this->costsToZero($ids, $currency);

        $costDistributor = new CostDistributor(
            $this->entityManager,
            $this->getUser(),
            $summ,
            $currency,
            array_filter(explode(',', $newsSourcesDates['news_ids'])),
            array_filter(explode(',', $newsSourcesDates['campaigns'])),
            explode(',', $newsSourcesDates['source_ids']),
            $newsSourcesDates['min_date'],
            $newsSourcesDates['max_date'],
        );

        try {

            $this->validateForm($summ, $ids);
            $costDistributor->distribute();

            return JsonResponse::create([
                'success' => 1
            ], 200);
        } catch (Exception $e) {

            return JsonResponse::create([
                'success' => 0,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @Route("/mediabuyer/costs/bulk-delete", name="mediabuyer_dashboard.costs_bulk_delete", methods={"POST"})
     *
     * @return mixed
     */
    public function bulkDeleteAction()
    {
        $checkedItems = $this->request->request->get('checkedItems');
        $statusCode = 200;

        try {
            foreach ($checkedItems as $costId) {
                $cost = $this->entityManager->getRepository(Costs::class)->find(intval($costId));
                $this->entityManager->remove($cost);
            }
            $this->entityManager->flush();
            $this->addFlash('success', $this->getFlashMessage('costs_delete'));
        } catch (\Exception $exception) {
            $statusCode = 500;
            $this->addFlash('success', $this->getFlashMessage('costs_delete_error', [$exception->getMessage()]));
        }

        return JsonResponse::create(['route_to_redirect' => $this->generateUrl('mediabuyer_dashboard.costs_list')], $statusCode);
    }

    private function costsToZero($ids, $currency)
    {
        $query = $this->entityManager->getRepository(Costs::class)
            ->createQueryBuilder('q')
            ->update(Costs::class, 'q')
            ->set('q.cost', ':cost')
            ->set('q.currency', ':currency')
            ->where('q.id IN (:ids)')
            ->setParameters([
                'cost' => "0",
                'ids' => explode(',', $ids),
                'currency' => $currency
            ])
            ->getQuery();

        $query->execute();
    }

    private function getNewsSourcesDates($ids)
    {
        $sql = "select group_concat(news_id) as news_ids, group_concat(campaign) as campaigns, group_concat(source_id) as source_ids, min(date_set_data) as min_date, max(date_set_data) as max_date from costs where id in (" . $ids .  ");";
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll()[0];
    }

    private function validateForm($summ, $ids)
    {
        $this->validateSumm($summ);
        $this->validateIds($ids);
    }

    private function validateSumm($summ)
    {
        if (!is_numeric($summ) || $summ == "") {
            throw new Exception('Некорректное значение в поле суммы');
        }

        if (strpos($summ, '.')) {
            $explodedSumm = explode('.', $summ);
            if (strlen($explodedSumm[0]) > 4 || strlen($explodedSumm[1]) > 5) {
                throw new Exception('Недопустимое кол-во символов в поле суммы');
            }
        } else {
            if (strlen($summ) > 5) {
                throw new Exception('Недопустимое кол-во символов в поле суммы');
            }
        }

    }

    private function validateIds($ids)
    {
        if (empty($ids)) {
            throw new Exception('Не выбран ни один расчёт');
        }
    }

    /**
     * Save request data to session to show it later if needed
     * @param Request $request
     * @param array $exclude list of the keys to forget in dot notation, like _token and other fields
     */
    private function pushTempFormData(Request $request, array $exclude = []){
        if (!$this->container->has('session')) {
            throw new \LogicException('You can not use the pushTempFormData method if sessions are disabled.');
        }

        $data = $request->request->all();
        array_forget($data, $exclude);
        $this->container->get('session')->set(static::SESSION_FORM_DATA, $data);
    }

    /**
     * Get saved request data from session
     * @return mixed
     */
    private function pullTempFormData(){
        if (!$this->container->has('session')) {
            throw new \LogicException('You can not use the pushTempFormData method if sessions are disabled.');
        }

        return $this->container->get('session')->remove(static::SESSION_FORM_DATA);
    }
}
