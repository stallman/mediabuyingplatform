<?php

namespace App\Twig\Dashboard\Admin;

use App\Entity\StatisticNews;
use App\Twig\AppExtension;
use Doctrine\ORM\PersistentCollection;
use Twig\TwigFunction;

class NewsExtension extends AppExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('common_statistic', [$this, 'commonStatistic'])
        ];
    }

    /**
     * @param PersistentCollection $statistic
     * @return StatisticNews
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function commonStatistic(PersistentCollection $statistic)
    {
        $statisticItem = new StatisticNews();

        if(!$statistic->isEmpty()){
            foreach($statistic as $statisticItem) {
                if(is_null($statisticItem->getMediabuyer())){
                    break;
                }
            }
        }

        return $statisticItem;
    }

}