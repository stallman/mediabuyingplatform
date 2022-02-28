<?php

namespace App\DataFixtures;

use App\Entity\Algorithm;
use App\Entity\Country;
use App\Entity\Design;
use App\Entity\Sources;
use App\Entity\DomainParking;
use App\Entity\Visits;
use App\Entity\User;
use App\Entity\News;
use App\Entity\NewsCategory;
use App\Entity\NewsClickShortToFull;
use App\Entity\Partners;
use App\Entity\TeasersClick;
use App\Entity\Postback;
use App\Traits\TimeInformationTrait;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use Symfony\Component\Filesystem\Filesystem;
use Ramsey\Uuid\Uuid;
use App\Traits\DeviceTrait;
use UAParser\Parser;
use App\Traits\Dashboard\DateRangeTrait;
use App\Service\Algorithms\AlgorithmBuilder;
use Carbon\Carbon;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class RealisticDataFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use DeviceTrait;
    use DateRangeTrait;
    use TimeInformationTrait;

    const DATE_FROM = '2020-02-06 17:54:28';
    const DATE_TO = '2020-08-06 17:54:28';
    const SCREEN_ELEMENTS_COUNT = 9;
    const CHANCE_OF_TEASERS_CLICK_1 = 20;
    const CHANCE_OF_TEASERS_CLICK_2 = 20;
    const CHANCE_OF_TEASERS_CLICK_3 = 5;
    const CHANCE_OF_TEASERS_CLICK_4 = 10;
    const CHANCE_OF_NEWS_CLICK_SHORT_TO_FULL = 80;
    const CHANCE_OF_POSTBACK_APPROVED = 50;
    const CHANCE_OF_POSTBACK_PENDING_OR_DECLINED = 70;

    /** @var EntityManagerInterface */
    public $entityManager;

    public $faker;

    public AlgorithmBuilder $algorithmBuilder;

    const VISITS_COUNT = 0;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->faker = Faker\Factory::create();
        $this->filesystem = new Filesystem();
        $this->algorithmBuilder = new AlgorithmBuilder();
        $this->randomAlgorythm = $this->algorithmBuilder->getInstance(1);
    }

    public function load(ObjectManager $manager)
    {
        for($i = 0; $i < self::VISITS_COUNT; $i++) {
            $visitStartTime = new Carbon();
            //Генерируем одну запись визита на страницу короткой новости
            $visit = $this->createVisit();
            $this->prepareAlgorithm($visit);
            $news = $this->getRandomNewsItem();
            
            //Для данного посетителя генерируем показы тизеров на странице кроткой новости от 1 до 3 экранов
            $teasersShowForShortNews  = $this->generateTeasersShow($news);
            foreach ($teasersShowForShortNews as $teaser) {
               //С вероятностью CHANCE_OF_TEASERS_CLICK_1 % генерируем клик по одному из показанных тизеров
               if ($this->faker->boolean($chanceOfGettingTrue = self::CHANCE_OF_TEASERS_CLICK_1)) {
                   $this->createTeasersClick($visit, $teaser);
               }

               //С вероятностью CHANCE_OF_NEWS_CLICK_SHORT_TO_FULL % генерируем переход на страницу полной новости
               if ($this->faker->boolean($chanceOfGettingTrue = self::CHANCE_OF_NEWS_CLICK_SHORT_TO_FULL)) {
                   $this->createNewsClickShortToFull($visit, $news);
               }
            }

            //Для данного посетителя генерируем показы тизеров на странице полной новости от 1 до 3 экранов
            $teasersShowForFullNews  = $this->generateTeasersShow($news);
            foreach ($teasersShowForFullNews as $teaser) {
               //С вероятностью CHANCE_OF_TEASERS_CLICK_2 % генерируем клик по одному из показанных тизеров
               if ($this->faker->boolean($chanceOfGettingTrue = self::CHANCE_OF_TEASERS_CLICK_2)) {
                   $this->createTeasersClick($visit, $teaser);
               }

               //С вероятностью CHANCE_OF_TEASERS_CLICK_3 % генерируем клик по еще одному из показанных тизеров
               if ($this->faker->boolean($chanceOfGettingTrue = self::CHANCE_OF_TEASERS_CLICK_3)) {
                   $this->createTeasersClick($visit, $teaser);
               }
            }

            //В 50% генерируем переход на страницу топа новостей или категорию новости и показ рекламных блоков новостей от 1 до 3 экранов
            // if ($this->faker->boolean($chanceOfGettingTrue = 50)) {
            //     $news = $this->generateNewsForTop();
            // } else {
            //     $news = $this->generateNewsForCategory();
            // }

            //В 50% случае генерируем переход на страницу топа тизеров
            if ($this->faker->boolean($chanceOfGettingTrue = 50)) {
                $teasers = $this->generateTeasersForTop();
                //В CHANCE_OF_TEASERS_CLICK_4 % генерируем клик по тизеру со страницы топа тизеров
                if ($this->faker->boolean($chanceOfGettingTrue = self::CHANCE_OF_TEASERS_CLICK_4)) {
                    $teaserClick = $this->createTeasersClick($visit, $teasers[0]);
                    //Для каждого из кликов по тизеру генерируем с вероятностью CHANCE_OF_POSTBACK_APPROVED % приход постебка со статусом "в ожидании"
                    if ($this->faker->boolean($chanceOfGettingTrue = self::CHANCE_OF_POSTBACK_APPROVED)) {
                        $this->createPostback($teaserClick, 'approved');
                        //В CHANCE_OF_POSTBACK_PENDING_OR_DECLINED % случаев прихода постебка в статусе ождидания генерируем еще один постбек со статусом "отклонен" или "подтвержден"
                        if ($this->faker->boolean($chanceOfGettingTrue = self::CHANCE_OF_POSTBACK_PENDING_OR_DECLINED)) {
                            if ($this->faker->boolean($chanceOfGettingTrue = 50)) {
                                $this->createPostback($teaserClick, 'pending'); 
                            } else {
                                $this->createPostback($teaserClick, 'declined'); 
                            }
                        }
                    }
                }
            }

            $visitEndTime = new Carbon();

            echo "Time per visit: " . $visitStartTime->diffInMilliseconds($visitEndTime) . " milliseconds\n";
          
        }

        $this->updateVisits();
    }

    private function prepareAlgorithm($visit)
    {
        $this->randomAlgorythm->setEntityManager($this->entityManager)
                ->setGeoCode($visit->getCountryCode())
                ->setTrafficType($visit->getTrafficType())
                ->setBuyerId($visit->getMediabuyer()->getId())
                ->setSourceId($visit->getSource())
                ->setCacheService(new RedisAdapter(
                    RedisAdapter::createConnection($_ENV['REDIS_URL']),
                    '',
                    $_ENV['CACHE_LIFETIME']
                ));
    }

    private function createPostback($click, $status)
    {
        $partner = $this->entityManager->getRepository(Partners::class)->find(1);
        $postBack = new Postback();
        $postBack->setAffiliate($partner)
            ->setClick($click)
            ->setStatus($status)
            ->setCurrencyCode($partner->getCurrency()->getIsoCode())
            ->setPayout(rand(0, 999));

        $postBack->setFulldata(serialize($postBack));
            
        $postBack->setPayoutRub($this->getPostbackRub($postBack));

        $this->entityManager->persist($postBack);
        $this->entityManager->flush();
    }

    private function getPostbackRub(Postback $postback)
    {
        if($postback->getCurrencyCode() == 'rub') return $postback->getPayout();

        $rate = [
            'usd' => $this->entityManager->getRepository(CurrencyRate::class)->getRateByCode('usd'),
            'uah' => $this->entityManager->getRepository(CurrencyRate::class)->getRateByCode('uah'),
            'eur' => $this->entityManager->getRepository(CurrencyRate::class)->getRateByCode('eur'),
        ];

        return $postback->getPayout() * $rate[$postback->getCurrencyCode()];
    }

    private function createTeasersClick($visit, $teaser)
    {
        $teaserClick = new TeasersClick();
        $teaserClick->setBuyer($visit->getMediabuyer());
        $teaserClick->setTeaser($teaser);  
        $teaserClick->setDesign($visit->getDesign());  
        $teaserClick->setAlgorithm($visit->getAlgorithm()); 
        $teaserClick->setCountryCode($visit->getCountryCode());
        $teaserClick->setTrafficType($visit->getTrafficType());
        $teaserClick->setPageType('short');
        $teaserClick->setUserIp($visit->getIp());
        $teaserClick->setUuid($visit->getUuid());

        $this->entityManager->persist($teaserClick);
        $this->entityManager->flush();

        return $teaserClick;
    }

    private function createNewsClickShortToFull($visit, $news)
    {
        $newsClickShortToFull = new NewsClickShortToFull();
        $newsClickShortToFull->setBuyer($visit->getMediabuyer());
        $newsClickShortToFull->setNews($news);  
        $newsClickShortToFull->setDesign($visit->getDesign());  
        $newsClickShortToFull->setAlgorithm($visit->getAlgorithm()); 
        $newsClickShortToFull->setCountryCode($visit->getCountryCode());
        $newsClickShortToFull->setTrafficType($visit->getTrafficType());
        $newsClickShortToFull->setUserIp($visit->getIp());
        $newsClickShortToFull->setUuid($visit->getUuid());

        $this->entityManager->persist($newsClickShortToFull);
        $this->entityManager->flush();
    }

    private function generateTeasersShow($news)
    {
        return $this->randomAlgorythm->getTeaserForNews($news, 1);
    }

    private function generateNewsForTop()
    {    
        return $this->randomAlgorythm->getNewsForTop(1);
    }

    private function generateTeasersForTop()
    {
        return $this->randomAlgorythm->getTeaserForTop(1);
    }

    private function generateNewsForCategory()
    {
        $randomCategory = $this->getRandomCategory();
        return $this->randomAlgorythm->getNewsForCategory($randomCategory, 1);
    }

    private function getRandomNewsItem()
    {
        return $this->entityManager->getRepository(News::class)->find(1);
    }

    private function getRandomCategory()
    {
        $query = $this->entityManager->createQueryBuilder('nc')
            ->from(NewsCategory::class, 'nc')
            ->addSelect('RAND() as HIDDEN rand')
            ->addOrderBy('rand')
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    private function createVisit()
    {
        $fakeUserAgent = $this->faker->userAgent;
        $parser = Parser::create();
        $userAgent = $parser->parse($fakeUserAgent);

        $visits = new Visits();
        $user = $this->getRandomEntityRow(User::class);
        $trafficTypes = ['desktop', 'tablet', 'mobile'];
        $mobileOperators = ['MTS', 'BeeLine', 'Tele2'];
        $screenSizes = ['1980x1080', '1680x1050', '320x240'];

        $visits->setUuid(Uuid::uuid4())
            ->setMediabuyer($user)
            ->setSource($this->getRandomSource($user))
            ->setNews($this->getRandomNews($user))
            ->setDomain($this->getRandomDomain($user))
            ->setDesign($this->getRandomEntityRow(Design::class))
            ->setAlgorithm($this->getRandomEntityRow(Algorithm::class))
            ->setCountryCode($this->getRandomEntityRow(Country::class)->getIsoCode())
            ->setCity($this->faker->city)
            ->setUtmTerm($this->faker->text($maxNbChars = 50))
            ->setUtmMedium($this->faker->text($maxNbChars = 50))
            ->setUtmContent($this->faker->text($maxNbChars = 50))
            ->setUtmCampaign($this->faker->text($maxNbChars = 50))
            ->setIp($this->faker->ipv4)
            ->setTrafficType($this->randValue($trafficTypes))
            ->setOs($userAgent->os->family)
            ->setOsWithVersion($userAgent->os->toString())
            ->setBrowser($userAgent->ua->family)
            ->setBrowserWithVersion($userAgent->ua->toString())
            ->setMobileBrand($userAgent->device->brand)
            ->setMobileModel($userAgent->device->model)
            ->setMobileOperator($this->randValue($mobileOperators))
            ->setScreenSize($this->randValue($screenSizes))
            ->setSubid1($this->faker->text($maxNbChars = 20))
            ->setSubid2($this->faker->text($maxNbChars = 20))
            ->setSubid3($this->faker->text($maxNbChars = 20))
            ->setSubid4($this->faker->text($maxNbChars = 20))
            ->setSubid5($this->faker->text($maxNbChars = 20))
            ->setCreatedAt()
            ->setUserAgent($fakeUserAgent)
            ->setUrl($this->faker->url);

        $this->entityManager->persist($visits);
        $this->entityManager->flush();

        return $visits;
    }

    private function randValue($array)
    {
        return $array[array_rand($array)];
    }

    private function getRandomEntityRow($entity)
    {
        $query = $this->entityManager->getRepository($entity)
            ->createQueryBuilder('q')
            ->addSelect('RAND() as HIDDEN rand')
            ->addOrderBy('rand')
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    private function getRandomSource(User $user)
    {
        $query = $this->entityManager->getRepository(Sources::class)
            ->createQueryBuilder('sources')
            ->where('sources.user = :mediaBuyer')
            ->andWhere('sources.is_deleted != :is_deleted')
            ->addSelect('RAND() as HIDDEN rand')
            ->addOrderBy('rand')
            ->setMaxResults(1)
            ->setParameters([
                'mediaBuyer' => $user,
                'is_deleted' => 1,
            ])
            ->getQuery();
        return $query->getOneOrNullResult();
    }

    private function getRandomNews(User $user)
    {
        $query = $this->entityManager->getRepository(News::class)
            ->createQueryBuilder('news')
            ->where('news.user = :mediaBuyer')
            ->andWhere('news.is_deleted != :is_deleted')
            ->addSelect('RAND() as HIDDEN rand')
            ->addOrderBy('rand')
            ->setMaxResults(1)
            ->setParameters([
                'mediaBuyer' => $user,
                'is_deleted' => 1,
            ])
            ->getQuery();
        return $query->getOneOrNullResult();
    }

    private function getRandomDomain(User $user)
    {
        $query = $this->entityManager->getRepository(DomainParking::class)
            ->createQueryBuilder('domain')
            ->where('domain.user = :mediaBuyer')
            ->andWhere('domain.is_deleted != :is_deleted')
            ->addSelect('RAND() as HIDDEN rand')
            ->addOrderBy('rand')
            ->setMaxResults(1)
            ->setParameters([
                'mediaBuyer' => $user,
                'is_deleted' => 1,
            ])
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    private function setDateInformation(array $visits)
    {
        /** @var Visits $visit */
        foreach($visits as $visit) {
            $visit->setDayOfWeek($this->getDayOfWeek($visit->getCreatedAt()))
                ->setTimesOfDay($this->getTimesOfDay($visit->getCreatedAt()));

            $this->entityManager->flush();
        }
    }

    private function updateVisits()
    {
        $visits = $this->entityManager->getRepository(Visits::class)->findAll();
        $this->updateCreatedAtByDateRange(self::DATE_FROM, self::DATE_TO, $visits);
        $this->setDateInformation($visits);
    }

    public function getDependencies()
    {
        return array(
            SourcesFixtures::class,
            NewsFixtures::class,
            UserFixtures::class,
            DesignsFixtures::class,
            AlgorithmsFixtures::class,
            CountryFixtures::class,
        );
    }

    public static function getGroups(): array
    {
        return ['RealisticDataFixtures'];
    }
}