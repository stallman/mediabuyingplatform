<?php


namespace App\Controller\Front;

use App\Entity\DomainParking;
use App\Entity\MediabuyerNews;
use App\Entity\News;
use App\Entity\Sources;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends FrontController
{
    /**
     * @Route("/news/test/{id}", name="news.test")
     * @param User $mediaBuyer
     * @return Response
     */
    public function newsLinkListAction(User $mediaBuyer)
    {
        $news = $this->entityManager->getRepository(News::class)->getMediaBuyerNewsList($mediaBuyer);
        $links = [];

        foreach($news as $newsItem) {
            $mediabuyerNews = $this->entityManager->getRepository(MediabuyerNews::class)->findOneBy(
                [
                    'mediabuyer' => $mediaBuyer,
                    'news' => $newsItem
                ]
            );

            $qb = $this->entityManager->createQueryBuilder();
            $paramsForSelect = ['src.id', 'src.title', 'src.utm_campaign', 'src.utm_term', 'src.utm_content', 'src.subid1', 'src.subid2', 'src.subid3', 'src.subid4', 'src.subid5'];

            if($mediabuyerNews){
                $dropSources = $this->cleanDropSources($mediabuyerNews->getDropSources());
                if($dropSources){
                    $sources = $this->getSourcesWithoutDropped($qb, $paramsForSelect, $dropSources);
                } else {
                    $sources = $this->getAllSources($qb, $paramsForSelect);
                }
            } else {
                $sources = $this->getAllSources($qb, $paramsForSelect);
            }
            $links = array_merge($links, $this->generateFullNewsSourceUrl($newsItem, $sources));
        }

        return JsonResponse::create($links);
    }

    private function cleanDropSources($dropSources)
    {
        $dropSourcesArr = array_filter(explode(",", $dropSources));
        return implode(",", $dropSourcesArr);
    }

    private function getAllSources($qb, $paramsForSelect)
    {
        return $qb->select($paramsForSelect)
            ->from(Sources::class, 'src')
            ->getQuery()
            ->getResult();
    }

    private function getSourcesWithoutDropped($qb, $paramsForSelect, $dropSources)
    {
        return $qb->select('src')
            ->from(Sources::class, 'src')
            ->select($paramsForSelect)
            ->where($qb->expr()->notIn('src.id', $dropSources))
            ->getQuery()
            ->getResult();
    }

    private function generateFullNewsSourceUrl($news, $sources)
    {
        $fullUrlParams = [];
        $sourceLink = $this->generateSourceLink($news);
        $excludedParams = ['id', 'title'];

        foreach($sources as $sourceParams) {
            $keyValueUrlParams = [];
            foreach($sourceParams as $paramKey => $paramValue) {
                if(!empty($paramValue)){
                    if(!in_array($paramKey, $excludedParams)){
                        $keyValueUrlParams[] = $paramKey . '=' . $paramValue;
                    }
                    if($paramKey == 'id'){
                        $keyValueUrlParams[] = "utm_source=" . $paramValue;
                    }
                }
            }

            $link = $sourceLink . '?' . implode('&', $keyValueUrlParams);
            $fullUrlParams[] = [
                'news_id' => $news->getId(),
                'link' => $link,
            ];
        }
        return $fullUrlParams;
    }

    private function generateSourceLink($news)
    {
        $mainDomain = $this->entityManager->getRepository(DomainParking::class)->findOneBy(['user' => $this->getUser(), 'is_main' => 1]);
        $sourceLink = ($mainDomain instanceof DomainParking) ? $mainDomain->getDomain() : $this->request->server->get('HTTP_HOST');
        return $sourceLink;
    }
}



