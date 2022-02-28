<?php

namespace App\Controller\Dashboard\MediaBuyer;

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
     * @Route("/mediabuyer/conversion/list", name="mediabuyer_dashboard.conversion_list")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        return $this->render('dashboard/mediabuyer/conversion/list.html.twig', [
            'columns' => $this->getConversionsTableHeader($this->generateUrl('mediabuyer_dashboard.conversion_list_ajax')),
            'h1_header_text' => 'Все лиды',
            'new_button_label' => 'Добавить лид',
            'new_button_action_link' => $this->generateUrl('mediabuyer_dashboard.conversion_add'),
        ]);
    }

    /**
     * @Route("/mediabuyer/conversion/add", name="mediabuyer_dashboard.conversion_add")
     */
    public function addAction()
    {
        $form = $this->createConversionForm();

        if($form->isSubmitted() && $form->isValid()){
            /** @var Conversions $formData */
            $formData = $form->getData();
            /** @var TeasersClick $teaserClick */
            $teaserClick = $formData->getTeaserClick();

            if($teaserClick && $teaserClick->getBuyer() == $this->getUser() && !$teaserClick->getConversions()){
                $addDate = $form->has('addDate') ? $form->get('addDate')->getViewData() : null;
                $formData = $this->setConversionData($formData, $teaserClick, $addDate);
                $formData->setUpdatedAt(null);
                $this->entityManager->persist($formData);
                $this->entityManager->flush();
                $this->addFlash('success', $this->getFlashMessage('conversion_add'));

                return $this->redirectToRoute('mediabuyer_dashboard.conversion_list', []);
            } else {
                $this->addFlash('error', $this->getFlashMessage('conversion_action_is_not_click_error', [$formData->getTeaserClick()->getId()]));
            }

            if(!$teaserClick){
                $this->addFlash('error', $this->getFlashMessage('conversion_action_is_not_click_error', [$formData->getTeaserClick()->getId()]));
            } elseif($teaserClick->getConversions()){
                $this->addFlash('error', $this->getFlashMessage('conversion_action_click_has_conversion'));
            }
        }

        return $this->render('dashboard/mediabuyer/conversion/form.html.twig', [
            'h1_header_text' => 'Новый лид',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mediabuyer/conversion/edit/{id}", name="mediabuyer_dashboard.conversion_edit")
     */
    public function editAction(Conversions $conversions)
    {
        $form = $this->createConversionForm($conversions);
        if($form->isSubmitted() && $form->isValid()){
            /** @var Conversions $formData */
            $formData = $form->getData();
            $currency = $formData->getAffilate()->getCurrency();
            $formData->setAmountRub($this->currencyConverter->convertCurrencies($currency->getId(),
                $formData->getAmount(), 4, $formData->getCreatedAt()->format('Y-m-d')));
            $teaserClick = $this->entityManager->getRepository(TeasersClick::class)->find($formData->getTeaserClick());
            if($teaserClick && $teaserClick->getBuyer() == $this->getUser()){
                $addDate = $form->has('addDate') ? $form->get('addDate')->getViewData() : null;
                $formData = $this->setConversionData($formData, $teaserClick, $addDate);
                $formData->setUpdatedAt(null);
                $this->entityManager->flush();
                $this->addFlash('success', $this->getFlashMessage('conversion_edit'));

                return $this->redirectToRoute('mediabuyer_dashboard.conversion_list', []);
            }
            $this->addFlash('error', $this->getFlashMessage('conversion_action_is_not_click_error', [$formData->getTeaserClick()->getId()]));

        }

        return $this->render('dashboard/mediabuyer/conversion/form.html.twig', [
            'h1_header_text' => 'Редактировать лид',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mediabuyer/conversion/list-ajax", name="mediabuyer_dashboard.conversion_list_ajax", methods={"GET"})
     */
    public function listAjaxAction()
    {
        $conversionsJson = [];
        $conversionsCount = $this->entityManager->getRepository(Conversions::class)->getMediaBuyerConversionsCount($this->getUser());
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
            $conversionsCount = $this->entityManager->getRepository(Conversions::class)->getMediaBuyerConversionsCount($this->getUser());
            $length = $this->request->query->get('length') == -1 ? $conversionsCount : $this->request->query->get('length');
            $conversions = $this->entityManager->getRepository(Conversions::class)->getMediaBuyerConversionsList($this->getUser(), $length, $start);
        }

        if($from && $to){
            $conversionsCount = $this->entityManager->getRepository(Conversions::class)->getMediaBuyerConversionsCountByDate($this->getUser(), $from, $to);
            $length = $this->request->query->get('length') == -1 ? $conversionsCount : $this->request->query->get('length');
            $conversions = $this->entityManager->getRepository(Conversions::class)->getMediaBuyerConversionsListByDate($this->getUser(), $length, $start, $from, $to);
        }

        /** @var Conversions $conversion */
        foreach($conversions as $conversion) {
            $conversionsJson[] = [
                $conversion->getTeaserClick()->getId(),
                $conversion->getAffilate()->getTitle(),
                $conversion->getSource() ? $conversion->getSource()->getTitle() : '',
                $conversion->getSubgroup()->getTeaserGroup()->getName() . ' - ' . $conversion->getSubgroup()->getName(),
                $conversion->getCountry()->getName(),
                $conversion->getStatus()->getLabelRu(),
                $conversion->getAmount(),
                $conversion->getCurrency()->getName(),
                DateHelper::formatDefaultDateTime( $conversion->getCreatedAt() ),
                $conversion->getUpdatedAt() ? DateHelper::formatDefaultDateTime( $conversion->getUpdatedAt() ): null,
                $this->getActionButtons($conversion, $actions = [
                    'edit' => $this->generateUrl('mediabuyer_dashboard.conversion_edit', ['id' => $conversion->getId()]),
                ])
            ];
        }

        return JsonResponse::create([
            'draw' => $draw,
            'recordsTotal' => $conversionsCount,
            'recordsFiltered' => $conversionsCount,
            'data' => $conversionsJson
        ], 200);
    }
}
