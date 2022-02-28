<?php

namespace App\Twig\Dashboard\MediaBuyer;

use App\Entity\MediabuyerNews;
use App\Entity\News;
use App\Entity\StatisticNews;
use App\Entity\User;
use App\Twig\AppExtension;
use Twig\TwigFunction;

class NewsExtension extends AppExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('statistic', [$this, 'statistic']),
            new TwigFunction('get_mediabuyer_news', [$this, 'getMediabuyerNews'])
        ];
    }

    /**
     * @param News $news
     * @param User $user
     * @return StatisticNews
     */
    public function statistic(News $news, User $user)
    {
        if($news->getUser() === $user){
            $statistic = $this->getMediabuyerNewsStatistic($news->getStatistic(), $user);
        } else {
            $statistic = $this->getCommonStatistic($news->getStatistic());
        }

        return $statistic;
    }

    /**
     * @param News $news
     * @param User $user
     * @return MediabuyerNews|object
     */
    public function getMediabuyerNews(News $news, User $user)
    {
        return $this->entityManager->getRepository(MediabuyerNews::class)->getMediaBuyerNewsItem($user, $news);
    }

    private function getMediabuyerNewsStatistic($statistic, $user)
    {
        $statisticItem = new StatisticNews();

        if(!$statistic->isEmpty()){
            foreach($statistic as $statisticItem) {
                if($statisticItem->getMediabuyer() == $user){
                    break;
                }
            }
        }

        return $statisticItem;
    }

    private function getCommonStatistic($statistic)
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