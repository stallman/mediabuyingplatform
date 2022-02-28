<?php


namespace App\Controller\Dashboard\MediaBuyer;


use App\Controller\Dashboard\DashboardController;
use App\Controller\Dashboard\NewsControllerInterface;
use App\Entity\Conversions;
use App\Entity\MediabuyerNews;
use App\Entity\MediabuyerNewsRotation;
use App\Entity\News;
use App\Entity\NewsCategory;
use App\Traits\Dashboard\NewsTrait;
use App\Traits\SerializerTrait;
use App\Traits\Dashboard\NewsMediabuyerTrait;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class NewsController extends DashboardController implements NewsControllerInterface
{
    use SerializerTrait;
    use NewsMediabuyerTrait;
    use NewsTrait;

    /**
     * @Route("/mediabuyer/news/list", name="mediabuyer_dashboard.news_list")
     */
    public function listAction()
    {
        $categories = $this->entityManager->getRepository(NewsCategory::class)->getEnabledCategories();

        return $this->render('dashboard/mediabuyer/news/list.html.twig', [
            'columns' => $this->getNewsTableHeaderBuyerDashboard($this->generateUrl('mediabuyer_dashboard.news_list_ajax')),
            'categories' => $categories,
            'h1_header_text' => 'Новости',
            'new_button_label' => 'Добавить новость',
            'new_button_action_link' => $this->generateUrl('mediabuyer_dashboard.news_add'),
        ]);
    }

    /**
     * @param News $news
     * @Route("/mediabuyer/news/edit/{id}", name="mediabuyer_dashboard.news_edit")
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function editAction(News $news)
    {
        $form = $this->createMediaBuyerNewsForm($news, $this->isShowTypeSelector($news));

        if($form->isSubmitted() && $form->isValid()){
            try{
                /** @var News $formData */
                $formData = $form->getData();
                $this->imageProcessor->validateImage($this->request, $formData);
                $mediabuyerNewsFirstKey = $this->getArrayFirstKey($formData->getMediabuyerNews());
                [$dropTeasersList, $dropSourcesList] = $this->setMediabuyerNews($formData->getMediabuyerNews()[$mediabuyerNewsFirstKey], $formData);

                $this->changeBuyerNewsRotation($news, $formData->getIsActive());

                if (!$formData->getIsActive() && $news->getUser() === $this->getUser()) {
                    $mediabuyerNewsRotations = $this->entityManager
                        ->getRepository(MediabuyerNewsRotation::class)->findBy([
                            'news' => $news,
                        ]);

                    /** @var MediabuyerNewsRotation $mediabuyerNewsRotation */
                    foreach ($mediabuyerNewsRotations as $mediabuyerNewsRotation) {
                        $mediabuyerNewsRotation->setIsRotation($formData->getIsActive());
                    }
                }

                $this->entityManager->flush();
                $this->imageProcessor->checkFormImage($this->request, $formData);

                $this->addFlash('success', $this->getFlashMessage('news_edit'));
                $this->dropItemsMediaBuyerFlashes($dropTeasersList, $dropSourcesList);

                return $this->redirectToRoute('mediabuyer_dashboard.news_list', []);
            } catch(\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('dashboard/mediabuyer/news/form.html.twig', [
            'h1_header_text' => 'Редактировать новость',
            'form' => $form->createView(),
        ]);
    }

    public function isShowTypeSelector($news)
    {
        return ($news->getType() == 'own') ? true : false;
    }

    /**
     * @Route("/mediabuyer/news/add", name="mediabuyer_dashboard.news_add")
     *
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function addAction()
    {
        $form = $this->createMediaBuyerNewsForm();

        if($form->isSubmitted() && $form->isValid()){
            $mediabuyerNewsFirstKey = 0;
            try{
                /** @var News $formData */
                $formData = $form->getData();
                $this->imageProcessor->validateImage($this->request, $formData);
                $formData->setType('own')->setUser($this->getUser());
                [$dropTeasersList, $dropSourcesList] = $this->setMediabuyerNews($formData->getMediabuyerNews()[$mediabuyerNewsFirstKey], $formData);

                $this->entityManager->persist($formData);
                $this->entityManager->flush();
                $this->imageProcessor->checkFormImage($this->request, $formData);
                $this->addRotationRow($formData);

                $this->addFlash('success', $this->getFlashMessage('news_create'));
                if($dropTeasersList['current'] != ""){
                    $this->addFlash('success', $this->getFlashMessage('news_create_teasers_blocked', [$dropTeasersList['current']]));
                }
                if($dropTeasersList['wrong'] != ""){
                    $this->addFlash('error', $this->getFlashMessage('news_create_teasers_blocked_error', [$dropTeasersList['wrong']]));
                }
                if($dropSourcesList['current'] != ""){
                    $this->addFlash('success', $this->getFlashMessage('news_create_sources_blocked', [$dropSourcesList['current']]));
                }
                if($dropSourcesList['wrong'] != ""){
                    $this->addFlash('error', $this->getFlashMessage('news_create_sources_blocked_error', [$dropSourcesList['wrong']]));
                }

                return $this->redirectToRoute('mediabuyer_dashboard.news_list', []);
            } catch(\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }

        }

        return $this->render('dashboard/mediabuyer/news/form.html.twig', [
            'h1_header_text' => 'Добавить новость',
            'form' => $form->createView(),
        ]);
    }

    private function addRotationRow($news)
    {
        $mediabuyerNewsRotation = new MediabuyerNewsRotation();
        $mediabuyerNewsRotation->setMediabuyer($this->getUser())
                    ->setNews($news)
                    ->setIsRotation(true);

        $this->entityManager->persist($mediabuyerNewsRotation);
        $this->entityManager->flush();
    }

    /**
     * @Route("/mediabuyer/news/delete/{id}", name="mediabuyer_dashboard.news_delete")
     * @param News $news
     * @return JsonResponse
     */
    public function deleteAction(News $news)
    {
        try{
            $news->setIsDeleted(true);
            $this->changeBuyerNewsRotation($news, false);
            $this->entityManager->flush();
            $this->imageProcessor->deleteImage($news);
            $this->addFlash('success', $this->getFlashMessage('news_delete'));

            return JsonResponse::create('', 200);
        } catch(Exception $exception) {

            return JsonResponse::create('Ошибка при удалении новости: ' . $exception->getMessage(), 500);
        }
    }

    /**
     * @Route("/mediabuyer/news/bulk-delete", name="mediabuyer_dashboard.news_bulk_delete", methods={"POST"})
     *
     * @return mixed
     */
    public function bulkDeleteAction()
    {
        $checkedItems = $this->request->request->get('checkedItems');

        return $this->bulkDeleteBuyerNews($checkedItems, $this->generateUrl('mediabuyer_dashboard.news_list'));
    }

    /**
     * @Route("/mediabuyer/news/bulk-set-active", name="mediabuyer_dashboard.news_bulk_set_active", methods={"POST"})
     *
     * @return JsonResponse
     */
    public function bulkSetActiveAction()
    {
        $checkedItems = $this->request->request->get('checkedItems');

        return $this->bulkSetStatusBuyerNews($checkedItems, true, $this->generateUrl('mediabuyer_dashboard.news_list'));
    }

    /**
     * @Route("/mediabuyer/news/bulk-set-disable", name="mediabuyer_dashboard.news_bulk_set_disable", methods={"POST"})
     *
     * @return JsonResponse
     */
    public function bulkSetDisableAction()
    {
        $checkedItems = $this->request->request->get('checkedItems');

        return $this->bulkSetStatusBuyerNews($checkedItems, false, $this->generateUrl('mediabuyer_dashboard.news_list'));

    }

    /**
     * @Route("/mediabuyer/news/mediabuyer-news/active-rotation/{id}", name="mediabuyer_dashboard.news_active_rotation")
     * @param MediabuyerNewsRotation $mediabuyerNews
     * @return RedirectResponse|Response
     */
    public function activeRotation(MediabuyerNewsRotation $mediabuyerNewsRotation)
    {
        $this->changeMediaBuyerNewsRotation($mediabuyerNewsRotation, true);

        return $this->redirectToRoute("mediabuyer_dashboard.news_list");
    }

    /**
     * @Route("/mediabuyer/news/{id}/mediabuyer-news/active-rotation/", name="mediabuyer_dashboard.news_active_new_rotation")
     * @param News $news
     * @return RedirectResponse|Response
     */
    public function activeNewRotation(News $news)
    {
        $status = true;
        $mediabuyerNewsRotationRow = $this->entityManager->getRepository(MediabuyerNewsRotation::class)->findOneBy([
            'mediabuyer' => $this->getUser()->getId(),
            'news' => $news,
        ]);

        if ($mediabuyerNewsRotationRow) {
            $mediabuyerNewsRotation = $mediabuyerNewsRotationRow;
            $status = !$mediabuyerNewsRotationRow->getIsRotation();
        } else {
            $mediabuyerNewsRotation = new MediabuyerNewsRotation();
            $mediabuyerNewsRotation->setMediabuyer($this->getUser())
                ->setNews($news);
        }

        $this->changeMediaBuyerNewsRotation($mediabuyerNewsRotation, $status, true);


        return $this->redirectToRoute("mediabuyer_dashboard.news_list");
    }

    /**
     * @Route("/mediabuyer/news/mediabuyer-news/disabled-rotation/{id}", name="mediabuyer_dashboard.news_disabled_rotation")
     * @param MediabuyerNews $mediabuyerNews
     * @return RedirectResponse|Response
     */
    public function disabledRotation(MediabuyerNewsRotation $mediabuyerNewsRotation)
    {
        $this->changeMediaBuyerNewsRotation($mediabuyerNewsRotation, false);

        return $this->redirectToRoute("mediabuyer_dashboard.news_list");
    }

    /**
     * @Route("/mediabuyer/news/list-ajax", name="mediabuyer_dashboard.news_list_ajax", methods={"GET"})
     */
    public function listAjaxAction()
    {
        $newsJson = [];
        $categories = $this->request->query->get('news_categories') ?? [];
        $search = $this->request->query->get('search')['value'];
        $order = $this->request->query->get('order');
        $draw = $this->request->query->get('draw');
        $start = $this->request->query->get('start');
        $length = $this->request->query->get('length');
        [$newsItems, $newsCount, $newsActiveCount] = $this->entityManager->getRepository(News::class)
            ->getMediaBuyerNewsPaginated($this->getUser(), $categories, $search, $order, $start, $length);
        $topConversionsCount = $this->entityManager->getRepository(Conversions::class)
            ->countConversionsByPageType($this->getUser(), 'top');

        foreach($newsItems as $newsItem) {
            $id = intval($newsItem['id']);

            $newsJson[] = [
                $this->getBulkCheckBox($id),
                $id,
                $newsItem['user_id'],
                $this->renderNewsTypeIcon($newsItem['type']),
                $this->getImagePreviewByClassAndId('App\Entity\News', $id),
                $newsItem['title'],
                $this->getNewsLinks($id),
                $newsItem['categories'] ?? '',
                $newsItem['countries'],
                $newsItem['inner_show'],
                $newsItem['inner_click'],
                round(floatval($newsItem['inner_ctr']), 2) . '%',
                round(floatval($this->convertToUserCurrency(floatval($newsItem['inner_e_cpm']), $this->getUser())), 2),
                $newsItem['click'],
                $newsItem['uniq_visits'],
                $newsItem['click_on_teaser'],
                round(floatval($newsItem['probiv']), 2) . '%',
                $newsItem['conversion'],
                $newsItem['approve_conversion'],
                round(floatval($newsItem['approve']), 2) . '%',
                round(floatval($newsItem['involvement']), 2) . '%',
                round(floatval($this->convertToUserCurrency(floatval($newsItem['epc']), $this->getUser())), 2),
                round(floatval($newsItem['cr']), 2) . '%',
                $this->getActionButtonsById($id, $this->getMediaBuyerNewsActionsByArray($newsItem)),
                (boolval(intval($newsItem['is_active'])) && boolval(intval($newsItem['is_rotation']))) ? 'active' : 'inactive',
            ];
        }

        return JsonResponse::create([
            'draw' => $draw,
            'recordsTotal' => $newsCount,
            'recordsFiltered' => $newsCount,
            'data' => $newsJson,
            'countActiveData' => $newsActiveCount,
            'countInActiveData' => $newsCount - $newsActiveCount,
            'countTopConversions' => $topConversionsCount,
        ], 200);
    }
}