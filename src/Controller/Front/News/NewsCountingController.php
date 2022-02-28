<?php


namespace App\Controller\Front\News;


use App\Entity\Algorithm;
use App\Entity\Design;
use App\Entity\News;
use App\Entity\NewsClick;
use App\Entity\NewsClickShortToFull;
use App\Entity\Sources;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewsCountingController extends AbstractNewsController
{
    /**
     * @Route("/counting_news/{news}", name="front.counting_news",  defaults={"news" = "null"})
     * @return Response
     */
    public function newsClicksCounting(News $news)
    {
        $source = $this->entityManager->getRepository(Sources::class)->find($this->visitorInformation->getSource());

        $newsClicksCounting = new NewsClick();
        $newsClicksCounting->setBuyer($this->entityManager->getRepository(User::class)->find($this->visitorInformation->getMediaBuyer()))
            ->setSource($source)
            ->setNews($news)
            ->setTrafficType('desktop')
            ->setPageType(str_replace(array("'","\""), "", $this->request->get('pageType')))
            ->setUserIp($this->request->getClientIp())
            ->setUuid($this->visitorInformation->getUserUuid())
            ->setCountryCode($this->ip2location->getUserCountryCode())
            ->setDesign(
                $this->entityManager->getRepository(Design::class)->findOneBy([
                    'slug' => $this->visitorInformation->getDesign()
                ])
            )
            ->setAlgorithm(
                $this->entityManager->getRepository(Algorithm::class)->find(
                    $this->visitorInformation->getAlgorithm()
                )
            );

        $this->entityManager->persist($newsClicksCounting);
        $this->entityManager->flush();

        return $this->redirect('/news/short/' . $news->getId());
    }

    /**
     * @Route("/counting_news/short-to-full/{news}", name="front.counting_news_short_to_full")
     * @param News $news
     * @return Response
     */
    public function newsClicksCountingShortToFull(News $news)
    {
        $source = $this->entityManager->getRepository(Sources::class)->find($this->visitorInformation->getSource());

        $newsClicksCounting = new NewsClickShortToFull();
        $newsClicksCounting->setBuyer($this->entityManager->getRepository(User::class)->find($this->visitorInformation->getMediaBuyer()))
            ->setSource($source)
            ->setNews($news)
            ->setTrafficType($this->device)
            ->setUserIp($this->request->getClientIp())
            ->setUuid($this->visitorInformation->getUserUuid())
            ->setCountryCode($this->ip2location->getUserCountryCode())
            ->setDesign(
                $this->entityManager->getRepository(Design::class)->findOneBy([
                    'slug' => $this->visitorInformation->getDesign()
                ])
            )
            ->setAlgorithm(
                $this->entityManager->getRepository(Algorithm::class)->find(
                    $this->visitorInformation->getAlgorithm()
                )
            );

        $this->entityManager->persist($newsClicksCounting);
        $this->entityManager->flush();

        return $this->redirectToRoute('front.full_news', ['id' => $news->getId()]);
    }
}