<?php

namespace App\Controller\Dashboard\Admin;

use App\Controller\Dashboard\DashboardController;
use App\Entity\Conversions;
use App\Entity\TeasersClick;
use App\Traits\Dashboard\ConversionsTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\DateHelper;

class ConversionController extends DashboardController
{
    use ConversionsTrait;
    /**
     * @Route("/admin/conversion/list", name="admin_dashboard.conversion_list")
     */
    public function listAction()
    {
        return $this->render('dashboard/admin/conversion/list.html.twig', [
            'columns' => $this->getConversionsTableHeader($this->generateUrl('admin_dashboard.conversion_list_ajax')),
            'h1_header_text' => 'Все лиды',
            'new_button_label' => 'Добавить лиды',
            'new_button_action_link' => $this->generateUrl('admin_dashboard.conversion_add'),
        ]);
    }

    /**
     * @Route("/admin/conversion/add", name="admin_dashboard.conversion_add")
     */
    public function addAction()
    {
        $form = $this->createConversionForm();

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Conversions $formData */
            $formData = $form->getData();
            $teaserClick = $this->entityManager->getRepository(TeasersClick::class)->find($formData->getTeaserClick());
            if ($teaserClick && $teaserClick->getBuyer() == $formData->getMediabuyer() && !$teaserClick->getConversions()){
                $addDate = $form->has('addDate') ? $form->get('addDate')->getViewData() : null;
                $formData = $this->setConversionData($formData, $teaserClick, $addDate);
                $formData->setUpdatedAt(null);
                $this->entityManager->persist($formData);
                $this->entityManager->flush();
                $this->addFlash('success', $this->getFlashMessage('conversion_add'));

                return $this->redirectToRoute('admin_dashboard.conversion_list', []);
            }

            if(!$teaserClick){
                $this->addFlash('error', $this->getFlashMessage('conversion_action_is_not_click_error', [$formData->getTeaserClick()->getId()]));
            } elseif($teaserClick->getConversions()){
                $this->addFlash('error', $this->getFlashMessage('conversion_action_click_has_conversion'));
            }

        }

        return $this->render('dashboard/admin/conversion/form.html.twig', [
            'h1_header_text' => 'Новый лид',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/conversion/edit/{id}", name="admin_dashboard.conversion_edit")
     */
    public function editAction(Conversions $conversions)
    {
        $form = $this->createConversionForm($conversions);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Conversions $formData */
            $formData = $form->getData();
            $teaserClick = $this->entityManager->getRepository(TeasersClick::class)->find($formData->getTeaserClick());
            if ($teaserClick && $teaserClick->getBuyer() == $formData->getMediabuyer()){
                $addDate = $form->has('addDate') ? $form->get('addDate')->getViewData() : null;
                $formData = $this->setConversionData($formData, $teaserClick, $addDate);
                $formData->setUpdatedAt(null);
                $this->entityManager->flush();
                $this->addFlash('success', $this->getFlashMessage('conversion_edit'));

                return $this->redirectToRoute('admin_dashboard.conversion_list', []);
            }
            $this->addFlash('error', $this->getFlashMessage('conversion_action_is_not_click_error', [$formData->getTeaserClick()->getId()]));
        }

        return $this->render('dashboard/admin/conversion/form.html.twig', [
            'h1_header_text' => 'Редактировать лид',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/conversion/delete/{id}", name="admin_dashboard.conversion_delete")
     * @param Conversions $conversion
     * @return JsonResponse
     */
    public function deleteAction(Conversions $conversion)
    {
        try{
            $this->entityManager->remove($conversion);
            $this->entityManager->flush();
            $this->addFlash('success', $this->getFlashMessage('conversion_delete'));

            return JsonResponse::create('', 200);
        } catch(\Exception $exception) {

            return JsonResponse::create($this->getFlashMessage('conversion_delete_error', [$exception->getMessage()]), 500);
        }
    }

    /**
     * @Route("/admin/conversion/bulk-convert", name="admin_dashboard.conversion_bulk_convert", methods={"POST"})
     *
     * @return mixed
     */
    public function bulkConvertAction()
    {
        $checkedItems = $this->request->request->get('checkedItems');

        return $this->bulkConvert(Conversions::class, $checkedItems, $this->generateUrl('admin_dashboard.conversion_list'));
    }

    /**
     * @Route("/admin/conversion/bulk-delete", name="admin_dashboard.conversion_bulk_delete", methods={"POST"})
     *
     * @return mixed
     */
    public function bulkDeleteAction()
    {
        $checkedItems = $this->request->request->get('checkedItems');

        return $this->bulkDelete(Conversions::class, $checkedItems, $this->generateUrl('admin_dashboard.conversion_list'));
    }

    /**
     * @Route("/admin/conversion/list-ajax", name="admin_dashboard.conversion_list_ajax", methods={"GET"})
     */
    public function listAjaxAction()
    {
        $conversionsJson = [];
        $draw = $this->request->query->get('draw');
        $start = $this->request->query->get('start');
        $from = $this->request->query->get('from');
        $to = $this->request->query->get('to');
        $period = $this->request->query->get('period');

        if(isset($period) && !empty($period)){
            $period = $this->getPeriod($period);
            [$from, $to] = $period->getDateBetween();
        } elseif(isset($from) && !empty($from) && isset($to) && !empty($to)) {
            [$from, $to] = $this->convertDate($from, $to);
        } else {
            $conversionsCount = $this->entityManager->getRepository(Conversions::class)->getUnDeletedConversionsCount();
            $length = $this->request->query->get('length') == -1 ? $conversionsCount : $this->request->query->get('length');
            $conversions = $this->entityManager->getRepository(Conversions::class)->getUnDeletedConversionsList($length, $start);
        }

        if($from && $to){
            $conversionsCount = $this->entityManager->getRepository(Conversions::class)->getUnDeletedConversionsCountByDate($from, $to);
            $length = $this->request->query->get('length') == -1 ? $conversionsCount : $this->request->query->get('length');
            $conversions = $this->entityManager->getRepository(Conversions::class)->getUnDeletedConversionsListByDate($from, $to, $length, $start);
        }

        /** @var Conversions $conversion */
        foreach ($conversions as $conversion) {
            $conversionsJson[] = [$this->getBulkCheckBox($conversion),
                $conversion->getTeaserClick()->getId(),
                $conversion->getAffilate()->getTitle(),
                $conversion->getSource()? $conversion->getSource()->getTitle() : '',
                $conversion->getSubgroup()->getTeaserGroup()->getName() . ' - ' . $conversion->getSubgroup()->getName(),
                $conversion->getCountry()->getName(),
                $conversion->getStatus()->getLabelRu(),
                $conversion->getAmount(),
                $conversion->getCurrency()->getName(),
                DateHelper::formatDefaultDateTime( $conversion->getCreatedAt() ),
                $conversion->getUpdatedAt() ? DateHelper::formatDefaultDateTime( $conversion->getUpdatedAt() ): null,
                $this->getActionButtons($conversion, $actions = [
                    'edit' => $this->generateUrl('admin_dashboard.conversion_edit', ['id' => $conversion->getId()]),
                    'delete' => $this->generateUrl('admin_dashboard.conversion_delete', ['id' => $conversion->getId()]),
                ])
            ];
        }

        return JsonResponse::create([
            'draw'  => $draw,
            'recordsTotal' =>  $conversionsCount,
            'recordsFiltered' =>  $conversionsCount,
            'data' =>  $conversionsJson
        ], 200);
    }

    /**
     * @Route("/admin/conversion/buyer-by-click/{clickId}", name="admin_dashboard.get_buyer_by_click_id", methods={"POST"})
     *
     * @return mixed
     */
    public function getBuyerByClickId($clickId)
    {
        $click = $this->entityManager->getRepository(TeasersClick::class)->find($clickId);
        if ($click) {
            return JsonResponse::create(['buyerId' => $click->getBuyer()->getId()]);
        }
        return JsonResponse::create(['buyerId' => null]);
    }
}
