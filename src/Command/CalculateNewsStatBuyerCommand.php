<?php
namespace App\Command;

use App\Entity\NewsClickShortToFull;
use App\Entity\User;
use App\Entity\Visits;
use Carbon\Carbon;
use App\Entity\Conversions;
use App\Service\CronHistoryChecker;
use App\Entity\News;
use App\Entity\NewsClick;
use App\Entity\ShowNews;
use App\Entity\StatisticNews;
use App\Entity\StatisticPromoBlockNews;
use App\Entity\TeasersClick;
use App\Entity\ConversionStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateNewsStatBuyerCommand extends CalculateNewsStatBase
{
    public EntityManagerInterface $entityManager;

    const CRON_HISTORY_SLUG = 'news-stat-buyer';

    protected function configure()
    {
        $this
            ->setName('app:news-stat-buyer:calculate')
            ->setDescription('Расчет данных глобальной статистики по новостям для байера')
            ->setHelp('Эта команда рассчитывает данные глобальной статистики по новостям для байера');
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

            if ($this->isPrivateAndDontShownFor24Hours($newsItem)) {
                continue;
            }

            foreach ($this->getBuyersForNewsInRotation($newsItem) as $buyer) {
                /** @var User $buyer */
                $buyer = $this->entityManager->getRepository(User::class)->find($buyer);
                $innerShowCount = $this->getInnerShowCount($newsItem, $buyer);
                $innerClickCount = $this->getInnerClickCount($newsItem, $buyer);

                if ($innerShowCount == 0 || $innerClickCount == 0) {
                    continue;
                }

                /** @var News $news */
                $news = $this->entityManager->getRepository(News::class)->find($newsItem['id']);

                $innerCTR = $this->calculateStatistic->calculateCTR($innerClickCount, $innerShowCount);
                $approvedConversionsCount = $this->getApprovedConvertionsCount($newsItem['id'], $buyer);
                $approvedConversionsAmountSummInInnerClicks = $this->getApprovedConvertionsAmountSumInnerClicks($newsItem, $buyer);
                $approvedConversionsAmountSumm = $this->getApprovedConvertionsAmountSum($newsItem, $buyer);
                $innerECPM = $this->calculateStatistic->calculateECPM($approvedConversionsAmountSummInInnerClicks, $innerShowCount);
                $allConversionsCount = $this->getAllConvertionsCount($newsItem['id'], $buyer);
                $showNewsCount = $this->getShowNewsCount($newsItem, $buyer);
                $uniqVisitsCount = $this->entityManager->getRepository(Visits::class)->getUniqueVisitsCountNews($news, $buyer);
                $teasersClickCount = $this->getTeasersClickCount($newsItem, $buyer);
                $probiv = $this->calculateStatistic->calculateProbiv($teasersClickCount, $uniqVisitsCount);
                $approve = $this->calculateStatistic->calculateApprove($approvedConversionsCount, $allConversionsCount);
                $epc = $this->calculateStatistic->calculateEPC($approvedConversionsAmountSumm, $uniqVisitsCount);
                $cr = $this->calculateStatistic->calculateCR($allConversionsCount, $uniqVisitsCount);
                $involvement = $this->calculateStatistic->calculateInvolvement(
                    $this->entityManager->getRepository(NewsClickShortToFull::class)->getCountBuyerClick($newsItem['id'], $buyer),
                    $showNewsCount
                );

                $statisticNews = $this->getStatisticNews($newsItem, $buyer);

                if (null === $statisticNews) {
                    $statisticNews = new StatisticNews();
                    $statisticNews->setNews($news);
                    $statisticNews->setMediabuyer($buyer);
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
        }

        $this->entityManager->flush();

        $cronHistoryChecker = new CronHistoryChecker($this->entityManager);
        $endTime = new Carbon();
        $cronHistoryChecker->create(self::CRON_HISTORY_SLUG, $startTime->floatDiffInSeconds($endTime));

        return 0;
    }

    private function isPrivateAndDontShownFor24Hours($newsItem)
    {
        if ($newsItem['type'] == 'own') {
            if (!$this->isStatisticPromoForBuyersExists()) {
                return true;
            }
        }

        return false;
    }

    private function isStatisticPromoForBuyersExists() {
        $query = $this->entityManager->createQueryBuilder('spbn')
                ->select('count(spbn.id)')
                ->from(StatisticPromoBlockNews::class, 'spbn')
                ->where('spbn.id in (:ids)')
                ->andWhere('spbn.createdAt BETWEEN :start AND :end')
                ->setParameters([
                    'ids' => $this->getMediabuyerUserIds(),
                    'start' => Carbon::now()->subHours(24),
                    'end' => Carbon::now()
                ])
                ->getQuery();

        return ($query->getOneOrNullResult()) ? true : false;
    }

    private function getMediabuyerUserIds()
    {
        $sql = "SELECT group_concat(id) as ids FROM users WHERE roles LIKE '%ROLE_MEDIABUYER%'";
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();
        return explode(',' ,$stmt->fetchAll()[0]['ids']);
    }

    private function getBuyersForNewsInRotation($newsItem)
    {
        $sql = "SELECT GROUP_CONCAT(DISTINCT mediabuyer_id) as buyers FROM `mediabuyer_news_rotation` WHERE is_rotation AND news_id = " . $newsItem['id'] . ";";
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();
        return explode(',', $stmt->fetchAll()[0]['buyers']);
    }

    private function getStatisticNews($newsItem, $buyer)
    {
        $query = $this->entityManager->createQueryBuilder('sn')
            ->select('sn')
            ->from(StatisticNews::class, 'sn')
            ->where('sn.news = :news')
            ->andWhere('sn.mediabuyer = :buyer')
            ->setParameters([
                'news' => $newsItem['id'],
                'buyer' => $buyer,
            ])
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    private function getNews()
    {
        $query = $this->entityManager->createQueryBuilder('n')
            ->select('n.id, n.is_deleted, n.isActive, n.updatedAt, n.type')
            ->from(News::class, 'n')
            ->getQuery();

        return $query->getResult();
    }

    private function getInnerShowCount($newsItem, $buyer)
    {
        $innerShow = $this->entityManager->createQueryBuilder('spbn')
            ->select('count(spbn.id)')
            ->from(StatisticPromoBlockNews::class, 'spbn')
            ->where('spbn.news = :newsItem')
            ->andWhere('spbn.mediabuyer = :buyer')
            ->setParameters([
                'newsItem' => $newsItem['id'],
                'buyer' => $buyer,
            ])
            ->getQuery();

        return $innerShow->getSingleScalarResult();
    }

    private function getInnerClickCount($newsItem, $buyer)
    {
        $innerShow = $this->entityManager->createQueryBuilder('nc')
            ->select('count(nc.id)')
            ->from(NewsClick::class, 'nc')
            ->where('nc.news = :newsItem')
            ->andWhere('nc.buyer = :buyer')
            ->setParameters([
                'newsItem' => $newsItem['id'],
                'buyer' => $buyer,
            ])
            ->getQuery();

        return $innerShow->getSingleScalarResult();
    }

    private function getAllConvertionsCount($newsItem, $buyer)
    {
        $query = $this->entityManager->createQueryBuilder('tc')
            ->select('count(c.id)')
            ->from(TeasersClick::class, 'tc')
            ->leftJoin(Conversions::class,  'c', 'WITH', 'tc.id = c.teaserClick')
            ->where('tc.news = :newsItem')
            ->andWhere('c.mediabuyer = :buyer')
            ->setParameters([
                'newsItem' => $newsItem,
                'buyer' => $buyer,
            ])
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    private function getApprovedConvertionsCount($newsItem, $buyer)
    {
        $status = $this->entityManager->getRepository(ConversionStatus::class)
            ->findOneBy(['label_en' => 'approved']);

        $query = $this->entityManager->createQueryBuilder('tc')
            ->select('count(c.id)')
            ->from(TeasersClick::class, 'tc')
            ->leftJoin(Conversions::class,  'c', 'WITH', 'tc.id = c.teaserClick')
            ->where('tc.news = :newsItem')
            ->andWhere('c.status = :status')
            ->andWhere('c.mediabuyer = :buyer')
            ->setParameters([
                'newsItem' => $newsItem,
                'status' => $status,
                'buyer' => $buyer,
            ])
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    private function getAllConvertionsAmountSum($newsItem, $buyer)
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('sum(c.amountRub)')
            ->from(TeasersClick::class, 'tc')
            ->leftJoin(Conversions::class,  'c', 'WITH', 'tc.id = c.teaserClick')
            ->where('tc.news = :newsItem')
            ->andWhere('c.mediabuyer = :buyer')
            ->setParameters([
                'newsItem' => $newsItem['id'],
                'buyer' => $buyer,
            ])
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    private function getApprovedConvertionsAmountSumInnerClicks($newsItem, $buyer)
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
            ->andWhere('c.news = :newsItem')
            ->andWhere('c.status = :status')
            ->andWhere('c.mediabuyer = :buyer')
            ->setParameters([
                'newsItem' => $newsItem['id'],
                'status' => $status,
                'buyer' => $buyer,
            ])
            ->getQuery();

        $result = $query->getSingleScalarResult();

        return floatval($result);
    }

    private function getApprovedConvertionsAmountSum($newsItem, $buyer)
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
            ->andWhere('c.mediabuyer = :buyer')
            ->setParameters([
                'newsItem' => $newsItem['id'],
                'status' => $status,
                'buyer' => $buyer,
            ])
            ->getQuery();

        $result = $query->getSingleScalarResult();

        return floatval($result);
    }


    private function getShowNewsCount($newsItem, $buyer)
    {
        $query = $this->entityManager->createQueryBuilder('sn')
            ->select('count(sn.id)')
            ->from(ShowNews::class, 'sn')
            ->where('sn.news = :newsItem')
            ->andWhere('sn.mediabuyer = :buyer')
            ->setParameters([
                'newsItem' => $newsItem['id'],
                'buyer' => $buyer,
            ])
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    private function getTeasersClickCount($newsItem, $buyer)
    {
        $query = $this->entityManager->createQueryBuilder('tc')
            ->select('count(tc.id)')
            ->from(TeasersClick::class, 'tc')
            ->where('tc.news = :newsItem')
            ->andWhere('tc.buyer = :buyer')
            ->setParameters([
                'newsItem' => $newsItem['id'],
                'buyer' => $buyer,
            ])
            ->getQuery();

        return $query->getSingleScalarResult();
    }
}