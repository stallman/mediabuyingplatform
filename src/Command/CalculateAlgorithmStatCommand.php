<?php

namespace App\Command;

use App\Entity\Algorithm;
use App\Entity\AlgorithmsAggregatedStatistics;
use App\Entity\ConversionStatus;
use App\Entity\User;
use App\Service\CalculateStatistic;
use App\Service\CronHistoryChecker;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateAlgorithmStatCommand extends Command
{
    const SLUG = 'aggregate-algr-stat';
    public EntityManagerInterface $entityManager;
    public LoggerInterface $logger;
    public CalculateStatistic $calculateStatistic;
    public int $algorithmCount;
    public Collection $conversionStatus;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, CalculateStatistic $calculateStatistic)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->calculateStatistic = $calculateStatistic;
        $this->conversionStatus = new ArrayCollection();
    }

    protected function configure()
    {
        $this
            ->setName('app:algorithm-stat:calculate')
            ->setDescription('Расчет данных глобальной статистики по алгоритмам для байера')
            ->setHelp('Эта команда рассчитывает данные глобальной статистики по алгоритмам для байера');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(3000);
        $cronHistoryChecker = new CronHistoryChecker($this->entityManager);
        $startTime = new Carbon();
        $mediaBuyers = $this->getMediabuyerUsers();
        $algorithms = $this->entityManager->getRepository(Algorithm::class)->findAll();
        $this->algorithmCount = count($algorithms);

        $rows = $this->entityManager->getRepository(ConversionStatus::class)->findAll();
        /** @var ConversionStatus $row */
        foreach ($rows as $row) {
            $this->conversionStatus->set($row->getLabelEn(), $row);
        }

        /** @var User $mediaBuyer */
        foreach($mediaBuyers as $mediaBuyer) {
            $this->createOrUpdateAlgorithmStat($mediaBuyer, $algorithms);
        }
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

    private function createOrUpdateAlgorithmStat(User $mediaBuyer, array $algorithms)
    {
        $conn = $this->entityManager->getConnection();
        $buyerId = $mediaBuyer->getId();
        $approvedStatus = $this->conversionStatus->get('approved');
        $approvedId = $approvedStatus->getId();

        /** @var Algorithm $algorithm */
        foreach($algorithms as $algorithm) {
            $algId = $algorithm->getId();
            $conn->close();
            $conn->connect();

            $sql = "select count(*) from teasers_click where buyer_id = $buyerId and algorithm_id = $algId";
            $stmt = $conn->executeQuery($sql);
            $teaserClick = intval($stmt->fetchOne());


            $sql = "select count(*) from conversions where mediabuyer_id = $buyerId and algorithm_id = $algId and is_deleted = 0";
            $stmt = $conn->executeQuery($sql);
            $conversionCount = intval($stmt->fetchOne());


            $sql = "select count(*) from conversions where mediabuyer_id = $buyerId and algorithm_id = $algId and is_deleted = 0 and status_id = $approvedId";
            $stmt = $conn->executeQuery($sql);
            $approveConversionCount = intval($stmt->fetchOne());


            $sql = "select sum(amount_rub) from conversions where mediabuyer_id = $buyerId and algorithm_id = $algId and is_deleted = 0 and status_id = $approvedId";
            $stmt = $conn->executeQuery($sql);
            $amountIncome = intval($stmt->fetchOne());


            $sql = "select count(*) from statistic_promo_block_teasers where mediabuyer_id = $buyerId and algorithm_id = $algId";
            $stmt = $conn->executeQuery($sql);
            $teaserShow = intval($stmt->fetchOne());


            $sql = "select sum(cost_rub) from costs where mediabuyer_id = $buyerId";
            $stmt = $conn->executeQuery($sql);
            $amountCost = intval($stmt->fetchOne());

            $algorithmStat = $this->entityManager->getRepository(AlgorithmsAggregatedStatistics::class)->getAlgorithmBuyerStatistic($algorithm, $mediaBuyer);

            if(!$algorithmStat){
                $algorithmStat = new  AlgorithmsAggregatedStatistics();
                $algorithmStat->setAlgorithm($algorithm)
                    ->setMediabuyer($mediaBuyer);
            }

            try{
                $ctr = round($this->calculateStatistic->calculateCTR($teaserClick, $teaserShow), $precision = 4);
                $maxCtr = 99999.9999;
                $ctr = $ctr > $maxCtr ? $maxCtr : $ctr ;

                $cr = $this->calculateStatistic->calculateCR((int)$conversionCount, (int)$teaserClick, 4);
                $maxCr = 9999.9999;
                $cr = $cr > $maxCr ? $maxCr : $cr ;

                $roi = $this->calculateStatistic->calculateROI($amountIncome, $amountCost);
                $maxRoi = 999.9999;
                $roi = $roi > $maxRoi ? $maxRoi : $roi ;

                $algorithmStat->setCTR($ctr)
                    ->setConversion($conversionCount ? $conversionCount : 0)
                    ->setApproveConversion($approveConversionCount ? $approveConversionCount : 0)
                    ->setEPC($this->calculateStatistic->calculateEPC($amountIncome, $teaserClick))
                    ->setCR($cr)
                    ->setECPM($this->calculateStatistic->calculateECPM($amountIncome, $teaserShow))
                    ->setROI($roi);

                $this->reconnect();

                if(!$algorithmStat->getId()) $this->entityManager->persist($algorithmStat);
                $this->entityManager->flush();

            } catch(\Exception $exception) {
                $this->logger->error($exception->getMessage());
            }
        }
    }

    private function reconnect()
    {
        if (!$this->entityManager->isOpen()) {
            $this->entityManager = $this->entityManager->create(
                $this->entityManager->getConnection(),
                $this->entityManager->getConfiguration()
            );
        }
    }
}