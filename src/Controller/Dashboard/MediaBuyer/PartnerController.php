<?php

namespace App\Controller\Dashboard\MediaBuyer;

use App\Controller\Dashboard\DashboardController;
use App\Entity\Partners;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PartnerController extends DashboardController
{
    /**
     * @Route("/mediabuyer/partner/list", name="mediabuyer_dashboard.partner_list")
     */
    public function listAction()
    {
        $columns = [
            [
                'label' => 'ID',
                'defaultTableOrder' => 'desc',
                'searching' => true,
                'sortable' => true
            ],
            [
                'label' => 'Название',
                'sortable' => false
            ],
            [
                'label' => 'Постбек',
                'sortable' => false
            ],
            [
                'label' => ''
            ]
        ];
        $partners = $news = $this->entityManager->getRepository(Partners::class)->getMediaBuyerPartnersList($this->getUser());

        return $this->render('dashboard/mediabuyer/partner/list.html.twig', [
            'columns' => $columns,
            'partners' => $partners,
            'h1_header_text' => 'Все партнерки',
            'new_button_label' => 'Добавить партнерку',
            'new_button_action_link' => $this->generateUrl('mediabuyer_dashboard.partner_add'),
        ]);
    }

    /**
     * @Route("/mediabuyer/partner/add", name="mediabuyer_dashboard.partner_add")
     */
    public function addAction()
    {
        $form = $this->createPartnersForm();

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Partners $formData */
            $formData = $form->getData();
            $formData->setUser($this->getUser());
            $formData->setPostback($_ENV['DOMAIN_NAME']);
            $this->entityManager->persist($formData);
            $this->entityManager->flush();

            return $this->redirectToRoute('mediabuyer_dashboard.partner_list', []);
        }

        return $this->render('dashboard/mediabuyer/partner/form.html.twig', [
            'h1_header_text' => 'Новая партнерка',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Partners $partners
     * @Route("/mediabuyer/partner/edit/{id}", name="mediabuyer_dashboard.partner_edit")
     */
    public function editAction(Partners $partners)
    {
        $form =$this->createPartnersForm($partners);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('mediabuyer_dashboard.partner_list', []);
        }

        return $this->render('dashboard/mediabuyer/news/form.html.twig', [
            'h1_header_text' => 'Редактировать партнерку',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mediabuyer/partner/delete/{id}", name="mediabuyer_dashboard.partner_delete")
     * @param Partners $partner
     * @return JsonResponse
     */
    public function deleteAction(Partners $partner)
    {
        try {
            $partner->setIsDeleted(true);

            $this->entityManager->flush();
            $this->addFlash('success', $this->getFlashMessage('partner_delete'));

            return JsonResponse::create('', 200);
        } catch (\Exception $exception) {

            return JsonResponse::create('Ошибка при удалении партнерки: ' . $exception->getMessage(), 500);
        }
    }

}
