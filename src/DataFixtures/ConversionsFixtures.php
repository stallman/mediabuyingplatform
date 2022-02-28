<?php

namespace App\DataFixtures;

use App\Entity\Conversions;
use App\Entity\ConversionStatus;
use App\Entity\Country;
use App\Entity\CurrencyList;
use App\Entity\CurrencyRate;
use App\Entity\Partners;
use App\Entity\TeasersClick;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use App\Traits\Dashboard\UsersTrait;

class ConversionsFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UsersTrait;

    const CONVERSION_COUNT = 100;
    const STATUS = ['подтвержден', 'в ожидании', 'отклонен'];

    public EntityManagerInterface $entityManager;
    public Faker\Generator $faker;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->faker = Faker\Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        foreach($this->getMediabuyerUsers() as $user) {
            $this->createAndAddFakeConversion($user);
        }
    }

    private function createAndAddFakeConversion($user)
    {
        $rate = [
            'usd' => $this->entityManager->getRepository(CurrencyRate::class)->getRateByCode('usd'),
            'uah' => $this->entityManager->getRepository(CurrencyRate::class)->getRateByCode('uah'),
            'eur' => $this->entityManager->getRepository(CurrencyRate::class)->getRateByCode('eur'),
        ];

        $partners = $this->entityManager->getRepository(Partners::class)->getMediaBuyerPartnersList($user);
        $currencyList = $this->entityManager->getRepository(CurrencyList::class)->findAll();
        $statuses = $this->entityManager->getRepository(ConversionStatus::class)->findAll();

        $i = 0;

        /** @var TeasersClick $teaserClick */
        foreach($this->entityManager->getRepository(TeasersClick::class)->getClickByBuyer($user) as $teaserClick) {
            $i++;
            if($i % 4 == 0) continue;

            $randomCurrency = $this->faker->randomElement($currencyList);
            /** @var ConversionStatus $status */
            $status = $statuses[array_rand($statuses)];
            $amount = 0;

            if($status->getCode() == 200){
                $amount = $this->faker->randomFloat($nbMaxDecimals = 2, $min = 1.00, $max = 3000.00);
            }
            $amountRub = $amount;
            if($randomCurrency->getIsoCode() != 'rub'){
                $amountRub = $amount * $rate[$randomCurrency->getIsoCode()];
            }

            $conversion = new Conversions();
            $conversion->setMediabuyer($user)
                ->setTeaserClick($teaserClick)
                ->setAffilate($this->faker->randomElement($partners))
                ->setSource($teaserClick->getSource())
                ->setNews($teaserClick->getNews())
                ->setSubgroup($teaserClick->getTeaser()->getTeasersSubGroup())
                ->setCountry($this->entityManager->getRepository(Country::class)->getCountryByIsoCode($teaserClick->getCountryCode()))
                ->setDesign($teaserClick->getDesign())
                ->setAlgorithm($teaserClick->getAlgorithm())
                ->setStatus($status)
                ->setAmount($amount)
                ->setAmountRub($amountRub)
                ->setCurrency($randomCurrency)
                ->setUuid($teaserClick->getUuid())
                ->setCreatedAt(null)
                ->setUpdatedAt(null);

            $this->entityManager->persist($conversion);
            $this->entityManager->flush();
        }
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
            CountryFixtures::class,
            CurrencyRateFixtures::class,
            TeasersClickFixtures::class,
            ConversionStatusFixtures::class,
        );
    }

    public static function getGroups(): array
    {
        return ['ConversionsFixtures'];
    }
}