<?php

namespace App\DataFixtures;

use App\Entity\Country;
use App\Entity\TeasersGroup;
use App\Entity\TeasersSubGroup;
use App\Entity\TeasersSubGroupSettings;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker;


class TeasersGroupsFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    const PARENT_COUNT = 4;
    const CHILD_COUNT = 3;
    const PROBABILITY_GEO = 70;

    /** @var EntityManagerInterface */
    public $entityManager;

    public $faker;

    public $users;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->faker = Faker\Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        foreach ($users as $user) {
            $this->generateFakeTeasersGroups($user);
        }
    }

    private function generateFakeTeasersGroups($user) {
        for($i = 1; $i <= self::PARENT_COUNT; $i++) {
            $teaserGroup = $this->createFakeTeasersGroup($user, $i);
            for ($j = 1; $j <=   self::CHILD_COUNT; $j++) {
                $teaserSubGroup = $this->createFakeTeasersSubGroup($teaserGroup, $j);
                $this->createFakeTeasersSubGroupSettings($teaserSubGroup);
            }
        }
    }

    private function createFakeTeasersGroup($user, $i) {
        $teaserGroup = new TeasersGroup();
        $is_active = $i == 4 ? false : true;
        $teaserGroup = $teaserGroup->setName($this->faker->text($maxNbChars = 80))
            ->setIsActive($is_active)
            ->setUser($user)
            ->setCreatedAt($this->faker->dateTime());

        $this->entityManager->persist($teaserGroup);
        $this->entityManager->flush();

        return $teaserGroup;
    }

    private function createFakeTeasersSubGroup(TeasersGroup $teaserGroup, $j) {
        $teaserSubGroup = new TeasersSubGroup();
        $is_active = $j == 3 ? false : true;
        $teaserSubGroup->setTeaserGroup($teaserGroup)
            ->setName($this->faker->text($maxNbChars = 80))
            ->setIsActive($is_active)
            ->setCreatedAt($this->faker->dateTime());

        $this->entityManager->persist($teaserSubGroup);
        $this->entityManager->flush();

        return $teaserSubGroup;
    }

    private function createFakeTeasersSubGroupSettings(TeasersSubGroup $subGroup)
    {
        foreach($this->getGeo() as $geo) {
            $teaserSubGroupSetting = new TeasersSubGroupSettings();

            $subGroupLink = 'http://' . $this->faker->domainName . '/' . $this->getQueryParams();

            $teaserSubGroupSetting->setTeasersSubGroup($subGroup)
                ->setApproveAveragePercentage($this->faker->numberBetween($min = 1, $max = 100))
                ->setLink($subGroupLink)
                ->setGeoCode($geo);

            $this->entityManager->persist($teaserSubGroupSetting);
            $this->entityManager->flush();
        }
    }

    private function getGeo()
    {

        if(mt_rand(0, 100) <= self::PROBABILITY_GEO){
            $geo = $this->entityManager->getRepository(Country::class)
                ->createQueryBuilder('country')
                ->addSelect('RAND() as HIDDEN rand')
                ->addOrderBy('rand')
                ->setMaxResults(rand(1, 3))
                ->getQuery()
                ->getResult();
        }

        $geo[] = null;

        return $geo;
    }

    private function getQueryParams()
    {
        $macros = '?';

        if($this->faker->boolean($chanceOfGettingTrue = 33)) {
            $macros .= "utm_term={$this->faker->text($maxNbChars = 5)}";
        } elseif($this->faker->boolean($chanceOfGettingTrue = 33)) {
            $macros .= "utm_term={utm_term}";
        }

        if($this->faker->boolean($chanceOfGettingTrue = 33)) {
            $macros .= "&utm_content={$this->faker->text($maxNbChars = 5)}";
        } elseif($this->faker->boolean($chanceOfGettingTrue = 33)) {
            $macros .= "&utm_content={utm_content}";
        }

        if($this->faker->boolean($chanceOfGettingTrue = 33)) {
            $macros .= "&utm_campaign={$this->faker->text($maxNbChars = 5)}";
        } elseif($this->faker->boolean($chanceOfGettingTrue = 33)) {
            $macros .= "&utm_campaign={utm_campaign}";
        }

        if($this->faker->boolean($chanceOfGettingTrue = 33)) {
            $macros .= "&subid1={$this->faker->text($maxNbChars = 5)}";
        } elseif($this->faker->boolean($chanceOfGettingTrue = 33)) {
            $macros .= "&subid1={subid1}";
        }

        if($this->faker->boolean($chanceOfGettingTrue = 33)) {
            $macros .= "&subid2={$this->faker->text($maxNbChars = 5)}";
        } elseif($this->faker->boolean($chanceOfGettingTrue = 33)) {
            $macros .= "&subid2={subid2}";
        }

        if($this->faker->boolean($chanceOfGettingTrue = 33)) {
            $macros .= "&subid3={$this->faker->text($maxNbChars = 5)}";
        } elseif($this->faker->boolean($chanceOfGettingTrue = 33)) {
            $macros .= "&subid3={subid3}";
        }

        if($this->faker->boolean($chanceOfGettingTrue = 33)) {
            $macros .= "&subid4={$this->faker->text($maxNbChars = 5)}";
        } elseif($this->faker->boolean($chanceOfGettingTrue = 33)) {
            $macros .= "&subid4={subid4}";
        }

        if($this->faker->boolean($chanceOfGettingTrue = 33)) {
            $macros .= "&subid5={$this->faker->text($maxNbChars = 5)}";
        } elseif($this->faker->boolean($chanceOfGettingTrue = 33)) {
            $macros .= "&subid5={subid5}";
        }

        return $macros;
    }

    public function getDependencies()
    {
        return array(
            CountryFixtures::class,
        );
    }

    public static function getGroups(): array
    {
        return ['dev', 'prod', 'TeasersGroupsFixtures'];
    }
}