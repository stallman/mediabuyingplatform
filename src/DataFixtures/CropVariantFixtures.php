<?php

namespace App\DataFixtures;

use App\Entity\CropVariant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;


class CropVariantFixtures extends Fixture implements FixtureGroupInterface
{

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    const DESIGN_PARAMS = [
        1 => [
            'news' => ['width' => 216, 'height' => 162],
            'teasers' => ['width' => 216, 'height' => 162],
        ],
//        2 => [
//            'teasers' => ['width' => 385, 'height' => 289],
//            'news' => ['width' => 385, 'height' => 289],
//        ],
        2 => [
            'teasers' => ['width' => 240, 'height' => 180],
            'news' => ['width' => 240, 'height' => 180],
        ],
        3 => [
            'teasers' => ['width' => 275, 'height' => 183],
            'news' => ['width' => 275, 'height' => 183],
        ],
        4 => [
            'teasers' => ['width' => 528, 'height' => 320],
            'news' => ['width' => 528, 'height' => 320],
        ],
        5 => [
            'teasers' => ['width' => 492, 'height' => 328],
            'news' => ['width' => 492, 'height' => 328],
        ],
    ];

    public function load(ObjectManager $manager)
    {
        $countCropVariant = $this->container->getParameter('theme_count');

        for ($i = 1; $i <= $countCropVariant; $i++) {
            $cropVariant = new CropVariant();

            $cropVariant->setDesignNumber("Дизайн $i");
            $cropVariant->setWidthTeaserBlock($this->getTeasersBlockAttr($i, 'width'));
            $cropVariant->setHeightTeaserBlock($this->getTeasersBlockAttr($i, 'height'));
            $cropVariant->setWidthNewsBlock($this->getNewsBlockAttr($i, 'width'));
            $cropVariant->setHeightNewsBlock($this->getNewsBlockAttr($i, 'height'));

            $manager->persist($cropVariant);
        }
        $manager->flush();
    }

    public function getTeasersBlockAttr($designNum, $param)
    {
        return self::DESIGN_PARAMS[$designNum]['teasers'][$param];
    }
    public function getNewsBlockAttr($designNum, $param)
    {
        return self::DESIGN_PARAMS[$designNum]['news'][$param];
    }

    public static function getGroups(): array
    {
        return ['dev', 'prod', 'CropVariantFixtures'];
    }
}