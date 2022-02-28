<?php

namespace App\DataFixtures;

use App\Command\InstallCommand;
use App\Entity\ConversionStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class ConversionStatusFixtures extends Fixture implements FixtureGroupInterface
{
    public EntityManagerInterface $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager)
    {
        foreach(InstallCommand::CONVERSION_STATUSES as $status) {
            $conversionStatus = new ConversionStatus();
            $conversionStatus
                ->setCode($status['code'])
                ->setLabelRu($status['label_ru'])
                ->setLabelEn($status['label_en']);

            $this->entityManager->persist($conversionStatus);
        }

        $this->entityManager->flush();
    }

    public static function getGroups(): array
    {
        return ['dev', 'prod', 'ConversionStatusFixtures'];
    }
}