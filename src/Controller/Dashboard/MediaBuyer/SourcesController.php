<?php

namespace App\Controller\Dashboard\MediaBuyer;

use App\Controller\Dashboard\DashboardController;
use App\Entity\DomainParking;
use App\Entity\MediabuyerNews;
use App\Entity\News;
use App\Entity\Sources;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Traits\Dashboard\SourcesTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SourcesController extends DashboardController
{
    use SourcesTrait;

    /**
     * @Route("/mediabuyer/sources/list", name="mediabuyer_dashboard.sources_list")
     */
    public function listAction()
    {
        $sources = $this->entityManager->getRepository(Sources::class)->getMediaBuyerSourcesList($this->getUser());

        return $this->render('dashboard/mediabuyer/sources/list.html.twig', [
            'columns' => $this->getSourcesTableHeader(),
            'sources' => $sources,
            'h1_header_text' => 'Все источники',
            'new_button_label' => 'Добавить источник',
            'new_button_action_link' => $this->generateUrl('mediabuyer_dashboard.sources_add'),
        ]);
    }

    /**
     * @Route("/mediabuyer/sources/add", name="mediabuyer_dashboard.sources_add")
     * @return RedirectResponse|Response
     */
    public function addAction()
    {
        $form = $this->createSourceForm();

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var Sources $formData */
                $formData = $form->getData();
                $formData->setUser($this->getUser());
                $this->entityManager->persist($formData);
                $this->entityManager->flush();
                $this->addFlash('success', $this->getFlashMessage('source_create'));
                return $this->redirectToRoute('mediabuyer_dashboard.sources_list', []);
            } else {
                $this->addFlash('error', $this->getFlashMessage('source_create_error'));
            }

        }

        return $this->render('dashboard/mediabuyer/sources/form.html.twig', [
            'h1_header_text' => 'Новый источник',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Sources $source
     * @Route("/mediabuyer/sources/edit/{id}", name="mediabuyer_dashboard.sources_edit")
     * @return RedirectResponse|Response
     */
    public function editAction(Sources $source)
    {
        $form = $this->createSourceForm($source);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->entityManager->flush();
                $this->addFlash('success', $this->getFlashMessage('source_edit'));
                return $this->redirectToRoute('mediabuyer_dashboard.sources_list', []);
            } else {
                $this->addFlash('error', $this->getFlashMessage('source_edit_error'));
            }
        }

        return $this->render('dashboard/mediabuyer/sources/form.html.twig', [
            'h1_header_text' => 'Редактировать источник',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mediabuyer/sources/delete/{id}", name="mediabuyer_dashboard.sources_delete")
     * @param Sources $source
     * @return JsonResponse
     */
    public function deleteAction(Sources $source)
    {
        try {
            $source->setIsDeleted(true);

            $this->entityManager->flush();
            $this->addFlash('success', $this->getFlashMessage('source_delete'));

            return JsonResponse::create('', 200);
        } catch (\Exception $exception) {

            return JsonResponse::create($this->getFlashMessage('source_delete_error', [$exception->getMessage()]), 500);
        }
    }

    /**
     * @Route("/mediabuyer/sources/bulk-delete", name="mediabuyer_dashboard.sources_bulk_delete", methods={"POST"})
     *
     * @return mixed
     */
    public function bulkDeleteAction()
    {
        $checkedItems = $this->request->request->get('checkedItems');
        return $this->bulkSafeDelete(Sources::class, $checkedItems, $this->generateUrl('mediabuyer_dashboard.sources_list'));
    }

    /**
     * @Route("/mediabuyer/news_sources/{id}", name="news_sources_list")
     */
    public function getNewsSourcesListAction(News $news) {
        $mediabuyerNews = $this->entityManager->getRepository(MediabuyerNews::class)->findOneBy(
            [
                'mediabuyer' => $this->getUser(),
                'news' => $news
            ]
        );

        $qb = $this->entityManager->createQueryBuilder();
        $paramsForSelect = [
            'src.id',
            'src.title',
            'src.utm_campaign',
            'src.utm_term',
            'src.utm_content',
            'src.subid1',
            'src.subid2',
            'src.subid3',
            'src.subid4',
            'src.subid5'
        ];

        if ($mediabuyerNews) {
            $dropSources = $this->cleanDropSources($mediabuyerNews->getDropSources());
            if ($dropSources) {
                $sources =$this->getSourcesWithoutDropped($qb, $paramsForSelect, $dropSources);
            } else {
                $sources = $this->getAllSources($qb, $paramsForSelect);
            }
        } else {
            $sources = $this->getAllSources($qb, $paramsForSelect);
        }

        $links = $this->generateFullNewsSourceUrl($news, $sources);

        return JsonResponse::create($links);
    }

    private function cleanDropSources($dropSources)
    {
        $dropSourcesArr = array_filter(explode( "," , $dropSources));
        return implode(",", $dropSourcesArr);
    }

    private function getAllSources($qb, $paramsForSelect) {
        return $qb->select($paramsForSelect)
            ->from(Sources::class, 'src')
            ->andWhere('src.user = :user')
            ->andWhere('src.is_deleted = :isDeleted')
            ->setParameters([
                'user' => $this->getUser(),
                'isDeleted' => false
            ])
            ->getQuery()
            ->getResult();
    }

    private function getSourcesWithoutDropped($qb, $paramsForSelect, $dropSources)
    {
        return $qb->select('src')
            ->from(Sources::class, 'src')
            ->select($paramsForSelect)
            ->where($qb->expr()->notIn('src.id', $dropSources))
            ->andWhere('src.user = :user')
            ->andWhere('src.is_deleted = :isDeleted')
            ->setParameters([
                'user' => $this->getUser(),
                'isDeleted' => false
            ])
            ->getQuery()
            ->getResult();
    }

    private function generateFullNewsSourceUrl($news, $sources)
    {
        $fullUrlParams = [];
        $sourceLink = $this->generateSourceLink($news);
        $excludedParams = ['id', 'title'];

        foreach ($sources as $sourceParams) {
            $keyValueUrlParams = [];
            foreach ($sourceParams as $paramKey => $paramValue) {
                if(!empty($paramValue)) {
                    if (!in_array($paramKey, $excludedParams)) {
                        $keyValueUrlParams[] = $paramKey . '=' . $paramValue;
                    }
                    if ($paramKey == 'id') {
                        $keyValueUrlParams[] = "utm_source=" . $paramValue;
                    }
                }
            }
            
            $link = $sourceLink . '?' . implode('&', $keyValueUrlParams);
            $fullUrlParams[] = [
                'source_id' => $sourceParams['id'],
                'title' => $sourceParams['title'],
                'link' => $link,
            ];
        }
        return $fullUrlParams;
    }

    /**
     * @param News $news
     * @return string
     */
    private function generateSourceLink($news)
    {
        $mainDomain = $this->entityManager->getRepository(DomainParking::class)->findOneBy(['user' => $this->getUser(), 'is_main' =>1]);
        $sourceLink = ($mainDomain instanceof DomainParking) ? $mainDomain->getDomain() : $this->request->server->get('HTTP_HOST');
        return "https://" . $sourceLink . "/news/short/" . $news->getId();
    }
}
