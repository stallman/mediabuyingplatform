<?php

namespace App\DataFixtures;

use App\Entity\Costs;
use App\Entity\CurrencyList;
use App\Entity\CurrencyRate;
use App\Entity\MediabuyerNews;
use App\Entity\Sources;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use App\Traits\Dashboard\UsersTrait;

class CostsFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UsersTrait;

    const MIN_NUMBER = 0;
    const MAX_NUMBER = 1000;
    const NB_MAX_DECIMALS = 9.4;
    const COSTS_COUNT = 10;

    /** @var EntityManagerInterface */
    public $entityManager;

    public $faker;
    public array $rate;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->faker = Faker\Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        $this->rate = [
            'usd' => $this->entityManager->getRepository(CurrencyRate::class)->getRateByCode('usd'),
            'uah' => $this->entityManager->getRepository(CurrencyRate::class)->getRateByCode('uah'),
            'eur' => $this->entityManager->getRepository(CurrencyRate::class)->getRateByCode('eur'),
        ];

        /** @var User $mediaBuyer */
        foreach($this->getMediabuyerUsers() as $mediaBuyer){
            $mediaBuyerNews = $this->entityManager->getRepository(MediabuyerNews::class)->getMediaBuyerNewsRotation($mediaBuyer);
            $this->createCosts($mediaBuyerNews);
        }
    }

    private function createCosts(array $mediaBuyerNews)
    {
        /** @var MediabuyerNews $mediaBuyerNewsItem */
        foreach($mediaBuyerNews as $mediaBuyerNewsItem){
            for ($i = 1; $i <= self::COSTS_COUNT; $i++) {
                $costAmount = $this->faker->randomFloat(self::NB_MAX_DECIMALS, self::MIN_NUMBER, self::MAX_NUMBER);
                $randomCurrency = $this->getRandomCurrency();
                $costAmountRub = $costAmount;

                if($randomCurrency->getIsoCode() != 'rub'){
                    $costAmountRub = $costAmount * $this->rate[$randomCurrency->getIsoCode()];
                }

                $cost = new Costs();
                $cost->setMediabuyer($mediaBuyerNewsItem->getMediabuyer())
                    ->setNews($mediaBuyerNewsItem->getNews())
                    ->setSource($this->getRandomMediaBuyerSource($mediaBuyerNewsItem))
                    ->setCurrency($randomCurrency)
                    ->setDate($this->getDate($i))
                    ->setCost($costAmount)
                    ->setCostRub($costAmountRub);

                $this->entityManager->persist($cost);
                $this->entityManager->flush();
            }
        }
    }

    public function getDate($dayNum): \DateTimeInterface
    {
        $date = new \DateTime();

        return $date->modify("-{$dayNum} days");
    }

    private function getRandomMediaBuyerSource(MediabuyerNews $mediabuyerNews)
    {
        return $this->faker->randomElement($this->entityManager->getRepository(Sources::class)->getMediaBuyerSources($mediabuyerNews->getMediabuyer(), $mediabuyerNews->getDropSources()));
    }

    private function getRandomCurrency()
    {
        return $this->faker->randomElement($this->entityManager->getRepository(CurrencyList::class)->findAll());
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
//            MediaBuyerNewsFixtures::class
        );
    }

    public static function getGroups(): array
    {
        return ['CostsFixtures'];
    }
}