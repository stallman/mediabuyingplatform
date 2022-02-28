<?php

namespace App\Controller\Dashboard\Journalist;

use App\Controller\Dashboard\NewsControllerInterface;
use App\Entity\News;
use App\Entity\NewsCategory;
use App\Traits\Dashboard\NewsTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class NewsController extends JournalistController implements NewsControllerInterface
{
    use NewsTrait;

    /**
     * @Route("/journalist/news/list", name="journalist_dashboard.news_list")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $categories = $this->entityManager->getRepository(NewsCategory::class)->getEnabledCategories();
        $columns = [
        [
            'label' => 'ID записи',
            'defaultTableOrder' => 'desc',
            'pagingServerSide' => true,
            'ajaxUrl' => $this->generateUrl('journalist_dashboard.news_list_ajax'),
            'columnName' => 'id',
            'sortable' => true,
            'searching' => true,
        ],
        [
            'label' => 'ID пользователя',
        ],
        [
            'label' => 'Тип',
        ],
        [
            'label' => 'Изображение',
        ],
        [
            'label' => 'Заголовок'
        ],
        [
            'label' => 'Ссылка',
        ],
        [
            'label' => 'Группы',
        ],
        [
            'label' => 'ГЕО',
        ],
        [
            'label' => ''
        ]
    ];

        return $this->render('dashboard/journalist/news/list.html.twig', [
            'columns' => $columns,
            'categories' => $categories,
            'h1_header_text' => 'Новости',
            'new_button_label' => 'Добавить новость',
            'new_button_action_link' => $this->generateUrl('journalist_dashboard.news_add'),
        ]);
    }

    /**
     * @Route("/journalist/news/add", name="journalist_dashboard.news_add")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction()
    {
        $form = $this->createNewsForm();

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var News $formData */
                $formData = $form->getData();
                $formData
                    ->setUser($this->getUser())
                    ->setType('common');
                $this->imageProcessor->validateImage($this->request, $form->getData());
                $this->entityManager->persist($formData);
                $this->entityManager->flush();
                $this->imageProcessor->checkFormImage($this->request, $form->getData());

                return $this->redirectToRoute('journalist_dashboard.news_list');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('dashboard/journalist/news/form.html.twig', [
            'form' => $form->createView(),
            'h1_header_text' => 'Добавить новость'
        ]);
    }

    /**
     * @Route("/journalist/news/edit/{id}", name="journalist_dashboard.news_edit")
     * @param News $news
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(News $news)
    {
        if ($this->getUser() !== $news->getUser()) {
            throw new AccessDeniedHttpException('Access denied.');
        }

        $form = $this->createNewsForm($news);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->imageProcessor->validateImage($this->request, $form->getData());
                $this->entityManager->flush();
                $this->imageProcessor->checkFormImage($this->request, $form->getData());

                return $this->redirectToRoute('journalist_dashboard.news_list');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('dashboard/journalist/news/form.html.twig', [
            'form' => $form->createView(),
            'h1_header_text' => 'Редактировать новость'
        ]);
    }

    /**
     * @Route("/journalist/news/delete/{id}", name="journalist_dashboard.news_delete")
     *
     * @param News $news
     *
     * @return JsonResponse
     */
    public function deleteAction(News $news)
    {
        if ($this->getUser() !== $news->getUser()) {
            throw new AccessDeniedHttpException('Access denied.');
        }

        try{
            $news->setIsDeleted(true);
            $this->entityManager->flush();
            $this->imageProcessor->deleteImage($news);
            $this->addFlash('success', $this->getFlashMessage('news_delete'));

            return JsonResponse::create('', 200);
        }catch (\Exception $exception) {

            return JsonResponse::create($this->getFlashMessage('news_delete_error', [$exception->getMessage()]), 500);
        }
    }

    /**
     * @Route("/journalist/news/list-ajax", name="journalist_dashboard.news_list_ajax", methods={"GET"})
     */
    public function listAjaxAction()
    {
        $newsJson = [];
        $categories = $this->request->query->get('news_categories');
        $search = $this->request->query->get('search')['value'];
        $order = $this->request->query->get('order');
        $draw = $this->request->query->get('draw');
        $start = $this->request->query->get('start');
        $newsCount = $this->entityManager->getRepository(News::class)->getJournalistNewsCount($this->getUser(), $categories, $search);
        $length = $this->request->query->get('length') == -1 ? $newsCount : $this->request->query->get('length');
        $news = $this->entityManager->getRepository(News::class)->getJournalistNewsPaginateList($this->getUser(), $order, $length, $start, $categories, $search);
        $newsActiveCount = $this->entityManager->getRepository(News::class)->getJournalistNewsCount($this->getUser(), $categories, $search, true);


        /** @var News $newsItem */
        foreach ($news as $newsItem) {
            $actions = [];

            if ($newsItem->getUser() === $this->getUser()) {
                $actions = [
                    'edit' => $this->generateUrl('journalist_dashboard.news_edit', ['id' => $newsItem->getId()]),
                    'delete' => $this->generateUrl('journalist_dashboard.news_delete', ['id' => $newsItem->getId()]),
                ];
            }

            $newsJson[] = [
                $newsItem->getId(),
                $newsItem->getUser()->getId(),
                $this->renderNewsTypeIcon($newsItem->getType()),
                $this->getImagePreview($newsItem),
                $newsItem->getTitle(),
                $this->getNewsLinks($newsItem),
                $this->newsCategoriesAsString($newsItem),
                $this->newsCountriesAsString($newsItem),
                $this->getActionButtons($newsItem, $actions),
                $this->getActive($newsItem)
            ];
        }

        return JsonResponse::create([
            'draw'  => $draw,
            'recordsTotal' =>  $newsCount,
            'recordsFiltered' =>  $newsCount,
            'data' =>  $newsJson,
            'countActiveData' => $newsActiveCount,
            'countInActiveData' => $newsCount - $newsActiveCount,
        ], 200);
    }

}