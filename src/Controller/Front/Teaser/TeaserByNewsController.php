<?php


namespace App\Controller\Front\Teaser;


use App\Entity\News;
use App\Traits\SerializerTrait;
use App\Traits\Dashboard\MacrosReplacementTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeaserByNewsController extends TeaserController
{
    use SerializerTrait;
    use MacrosReplacementTrait;

    protected string $pageType;

    const PAGE_TYPES = ['short', 'full'];

    /**
     * @Route("/ajax-news-teasers/{news}/{type}/{page}", name="front.ajax_news_teasers")
     * @param News $news
     * @param string $type
     * @param int $page
     * @return Response
     */
    public function getAjaxTeasers(News $news, string $type, int $page = 1)
    {
        if(!in_array($type, self::PAGE_TYPES)) return JsonResponse::create('not found', 404);
        if ($news->getIsDeleted()) return JsonResponse::create('not found', 404);

        $this->pageType = $type;
        $this->setPreparedTeasers($news, $page);
        $serializer = $this->serializer();
        $teasers = $this->replaceMacrosToCity($this->getCity());

        return JsonResponse::create($serializer->serialize($teasers, 'json'), 200);
    }
}