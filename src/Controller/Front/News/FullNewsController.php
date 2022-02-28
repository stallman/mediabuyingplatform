<?php


namespace App\Controller\Front\News;


use App\Entity\News;
use App\Service\VisitorInformation;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Twig\Front\TeasersExtension;

class FullNewsController extends AbstractNewsController
{
    protected string $pageType = 'full';

    /**
     * @Route("/news/full/{id}", name="front.full_news")
     *
     * @return Response
     */
    public function renderPage(News $news, TeasersExtension $twigExtensions)
    {
        $this->visitorInformation->incrementInteraction(VisitorInformation::INTERACTION_NAME_NEWS);

        $algorithm = $this->algorithmBuilder->getInstance($this->visitorInformation->getAlgorithm());
        $page = $this->getPage($algorithm, VisitorInformation::INTERACTION_NAME_NEWS);

        $this
            ->setNewsCroppedImage($news)
            ->setPreparedTeasers($news, $page)
            ->createShowNews($news, $this->pageType, $this->mediaBuyer)
        ;

        $articleFullDescription = preg_replace_callback(
            self::TEASER_BLOCK_REGEX,
                function() use ($twigExtensions, $news) {
                    $teaser = $this->teasers->first();
                    $width_teaser_block = $this->cropVariant->getWidthTeaserBlock();
                    $height_teaser_block = $this->cropVariant->getHeightTeaserBlock();
                    $city = $this->ip2location->getUserCity();
                    $this->teasers->removeElement($teaser);
                    return $twigExtensions->getTeasers($teaser, $this->theme, $width_teaser_block, $height_teaser_block, $news, $city);
                },
            $news->getFullDescription());

        return $this->render("front/$this->theme/news/full_news.html.twig", [
            'article' => $news,
            'article_full_description' => $articleFullDescription,
            'teasers' => $this->teasers,
            'page_number' => $page,
            'block_count' => count($this->teasers),
            'teaser_block' => self::TEASER_BLOCK,
            'city' => $this->getCity(),
            'width_teaser_block' => $this->cropVariant->getWidthTeaserBlock(),
            'height_teaser_block' => $this->cropVariant->getHeightTeaserBlock(),
            'news_cropped_image_link' => $this->newsCroppedImage->getFilePath() . '/' . $this->newsCroppedImage->getFileName(),
        ]);
    }
}