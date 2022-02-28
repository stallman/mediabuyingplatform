<?php


namespace App\Command;


use App\Entity\Conversions;
use App\Entity\StatisticPromoBlockTeasers;
use App\Entity\StatisticTeasers;
use App\Entity\Teaser;
use App\Entity\TeasersClick;
use App\Service\CalculateStatistic;
use App\Service\CronHistoryChecker;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TeaserStatisticCommand extends Command
{
    public const SLUG = 'teaser-statistics';
    private EntityManagerInterface $entityManager;
    private CalculateStatistic $calculateStatistic;
    private CronHistoryChecker $cronHistoryChecker;
    private array $teasers;
    private array $teasersStatistic = [];
    private Carbon $startTime;

    public function __construct(EntityManagerInterface $entityManager, CalculateStatistic $calculateStatistic)
    {
        parent::__construct();
        $this->startTime = new Carbon();
        $this->entityManager = $entityManager;
        $this->calculateStatistic = $calculateStatistic;
        $this->cronHistoryChecker = new CronHistoryChecker($this->entityManager);
    }

    public function configure()
    {
        $this
            ->setName('app:teaser:statistics')
            ->setDescription('Command for gathering teaser statistics')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this
            ->countTeaserStatistics()
            ->fillStatisticsTable()
            ->createCronHistoryRecord()
        ;

        return 0;
    }

    /**
     * @return $this
     */
    private function countTeaserStatistics()
    {
        $this
            ->getTeasers()
            ->countTeaserShows()
            ->countTeaserClicks()
            ->countTeasersConversion()
            ->countTeasersApprovedConversions()
            ->amountRubTeasersApprovedConversions()
            ->countApproveCoefficient()
            ->countECPM()
            ->countEPC()
            ->countCTR()
            ->countCR()
        ;

        return $this;
    }

    /**
     * @return $this
     */
    private function fillStatisticsTable()
    {
        foreach ($this->teasersStatistic as $key => $teaserStatistic) {
            $teaser = $this->entityManager->getRepository(Teaser::class)->findOneBy(['id' => $key]);

            $statistic = $this->entityManager->getRepository(StatisticTeasers::class)->findOneBy(['teaser' => $teaser]);
            if(!$statistic) {
                $statistic = new StatisticTeasers();
                $statistic->setTeaser($teaser);
            }
            $statistic
                ->setApproveConversion($teaserStatistic['approve_conversion_count'] )
                ->setApprove($teaserStatistic['approve_coefficient'])
                ->setClick($teaserStatistic['clicks_count'])
                ->setConversion($teaserStatistic['conversions_count'])
                ->setCR($teaserStatistic['CR'])
                ->setCTR($teaserStatistic['CTR'])
                ->setEPC($teaserStatistic['EPC'])
                ->setECPM($teaserStatistic['eCPM'])
                ->setTeaserShow($teaserStatistic['shows_count'])
            ;

            if(!$statistic->getId()) $this->entityManager->persist($statistic);
            $this->entityManager->flush();
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function createCronHistoryRecord()
    {
        $this->cronHistoryChecker->create(self::SLUG, $this->startTime->floatDiffInSeconds(new Carbon()));

        return $this;
    }

    /**
     * @return $this
     */
    private function getTeasers()
    {
        $this->teasers = $this->entityManager->getRepository(Teaser::class)->getTeasersStatisticData();

        return $this;
    }

    /**
     * @return $this
     */
    private function countTeaserShows()
    {
        foreach ($this->teasers as $teaser) {
            $showsCountPerTeaser = $this->entityManager->getRepository(StatisticPromoBlockTeasers::class)->countById(intval($teaser['id']));
            $this->teasersStatistic[$teaser['id']]['shows_count'] = $showsCountPerTeaser;
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function countTeaserClicks()
    {
        foreach ($this->teasers as $teaser) {
            $clickCountPerTeaser = $this->entityManager->getRepository(TeasersClick::class)->findBy(['teaser' => $teaser['id']]);
            $this->teasersStatistic[$teaser['id']]['clicks_count'] = count($clickCountPerTeaser);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function countTeasersConversion()
    {
        foreach ($this->teasers as $teaser) {
            $conversionCount = $this->entityManager->getRepository(Conversions::class)->countConversionsByTeaserId($teaser['id']);
            $this->teasersStatistic[$teaser['id']]['conversions_count'] = count($conversionCount);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function countTeasersApprovedConversions()
    {
        foreach ($this->teasers as $teaser) {
            $approvedConversionCount = $this->entityManager->getRepository(Conversions::class)->countApprovedConversionsId($teaser['id']);
            $this->teasersStatistic[$teaser['id']]['approve_conversion_count'] = count($approvedConversionCount);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function amountRubTeasersApprovedConversions()
    {
        foreach ($this->teasers as $teaser) {
            $approvedConversionAmount = $this->entityManager->getRepository(Conversions::class)->getAmountIncomeByTeaser($teaser['id']);
            $this->teasersStatistic[$teaser['id']]['amount_rub'] = $approvedConversionAmount;
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function countApproveCoefficient()
    {
        foreach ($this->teasersStatistic as &$item)
        {
            $item['approve_coefficient'] = $this->calculateStatistic->calculateApprove($item['approve_conversion_count'], $item['conversions_count']);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function countECPM()
    {
        foreach ($this->teasersStatistic as &$item)
        {
            $item['eCPM'] = $this->calculateStatistic->calculateECPM($item['amount_rub'], $item['shows_count']);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function countEPC()
    {
        foreach ($this->teasersStatistic as &$item)
        {
            $item['EPC'] = $this->calculateStatistic->calculateEPC($item['amount_rub'], $item['clicks_count']);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function countCTR()
    {
        foreach ($this->teasersStatistic as &$item)
        {
            $item['CTR'] = $this->calculateStatistic->calculateCTR($item['clicks_count'], $item['shows_count']);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function countCR()
    {
        foreach ($this->teasersStatistic as &$item)
        {
            $item['CR'] = $this->calculateStatistic->calculateCR($item['conversions_count'], $item['clicks_count']);
        }

        return $this;
    }
}