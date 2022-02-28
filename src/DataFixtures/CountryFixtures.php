<?php

namespace App\DataFixtures;

use App\Entity\Country;
use App\Entity\News;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;

class CountryFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public $entityManager;
    const COUNTRY_LIST = [
        'RU' => 'Россия',
        'UA' => 'Украина',
        'US' => 'США',
    ];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager)
    {
        return $this
            ->addCountries()
            ->relateNewsAndCountries();
    }

    private function countryParser()
    {
        $html = file_get_contents('https://ru.wikipedia.org/wiki/ISO_3166-1');
        $crawler = new Crawler($html);
        return $crawler->filter('table')->filter('tr')->each(function ($tr) {
            return $tr->filter('td')->each(function ($td) {
                return trim($td->text());
            });
        });
    }

    //TODO расскомментировать на случай необходимости получения всех стран(закомментировать дубль метод)
//    private function addCountries()
//    {
//        $countries = $this->countryParser();
//        foreach ($countries as $country) {
//            if (isset($country[0]) && isset($country[1]) && strlen($country[1]) == 2) {
//
//                $country_name = $country[0];
//                $country_code = $country[1];
//
//                $countryEntity = new Country();
//                $countryEntity->setName($country_name);
//                $countryEntity->setIsoCode($country_code);
//
//                $this->entityManager->persist($countryEntity);
//                if ($country_name == "Япония") {
//                    break;
//                }
//            }
//        }
//        $this->entityManager->flush();
//
//        return $this;
//    }

    private function addCountries()
    {
        foreach (self::COUNTRY_LIST as $isoCode => $country) {
            $countryEntity = new Country();
            $countryEntity->setName($country);
            $countryEntity->setIsoCode($isoCode);

            $this->entityManager->persist($countryEntity);
        }
        $this->entityManager->flush();

        return $this;
    }

    private function relateNewsAndCountries()
    {
        $news = $this->entityManager->getRepository(News::class)->findAll();
        $countries = $this->entityManager->getRepository(Country::class)->findAll();
        $russia = $this->entityManager->getRepository(Country::class)->getCountryByIsoCode('RU');

        /** @var News $newsItem */
        $newsIterator = 1;
        foreach ($news as $newsItem) {
            if($newsIterator <= 200){
                $newsIterator++;
                $newsItem->addCountry($russia);
            } else {
                $numberOfCountry = rand(1, 3);
                for ($i = 0; $i <= $numberOfCountry; $i++) {
                    $randomCountryKey = array_rand($countries);
                    $country = $countries[$randomCountryKey];
                    /** @var Country $country */
                    $newsItem->addCountry($country);
                }
            }
        }
        $this->entityManager->flush();

        return $this;
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
            NewsFixtures::class,
            PartnersFixtures::class,
        );
    }

    public static function getGroups(): array
    {
        return ['dev', 'prod', 'CountryFixtures'];
    }
}
