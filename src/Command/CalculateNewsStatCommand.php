<?php
namespace App\Command;

use App\Entity\Conversions;
use App\Entity\NewsClickShortToFull;
use App\Entity\Visits;
use App\Service\CronHistoryChecker;
use App\Entity\News;
use App\Entity\NewsClick;
use App\Entity\ShowNews;
use App\Entity\StatisticNews;
use App\Entity\StatisticPromoBlockNews;
use App\Entity\TeasersClick;
use App\Entity\ConversionStatus;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateNewsStatCommand extends CalculateNewsStatBase
{
    public EntityManagerInterface $entityManager;

    const CRON_HISTORY_SLUG = 'news-stat';

    protected function configure()
    {
        $this
            ->setName('app:news-stat:calculate')
            ->setDescription('Расчет данных глобальной статистики по новостям')
            ->setHelp('Эта команда рассчитывает данные глобальной статистики по новостям');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startTime = new Carbon();
        foreach ($this->getNews() as $newsItem) {
            if (
                ($this->isDeleted($newsItem))
                &&
                ($this->isOlderThen24Hours($newsItem))
            ) {
                continue;
            }

            /** @var News $news */
            $news = $this->entityManager->getRepository(News::class)->find($newsItem['id']);
            $statisticNews = $this->entityManager->getRepository(StatisticNews::class)->findOneBy(['news' => $newsItem['id'], 'mediabuyer' => null]);

            $innerShowCount = $this->getInnerShowCount($newsItem);
            $innerClickCount = $this->getInnerClickCount($newsItem);
            $innerCTR = $this->calculateStatistic->calculateCTR($innerClickCount, $innerShowCount);
            $approvedConversionsCount = $this->getApprovedConvertionsCount($newsItem['id']);
            $approvedConversionsAmountSummInInnerClicks = $this->getApprovedConvertionsAmountSumInnerClicks($newsItem);
            $approvedConversionsAmountSumm = $this->getApprovedConvertionsAmountSum($newsItem);
            $innerECPM = $this->calculateStatistic->calculateECPM($approvedConversionsAmountSummInInnerClicks, $innerShowCount);
            $allConversionsCount = $this->getAllConvertionsCount($newsItem['id']);
            $showNewsCount = $this->getShowNewsCount($newsItem);
            $uniqVisitsCount = $this->entityManager->getRepository(Visits::class)->getUniqueVisitsCountNews($news);
            $teasersClickCount = $this->getTeasersClickCount($newsItem);
            $probiv = $this->calculateStatistic->calculateProbiv($teasersClickCount, $uniqVisitsCount);
            $approve = $this->calculateStatistic->calculateApprove($approvedConversionsCount, $allConversionsCount);
            $epc = $this->calculateStatistic->calculateEPC($approvedConversionsAmountSumm, $uniqVisitsCount);
            $cr = $this->calculateStatistic->calculateCR($allConversionsCount, $uniqVisitsCount);
            $involvement = $this->calculateStatistic->calculateInvolvement(
                $this->entityManager->getRepository(NewsClickShortToFull::class)->getCountClick($newsItem['id']),
                $showNewsCount
            );

            if (null === $statisticNews) {
                $statisticNews = new StatisticNews();
                $statisticNews->setNews($news);
                $statisticNews->setMediabuyer(null);
            }

            $statisticNews->setInnerShow($innerShowCount);
            $statisticNews->setInnerClick($innerClickCount);
            $statisticNews->setInnerCTR(round($innerCTR, 4));
            $statisticNews->setInnerECPM(round($innerECPM, 4));
            $statisticNews->setClick($showNewsCount);
            $statisticNews->setUniqVisits($uniqVisitsCount);
            $statisticNews->setClickOnTeaser($teasersClickCount);
            $statisticNews->setProbiv(round($probiv, 4));
            $statisticNews->setConversion($allConversionsCount);
            $statisticNews->setApproveConversion($approvedConversionsCount);
            $statisticNews->setApprove($approve);
            $statisticNews->setEPC(round($epc, 4));
            $statisticNews->setCR(round($cr, 4));
            $statisticNews->setInvolvement(round($involvement, 4));

            $this->entityManager->persist($statisticNews);

        }

        $this->entityManager->flush();

        $cronHistoryChecker = new CronHistoryChecker($this->entityManager);
        $endTime = new Carbon();
        $cronHistoryChecker->create(self::CRON_HISTORY_SLUG, $startTime->floatDiffInSeconds($endTime));

        return 0;
    }

    private function getNews()
    {
        $query = $this->entityManager->createQueryBuilder('n')
            ->select('n.id, n.is_deleted, n.isActive, n.updatedAt')
            ->from(News::class, 'n')
            ->getQuery();

        return $query->getResult();
    }

    private function getInnerShowCount($newsItem)
    {
        $innerShow = $this->entityManager->createQueryBuilder('spbn')
            ->select('count(spbn.id)')
            ->from(StatisticPromoBlockNews::class, 'spbn')
            ->where('spbn.news = :newsItem')
            ->setParameters([
                'newsItem' => $newsItem['id']
            ])
            ->getQuery();

        return $innerShow->getSingleScalarResult();
    }

    private function getInnerClickCount($newsItem)
    {
        $innerShow = $this->entityManager->createQueryBuilder('nc')
            ->select('count(nc.id)')
            ->from(NewsClick::class, 'nc')
            ->where('nc.news = :newsItem')
            ->setParameters([
                'newsItem' => $newsItem['id']
            ])
            ->getQuery();

        return $innerShow->getSingleScalarResult();
    }

    private function getAllConvertionsCount($newsItem)
    {
        $query = $this->entityManager->createQueryBuilder('tc')
            ->select('count(c.id)')
            ->from(TeasersClick::class, 'tc')
            ->leftJoin(Conversions::class,  'c', 'WITH', 'tc.id = c.teaserClick')
            ->where('tc.news = :newsItem')
            ->setParameters([
                'newsItem' => $newsItem
            ])
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    private function getApprovedConvertionsCount($newsItem)
    {
        $status = $this->entityManager->getRepository(ConversionStatus::class)
            ->findOneBy(['label_en' => 'approved']);

        $query = $this->entityManager->createQueryBuilder('tc')
            ->select('count(c.id)')
            ->from(TeasersClick::class, 'tc')
            ->leftJoin(Conversions::class,  'c', 'WITH', 'tc.id = c.teaserClick')
            ->where('tc.news = :newsItem')
            ->andWhere('c.status = :status')
            ->setParameters([
                'newsItem' => $newsItem,
                'status' => $status,
            ])
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    private function getAllConvertionsAmountSum($newsItem)
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('sum(c.amountRub)')
            ->from(TeasersClick::class, 'tc')
            ->leftJoin(Conversions::class,  'c', 'WITH', 'tc.id = c.teaserClick')
            ->where('tc.news = :newsItem')
            ->setParameters([
                'newsItem' => $newsItem['id'],
            ])
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    private function getApprovedConvertionsAmountSumInnerClicks($newsItem)
    {
        $status = $this->entityManager->getRepository(ConversionStatus::class)
            ->findOneBy(['label_en' => 'approved']);

        $query = $this->entityManager->createQueryBuilder()
            ->select('sum(c.amountRub)')
            ->from(NewsClick::class, 'nc')
            ->leftJoin(TeasersClick::class,  'tc', 'WITH', 'tc.uuid = nc.uuid')
            ->leftJoin(Conversions::class,  'c', 'WITH', 'c.teaserClick = tc.id')
            ->where('nc.news = :newsItem')
            ->andWhere('tc.news = :newsItem')
            ->andWhere('c.status = :status')
            ->setParameters([
                'newsItem' => $newsItem['id'],
                'status' => $status,
            ])
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    private function getApprovedConvertionsAmountSum($newsItem)
    {
        $status = $this->entityManager->getRepository(ConversionStatus::class)
            ->findOneBy(['label_en' => 'approved']);

        $query = $this->entityManager->createQueryBuilder()
            ->select('sum(c.amountRub)')
            ->from(TeasersClick::class,  'tc')
            ->leftJoin(Conversions::class,  'c', 'WITH', 'c.teaserClick = tc.id')
            ->where('tc.news = :newsItem')
            ->andWhere('c.news = :newsItem')
            ->andWhere('c.status = :status')
            ->setParameters([
                'newsItem' => $newsItem['id'],
                'status' => $status,
            ])
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    private function getShowNewsCount($newsItem)
    {
        $query = $this->entityManager->createQueryBuilder('sn')
            ->select('count(sn.id)')
            ->from(ShowNews::class, 'sn')
            ->where('sn.news = :newsItem')
            ->setParameters([
                'newsItem' => $newsItem['id'],
            ])
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    private function getTeasersClickCount($newsItem)
    {
        $query = $this->entityManager->createQueryBuilder('tc')
            ->select('count(tc.id)')
            ->from(TeasersClick::class, 'tc')
            ->where('tc.news = :newsItem')
            ->setParameters([
                'newsItem' => $newsItem['id'],
            ])
            ->getQuery();

        return $query->getSingleScalarResult();
    }
}