<?php


namespace App\Command;

use App\Entity\Conversions;
use App\Entity\Country;
use App\Entity\News;
use App\Entity\StatisticPromoBlockNews;
use App\Entity\TeasersClick;
use App\Entity\User;
use App\Service\CalculateStatistic;
use App\Service\CronHistoryChecker;
use App\Traits\CacheActionsTrait;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class CalculateNewsECPMCommand extends Command
{
    use CacheActionsTrait;

    const TRAFFIC_TYPE = ['desktop', 'mobile', 'tablet'];
    const CRON_HISTORY_SLUG = 'news-ecpm';

    public EntityManagerInterface $entityManager;
    public LoggerInterface $logger;
    public CalculateStatistic $calculateStatistic;
    public TagAwareAdapter $cache;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, CalculateStatistic $calculateStatistic)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->calculateStatistic = $calculateStatistic;
        $this->cache = new TagAwareAdapter(new RedisAdapter(
            RedisAdapter::createConnection($_ENV['REDIS_URL'])
        ));;
    }


    protected function configure()
    {
        $this->setName('app:news-ecpm:calculate');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $cronHistoryChecker = new CronHistoryChecker($this->entityManager);
        $startTime = new Carbon();

        $news = $this->entityManager->getRepository(News::class)->getActiveNews();
        $countries = $this->entityManager->getRepository(Country::class)->findAll();

        /** @var News $newsItem */
        foreach($news as $newsItem) {
            /** @var User $mediaBuyer */
            foreach($this->getMediabuyerUsers() as $mediaBuyer) {
                /** @var Country $country */
                foreach($countries as $country) {
                    /** @var string $trafficType */
                    foreach(self::TRAFFIC_TYPE as $trafficType) {
                        $showNews = $this->entityManager->getRepository(StatisticPromoBlockNews::class)->getNewsShowCountByNews($newsItem, $mediaBuyer, $country->getIsoCode(), $trafficType);
                        $showNews = $showNews ? $showNews : 1;
                        $income = $this->calculateIncome($newsItem, $mediaBuyer, $trafficType, $country);
                        $this->setTopNews(
                            $newsItem->getId(),
                            $mediaBuyer->getId(),
                            $country->getIsoCode(),
                            $trafficType,
                            $this->calculateStatistic->calculateECPM($income, $showNews),
                            (int)$showNews
                        );
                    }
                }
            }
        }

        $this->clearCacheByTags($this->cache, ['news'], 'entity-');
        $endTime = new Carbon();
        $cronHistoryChecker->create(self::CRON_HISTORY_SLUG, $startTime->floatDiffInSeconds($endTime));

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

    private function setTopNews(int $newsId, int $buyerId, string $geoCode, string $trafficType, float $eCPM, int $impressions)
    {
        try{
            $sql =
                "INSERT INTO top_news (news_id, mediabuyer_id, geo_code, traffic_type, e_cpm, impressions) 
                             VALUES ({$newsId}, {$buyerId}, '$geoCode', '$trafficType', {$eCPM}, {$impressions})
                             ON DUPLICATE KEY UPDATE e_cpm={$eCPM}, impressions={$impressions}";
            $stmt = $this->entityManager->getConnection()->prepare($sql);
            $stmt->execute();
        } catch(\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    private function calculateIncome(News $news, User $mediaBuyer, string $trafficType, Country $country)
    {
        $income = 0;
        $clicks = $this->entityManager->getRepository(TeasersClick::class)->getByNewsAndTrafficType($news, $mediaBuyer, $trafficType, $country);
        /** @var TeasersClick $click */
        foreach($clicks as $click) {
            $income += $this->entityManager->getRepository(Conversions::class)->getIncomeRub($click, $country, $mediaBuyer);
        }

        return $income;
    }
}
