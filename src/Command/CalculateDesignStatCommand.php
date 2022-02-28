<?php

namespace App\Command;

use App\Entity\Conversions;
use App\Entity\Costs;
use App\Entity\Design;
use App\Entity\DesignsAggregatedStatistics;
use App\Entity\StatisticPromoBlockTeasers;
use App\Entity\TeasersClick;
use App\Entity\User;
use App\Entity\Visits;
use App\Service\CalculateStatistic;
use App\Service\CronHistoryChecker;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateDesignStatCommand extends Command
{
    const SLUG = 'aggregate-dsgn-stat';
    public EntityManagerInterface $entityManager;
    public LoggerInterface $logger;
    public CalculateStatistic $calculateStatistic;
    public int $designCount;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, CalculateStatistic $calculateStatistic)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->calculateStatistic = $calculateStatistic;
    }

    protected function configure()
    {
        $this
            ->setName('app:design-stat:calculate')
            ->setDescription('Расчет данных глобальной статистики по дизайнам для байера')
            ->setHelp('Эта команда рассчитывает данные глобальной статистики по дизайнам для байера');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startTime = new Carbon();
        $mediaBuyers = $this->getMediabuyerUsers();
        $designs = $this->entityManager->getRepository(Design::class)->findAll();
        $this->designCount = count($designs);

        /** @var User $mediaBuyer */
        foreach($mediaBuyers as $mediaBuyer) {
            $this->createOrUpdateDesignStat($mediaBuyer, $designs);
        }

        $this->entityManager->flush();

        $cronHistoryChecker = new CronHistoryChecker($this->entityManager);
        $endTime = new Carbon();
        $cronHistoryChecker->create(self::SLUG, $startTime->floatDiffInSeconds($endTime));

        return 0;
    }

    private function getMediabuyerUsers()
    {
        $dql = "SELECT u FROM App\Entity\User u WHERE u.roles LIKE :role";
        return $this->entityManager
            ->createQuery($dql)
            ->setParameter(
                'role', '%ROLE_MEDIABUYER%'
            )
            ->getResult();
    }

    private function createOrUpdateDesignStat(User $mediaBuyer, array $designs)
    {
        /** @var Design $design */
        foreach($designs as $design) {
            $teaserClick = $this->entityManager->getRepository(TeasersClick::class)->getTeaserClickCountDesign($mediaBuyer, $design);
            $uniqVisits = $this->entityManager->getRepository(Visits::class)->getUniqueVisitsCountDesign($mediaBuyer, $design);
            $conversionCount = $this->entityManager->getRepository(Conversions::class)->getMediaBuyerConversionsCountByDesign($mediaBuyer, $design);
            $approveConversionCount = $this->entityManager->getRepository(Conversions::class)->getMediaBuyerApproveConversionsCountByDesign($mediaBuyer, $design);
            $teaserShow = $this->entityManager->getRepository(StatisticPromoBlockTeasers::class)->getTeaserShowCountByDesign($mediaBuyer, $design);
            $amountIncome = $this->entityManager->getRepository(Conversions::class)->getAmountIncomeByDesign($mediaBuyer, $design);
            $amountCost = $this->entityManager->getRepository(Costs::class)->getAmountCost($mediaBuyer) / $this->designCount;

            $designStat = $this->entityManager->getRepository(DesignsAggregatedStatistics::class)->getDesignBuyerStatistic($design, $mediaBuyer);
            if(!$designStat){
                $designStat = new DesignsAggregatedStatistics();
                $designStat->setDesign($design)
                    ->setMediabuyer($mediaBuyer);
            }
            try{
                $epc = round($this->calculateStatistic->calculateEPC($amountIncome, $uniqVisits), $precision = 4);
                $ctr = round($this->calculateStatistic->calculateCTR($teaserClick, $teaserShow), $precision = 4);
                $probiv = round($this->calculateStatistic->calculateUniqVisitsProbiv($teaserClick, $uniqVisits), $precision = 4);
                $cr = $this->calculateStatistic->calculateCR((int)$conversionCount, (int)$uniqVisits, 4);

                $designStat
                    ->setProbiv($probiv)
                    ->setCTR($ctr)
                    ->setConversion($conversionCount ? $conversionCount : 0)
                    ->setApproveConversion($approveConversionCount ? $approveConversionCount : 0)
                    ->setEPC($epc)
                    ->setCR($cr)
                    ->setROI($this->calculateStatistic->calculateROI($amountIncome, $amountCost));

                if(!$designStat->getId()) $this->entityManager->persist($designStat);

            } catch(\Exception $exception) {
                $this->logger->error($exception->getMessage());
            }
        }
    }
}