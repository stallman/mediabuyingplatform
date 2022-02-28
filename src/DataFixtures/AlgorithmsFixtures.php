<?php

namespace App\DataFixtures;

use App\Entity\Algorithm;
use App\Entity\MediabuyerAlgorithms;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use App\Traits\Dashboard\UsersTrait;


class AlgorithmsFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    use UsersTrait;

    const ALGORITHMS = [
        'random' => 'Рандом',
        'section_random' => 'Секции + рандом',
        'screens' => 'Экраны',
        'hidden_blocks' => 'Скрытые блоки'
    ];

    /** @var EntityManagerInterface */
    public $entityManager;

    public $faker;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->faker = Faker\Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        $this->createAlgorithms();
        $this->activeAlgorithmsForBuyers();
    }

    private function createAlgorithms()
    {
        foreach(self::ALGORITHMS as $slug => $name) {
            $algorithm = new Algorithm();
            $algorithm->setName($name)
                    ->setSlug($slug);

            if($name == 'Рандом'){
                $algorithm->setIsDefault(1);
            } else {
                $algorithm->setIsDefault(0);
            }

            if($name == 'Рандом'){
                $algorithm->setIsActive(0);
            } else {
                $algorithm->setIsActive(1);
            }

            $this->entityManager->persist($algorithm);
            $this->entityManager->flush();
        }
    }

    private function activeAlgorithmsForBuyers()
    {
        $mediaBuyers = $this->getMediabuyerUsers();
        $algorithms = $this->entityManager->getRepository(Algorithm::class)->findAll();

        /** @var User $mediaBuyer */
        foreach($mediaBuyers as $mediaBuyer) {
            /** @var Algorithm $algorithm */
            foreach($algorithms as $algorithm) {
                if($this->faker->boolean($chanceOfGettingTrue = 50)){
                    $mediaBuyerAlgorithm = new MediabuyerAlgorithms();
                    $mediaBuyerAlgorithm->setMediabuyer($mediaBuyer)
                        ->setAlgorithm($algorithm);

                    $this->entityManager->persist($mediaBuyerAlgorithm);
                    $this->entityManager->flush();
                }
            }
        }
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class
        );
    }

    public static function getGroups(): array
    {
        return ['dev', 'prod', 'AlgorithmsFixtures'];
    }
}