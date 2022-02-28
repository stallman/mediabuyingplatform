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

class VisitFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use DeviceTrait;
    use DateRangeTrait;
    use TimeInformationTrait;

    const DATE_FROM = '2020-02-06 17:54:28';
    const DATE_TO = '2020-08-06 17:54:28';

    /** @var EntityManagerInterface */
    public $entityManager;

    public $faker;

    const VISITS_COUNT = 100;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->faker = Faker\Factory::create();
        $this->filesystem = new Filesystem();
    }

    public function load(ObjectManager $manager)
    {
        for($i = 0; $i < self::VISITS_COUNT; $i++) {
            $this->createVisit();
        }

        $visits = $this->entityManager->getRepository(Visits::class)->findAll();
        $this->updateCreatedAtByDateRange(self::DATE_FROM, self::DATE_TO, $visits);
        $this->setDateInformation($visits);
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
        return ['VisitFixtures'];
    }
}