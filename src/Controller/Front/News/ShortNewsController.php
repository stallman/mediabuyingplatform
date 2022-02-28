<?php


namespace App\Controller\Front\News;


use App\Entity\News;
use App\Service\VisitorInformation;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShortNewsController extends AbstractNewsController
{
    protected string $pageType = 'short';

    /**
     * @Route("/news/short/{id}", name="front.short_news")
     *
     * @return Response
     */
    public function renderPage(News $news)
    {
        $this->visitorInformation->incrementInteraction(VisitorInformation::INTERACTION_NAME_NEWS);

        $algorithm = $this->algorithmBuilder->getInstance($this->visitorInformation->getAlgorithm());
        $page = $this->getPage($algorithm, VisitorInformation::INTERACTION_NAME_NEWS);

        $this
            ->setNewsCroppedImage($news)
            ->setPreparedTeasers($news, $page)
            ->createShowNews($news, $this->pageType, $this->mediaBuyer)
        ;

        return $this->render("front/$this->theme/news/short_news.html.twig", [
            'article' => $news,
            'teasers' => $this->teasers,
            'page_number' => $page,
            'block_count' => count($this->teasers),
            'city' => $this->getCity(),
            'width_teaser_block' => $this->cropVariant->getWidthTeaserBlock(),
            'height_teaser_block' => $this->cropVariant->getHeightTeaserBlock(),
            'news_cropped_image_link' => $this->newsCroppedImage->getFilePath() . '/' . $this->newsCroppedImage->getFileName(),
        ]);
    }
}