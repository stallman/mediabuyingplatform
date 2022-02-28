<?php


namespace App\Controller\Front\News;


use App\Entity\News;
use App\Entity\NewsClick;
use App\Entity\StatisticPromoBlockNews;
use App\Entity\User;
use App\Service\VisitorInformation;
use App\Traits\SerializerTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TopNewsController extends AbstractNewsController
{
    use SerializerTrait;

    protected string $pageType = 'top';

    /**
     * @Route("/", name="front.show_top_news")
     * @return Response
     * @throws \Exception
     */
    public function renderPage()
    {
        if (!$this->visitorInformation->isAutoreload()) {
            $this->visitorInformation->rewindInteraction(VisitorInformation::INTERACTION_NAME_NEWS);
            $this->visitorInformation->incrementInteraction(VisitorInformation::INTERACTION_NAME_TOP);
        }

        $this->visitorInformation->setAutoreload(false);

        $algorithm = $this->algorithmBuilder->getInstance($this->visitorInformation->getAlgorithm());
        $page = $this->getPage($algorithm, VisitorInformation::INTERACTION_NAME_TOP);
        $news = $this->getNews($page);

        return $this->render("front/$this->theme/news/all_news.html.twig", [
            'news' => $news,
            'page_number' => $page,
            'block_count' => count($news),
            'user_city' => $this->ip2location->getUserCity(),
            'width_news_block' => $this->cropVariant->getWidthNewsBlock(),
            'height_news_block' => $this->cropVariant->getHeightNewsBlock(),
        ]);
    }

    /**
     * @Route("/ajax-news/{page}", name="front.ajax_top_news")
     * @param int $page
     * @return Response
     */
    public function getAjaxNews(int $page = 1)
    {
        $serializer = $this->serializer();

        return JsonResponse::create($serializer->serialize($this->getNews($page), 'json'), 200);
    }

    private function getNews($page = 1)
    {
        $algorithm = $this->algorithmBuilder->getInstance($this->visitorInformation->getAlgorithm());
        $algorithm->setEntityManager($this->entityManager)
            ->setGeoCode($this->visitorInformation->getCountryCode())
            ->setTrafficType($this->device)
            ->setBuyerId($this->visitorInformation->getMediaBuyer())
            ->setSourceId($this->visitorInformation->getSource())
            ->setCacheService(new RedisAdapter(
                RedisAdapter::createConnection($_ENV['REDIS_URL']),
                '',
                $_ENV['CACHE_LIFETIME']
            ));

        $news = $algorithm->getNewsForTop($page);

        if($news){
            $this->promoBlockNews->setPageType($this->pageType);
            $this->promoBlockNews->saveStatistic($news);
        }
        $newsArr = [];
        foreach($news as $key => $newsItem){
            if(isset($_ENV['TITLE_STAT_PARAMS']) && $_ENV['TITLE_STAT_PARAMS']){
                $buyer = $this->entityManager->getRepository(User::class)->find($this->visitorInformation->getMediaBuyer());
                $showCount = $this->entityManager->getRepository(StatisticPromoBlockNews::class)->getNewsShowCountByNews(
                    $this->entityManager->getRepository(News::class)->find($newsItem['id']),
                    $buyer,
                    $this->visitorInformation->getCountryCode(),
                    $this->device,
                );
                $clickCount = $this->entityManager->getRepository(NewsClick::class)->getCountBuyerClick(
                    $newsItem['id'],
                    $buyer,
                );
                $newsItem['title'] = $key . ' ' . $newsItem['id'] . ' ' . $newsItem['title'] . " - {$newsItem['inner_eCPM']}" . "/$showCount/$clickCount";
                if(isset($newsItem['impressions'])){
                    $newsItem['title'] = '*** ' . $newsItem['title'];
                }
            }
            $newsArr[] = $newsItem;
        }

        return new ArrayCollection($newsArr);
    }
}