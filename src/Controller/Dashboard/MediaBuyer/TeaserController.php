<?php


namespace App\Controller\Dashboard\MediaBuyer;


use App\Controller\Dashboard\DashboardController;
use App\Entity\Conversions;
use App\Entity\EntityInterface;
use App\Entity\StatisticTeasers;
use App\Entity\Teaser;
use App\Entity\TeasersGroup;
use App\Entity\Image;
use App\Entity\TeasersSubGroup;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Twig\DashboardExtension;

class TeaserController extends DashboardController
{
    /**
     * @Route("/mediabuyer/teaser/list", name="mediabuyer_dashboard.teaser_list")
     */
    public function listAction()
    {
        return $this->render('dashboard/mediabuyer/teaser/list.html.twig', [
            'teaser_groups' => $this->entityManager->getRepository(TeasersGroup::class)->getUserTeasersGroupList($this->getUser()),
            'columns' => $this->getTeaserTableHeader(),
            'h1_header_text' => 'Тизеры',
            'new_button_label' => 'Добавить тизер',
            'new_button_action_link' => $this->generateUrl('mediabuyer_dashboard.teaser_add'),
        ]);
    }

    /**
     * @param Teaser $teaser
     * @Route("/mediabuyer/teaser/edit/{id}", name="mediabuyer_dashboard.teaser_edit")
     */
    public function editAction(Teaser $teaser)
    {
        $form = $this->createTeaserForm($teaser);
        if ($form->isSubmitted()) {
            $isValid = $form->isValid();
            if (!$isValid){
                $errorField = $form->getErrors(true)->current()->getOrigin()->getName();
                $errorMessage = $form->getErrors(true)->current()->getMessage();
                if ($errorField == 'image' && $errorMessage == 'Значение не должно быть пустым.') {
                    $image = $this->entityManager->getRepository(Image::class)->getEntityImage($teaser);
                    $isValid = $image ? true : false;
                }
            }
        }
        if($form->isSubmitted() && $isValid){

            try {
                $this->imageProcessor->validateImage($this->request, $form->getData());
                [$dropNews, $dropNewsWrong] = $this->setDropNews($form);
                [$dropSources, $dropSourcesWrong] = $this->setDropSources($form);
    
                $this->entityManager->flush();
                $this->imageProcessor->checkFormImage($this->request, $form->getData());
    
                $this->dropItemsFlashes($dropNews, $dropNewsWrong, $dropSources, $dropSourcesWrong);
                $this->addFlash('success', $this->getFlashMessage('teaser_edit'));
    
                return $this->redirectToRoute('mediabuyer_dashboard.teaser_list', []);
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('dashboard/mediabuyer/teaser/form.html.twig', [
            'h1_header_text' => 'Редактировать тизер',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mediabuyer/teaser/add", name="mediabuyer_dashboard.teaser_add")
     *
     * @return Response|RedirectResponse
     */
    public function addAction()
    {
        $form = $this->createTeaserForm();
        if ($form->isSubmitted()) {
            $isValid = $form->isValid();
            if (!$isValid){
                $errorField = $form->getErrors(true)->current()->getOrigin()->getName();
                $errorMessage = $form->getErrors(true)->current()->getMessage();
                if ($errorField == 'image' && $errorMessage == 'Значение не должно быть пустым.') {
                        $isValid = $this->request->files->has('image') && $this->request->files->get('image');
                }
            }
        }
        if ($form->isSubmitted() && $isValid) {
            try {
                /** @var Teaser $formData */
                $formData = $form->getData();

                [$dropNews, $dropNewsWrong] = $this->setDropNews($form);
                [$dropSources, $dropSourcesWrong] = $this->setDropSources($form);
                $formData->setUser($this->getUser());
                $this->imageProcessor->validateImage($this->request, $formData);
                $this->entityManager->persist($formData);
                $this->entityManager->flush();
                $this->imageProcessor->checkFormImage($this->request, $formData);

                $this->dropItemsFlashes($dropNews, $dropNewsWrong, $dropSources, $dropSourcesWrong);
                $this->addFlash('success', $this->getFlashMessage('teaser_create'));

                return $this->redirectToRoute('mediabuyer_dashboard.teaser_list', []);
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('dashboard/mediabuyer/teaser/form.html.twig', [
            'h1_header_text' => 'Добавить тизер',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mediabuyer/teaser/delete/{id}", name="mediabuyer_dashboard.teaser_delete")
     * @param Teaser $teaser
     * @return JsonResponse
     */
    public function deleteAction(Teaser $teaser)
    {
        try{
            $teaser->setIsActive(false);
            $teaser->setIsDeleted(true);

            $this->entityManager->flush();
            $this->addFlash('success', $this->getFlashMessage('teaser_delete'));

            return JsonResponse::create('', 200);
        } catch(\Exception $exception) {

            return JsonResponse::create('Ошибка при удалении тизера: ' . $exception->getMessage(), 500);
        }
    }

    /**
     * @Route("/mediabuyer/teaser/copy/{sourceTeaser}", name="mediabuyer_dashboard.teaser_copy")
     *
     * @param Teaser $sourceTeaser
     *
     * @return RedirectResponse
     */
    public function copyAction(Teaser $sourceTeaser)
    {
        $targetTeaser = clone $sourceTeaser;
        $targetTeaser->setText("Копия <{$targetTeaser->getText()}>");
        $image = $this->entityManager->getRepository(Image::class)->getEntityImage($sourceTeaser);
       
        try{
            $this->entityManager->persist($targetTeaser);
            $this->entityManager->flush();
            if ($image) {
                $this->imageProcessor->copyImage($sourceTeaser, $targetTeaser, $image);
            }

            $this->addFlash('success', $this->getFlashMessage('teaser_copy'));
        } catch(\Exception $exception) {
            $this->addFlash('error',  $this->getFlashMessage('teaser_copy_error'));
        }

        return $this->redirectToRoute('mediabuyer_dashboard.teaser_list', []);

    }

    /**
     * @Route("/mediabuyer/teaser/list-ajax", name="mediabuyer_dashboard.teaser_list_ajax", methods={"GET"})
     */
    public function listAjaxAction(Request $request, DashboardExtension $twigExtensions)
    {
        $json = [];
        $order = $this->request->query->get('order');
        // если порядок пуст то выводить id desc
        $draw = $this->request->query->get('draw');
        $start = $this->request->query->get('start');
        $search = $this->request->query->get('search')['value'];
        $teasersGroupSubGroupIds = $this->request->query->get('groupSubGroupId');

        $filterSubGroups = [];
        if ($teasersGroupSubGroupIds){
            foreach($teasersGroupSubGroupIds as $teasersGroupSubGroupId){
                if(substr($teasersGroupSubGroupId, 0, 1) === 'g'){
                    $teasersGroupId = substr($teasersGroupSubGroupId, 1);
                    $teasersSubGroups = $this->entityManager->getRepository(TeasersSubGroup::class)->findBy(['teaserGroup' => $teasersGroupId]);

                    foreach( $teasersSubGroups as $teasersSubGroup ){
                        $filterSubGroups[] = $teasersSubGroup->getId();
                    }

                } else if($teasersGroupSubGroupId !== ''){
                    $teasersSubGroupId  = (int)$teasersGroupSubGroupId;
                    $filterSubGroups[] = $teasersSubGroupId;
                }
            }
        }

        $count = $this->entityManager->getRepository(Teaser::class)->getCountTeasers($this->getUser(), array_unique($filterSubGroups), $search);
        $length = $this->request->query->get('length') == -1 ? $count : $this->request->query->get('length');

        $teasers = $this->entityManager->getRepository(Teaser::class)
            ->getTeasersPaginateList($this->getUser(), $order, $length, $start, array_unique($filterSubGroups), $search);
        $count = $this->entityManager->getRepository(Teaser::class)->getCountTeasers($this->getUser(), array_unique($filterSubGroups), $search);
        $activeCount = $this->entityManager->getRepository(Teaser::class)->getCountTeasers($this->getUser(), array_unique($filterSubGroups), $search, true);

        /** @var Teaser $teasersItem */
        foreach ($teasers as $teasersItem) {
            $statistic = $this->statistic($teasersItem);

            $json[] = [
                $twigExtensions->renderBulkItemCheckboxWithoutRules(
                    $request->attributes->get('_route'),
                    $teasersItem),
                $teasersItem->getId(),
                $this->getImagePreview($teasersItem),
                $teasersItem->getText(),
                $twigExtensions->renderTeasersGeoList($teasersItem),
                $statistic->getTeaserShow(),
                $statistic->getClick(),
                $statistic->getConversion(),
                $statistic->getApproveConversion(),
                ((intval($statistic->getConversion())) ? round(intval($statistic->getApproveConversion()) / intval($statistic->getConversion()) * 100, 2) : 0) . '%',
                round(floatval($this->convertToUserCurrency($statistic->getECPM(), $this->getUser())), 2),
                round(floatval($this->convertToUserCurrency($statistic->getEPC(), $this->getUser())), 2),
                round(floatval($statistic->getCTR()), 2) . '%',
                round(floatval($statistic->getCR()), 2) . '%',
                $this->getActionButtons($teasersItem, $actions = [
                    'edit' => $this->generateUrl('mediabuyer_dashboard.teaser_edit', ['id' => $teasersItem->getId()]),
                    'copy' => $this->generateUrl('mediabuyer_dashboard.teaser_copy', ['sourceTeaser' => $teasersItem->getId()]),
                    'delete' => $this->generateUrl('mediabuyer_dashboard.teaser_delete', ['id' => $teasersItem->getId()]),
                ]),
                $this->getTeaserActive($teasersItem)
            ];
        }

        return JsonResponse::create([
            'draw'  => $draw,
            'recordsTotal' => $count,
            'recordsFiltered' => $count,
            'data' => $json,
            'countActiveData' => $activeCount,
            'countInActiveData' => $count - $activeCount,
        ], 200);
    }
    /**
     * @Route("/mediabuyer/teaser/bulk-set-active", name="mediabuyer_dashboard.teaser_bulk_set_active", methods={"POST"})
     *
     * @return JsonResponse
     */
    public function bulkSetActiveAction()
    {
        $checkedItems = $this->request->request->get('checkedItems');

        return $this->bulkSetActive(Teaser::class, $checkedItems, $this->generateUrl('mediabuyer_dashboard.teaser_list'));
    }

    /**
     * @Route("/mediabuyer/teaser/bulk-set-disable", name="mediabuyer_dashboard.teaser_bulk_set_disable", methods={"POST"})
     *
     * @return JsonResponse
     */
    public function bulkSetDisableAction()
    {
        $checkedItems = $this->request->request->get('checkedItems');

        return $this->bulkSetDisable(Teaser::class, $checkedItems, $this->generateUrl('mediabuyer_dashboard.teaser_list'));
    }

    /**
     * @Route("/mediabuyer/teaser/bulk-delete", name="mediabuyer_dashboard.teaser_bulk_delete", methods={"POST"})
     *
     * @return mixed
     */
    public function bulkDeleteAction()
    {
        $checkedItems = $this->request->request->get('checkedItems');

        return $this->bulkSafeDelete(Teaser::class, $checkedItems, $this->generateUrl('mediabuyer_dashboard.teaser_list'));
    }

    /**
     * @Route("/mediabuyer/teaser/bulk-change-subgroup", name="mediabuyer_dashboard.teaser_bulk_change_subgroup", methods={"POST"})
     *
     * @return mixed
     */
    public function bulkChangeSubGroupsAction()
    {
        $checkedItems = $this->request->request->get('checkedItems')['checked_items'];
        $subGroup = $this->request->request->get('checkedItems')['sub_group'];

        return $this->bulkChangeTeasersSubGroup(Teaser::class, $checkedItems, $subGroup, $this->generateUrl('mediabuyer_dashboard.teaser_list'));
    }

    /**
     * @param EntityInterface $entity
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    private function getImagePreview(EntityInterface $entity)
    {
        /** @var Image $image */
        $image = $this->entityManager->getRepository(Image::class)->getEntityImage($entity);

        return $this->renderView('dashboard/partials/table/image_preview_size_generation.html.twig', [
            'item' => $image,
            'width' => 200,
            'height' => 100,
            'class' => 'teaser',
        ]);
    }

    public function statistic(Teaser $teaser)
    {
        $statistic = $teaser->getStatistic();

        if (null === $statistic) {
            $statistic = new StatisticTeasers();
            $statistic->setTeaser($teaser);
            $statistic->setApprove(0);
            $statistic->setApproveConversion(0);
            $statistic->setClick(0);
            $statistic->setConversion(0);
            $statistic->setCR(0);
            $statistic->setCTR(0);
            $statistic->setECPM(0);
            $statistic->setEPC(0);
            $statistic->setTeaserShow(0);
        }

        return $statistic;
    }
}