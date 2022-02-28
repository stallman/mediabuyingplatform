<?php

namespace App\DataFixtures;

use App\Entity\Design;
use App\Entity\MediabuyerDesigns;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use App\Traits\Dashboard\UsersTrait;

class DesignsFixtures extends Fixture implements FixtureGroupInterface
{
    use UsersTrait;
    
    const DESIGNES_COUNT = 5;

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
        $this->createDesigns();
        $this->activeDesignsForBuyers();
    }

    private function createDesigns()
    {
        for($i = 0; $i < self::DESIGNES_COUNT; $i++) {
            $design = new Design();
            $design->setName("Дизайн " . ($i + 1))
                ->setSlug("theme_" . ($i + 1));
            $active = true;
//            if($i == 2){
//                $active = false;
//            }
            $design->setIsActive($active);

            $this->entityManager->persist($design);
            $this->entityManager->flush();
        }
    }

    private function activeDesignsForBuyers()
    {
        $mediaBuyers = $this->getMediabuyerUsers();
        #dd($mediaBuyers);
        $designs = $this->entityManager->getRepository(Design::class)->findAll();

        /** @var User $mediaBuyer */
        foreach($mediaBuyers as $mediaBuyer) {
            /** @var Design $design */
            foreach($designs as $design) {
                if($this->faker->boolean($chanceOfGettingTrue = 50)){
                    $mediabuyerDesigns = new MediabuyerDesigns();
                    $mediabuyerDesigns->setMediabuyer($mediaBuyer)
                        ->setDesign($design);

                    $this->entityManager->persist($mediabuyerDesigns);
                    $this->entityManager->flush();
                }
            }
        }
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
        );
    }

    public static function getGroups(): array
    {
        return ['dev', 'prod', 'DesignsFixtures'];
    }
}