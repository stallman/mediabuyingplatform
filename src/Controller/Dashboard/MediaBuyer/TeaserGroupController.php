<?php


namespace App\Controller\Dashboard\MediaBuyer;


use App\Controller\Dashboard\DashboardController;
use App\Entity\TeasersGroup;
use App\Entity\TeasersSubGroup;
use App\Traits\Dashboard\TeasersGroupTrait;
use App\Traits\Dashboard\TeasersSubGroupTrait;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class TeaserGroupController extends DashboardController
{
    use TeasersGroupTrait;
    use TeasersSubGroupTrait;

    /**
     * @Route("/mediabuyer/teaser_group/list", name="mediabuyer_dashboard.teaser_group_list")
     */
    public function listGroupAction()
    {
        $teaserGroups = $teaserGroups = $this->entityManager->getRepository(TeasersGroup::class)->getUserTeasersGroupList($this->getUser());

        return $this->render('dashboard/mediabuyer/teaser_group/list.html.twig', [
            'teaser_groups' => $teaserGroups,
            'columns' => $this->getTeasersGroupTableHeader(),
            'h1_header_text' => 'Все группы',
            'new_button_label' => 'Добавить группу',
            'new_button_action_link' => $this->generateUrl('mediabuyer_dashboard.teaser_group_add'),
        ]);
    }

    /**
     * @Route("/mediabuyer/teaser_group/json", name="mediabuyer_dashboard.teaser_group_json", methods={"GET"})
     */
    public function jsonGroupAction()
    {
        $teaserGroups = $teaserGroups = $this->entityManager->getRepository(TeasersGroup::class)->getUserTeasersGroupSubgroup($this->getUser());

        return JsonResponse::create($teaserGroups, 200);

    }

    /**
     * @Route("/mediabuyer/teasers_group/add", name="mediabuyer_dashboard.teaser_group_add")
     */
    public function addGroupAction()
    {
        $form = $this->createTeasersGroupForm();

        if($form->isSubmitted() && $form->isValid()){
            /** @var TeasersGroup $formData */
            $formData = $form->getData();
            $formData->setUser($this->getUser());
            $formData->setCreatedAt();
            $this->entityManager->persist($formData);
            $this->entityManager->flush();

            return $this->redirectToRoute('mediabuyer_dashboard.teaser_group_list', []);
        }

        return $this->render('dashboard/mediabuyer/teaser_group/form.html.twig', [
            'h1_header_text' => 'Добавить группу',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param TeasersGroup $teasersGroup
     * @Route("/mediabuyer/teasers_group/edit/{id}", name="mediabuyer_dashboard.teaser_group_edit")
     */
    public function editGroupAction(TeasersGroup $teasersGroup)
    {
        $form = $this->createTeasersGroupForm($teasersGroup);

        if($form->isSubmitted() && $form->isValid()){
            $formData = $form->getData();
            $this->entityManager->persist($formData);
            $this->entityManager->flush();

            return $this->redirectToRoute('mediabuyer_dashboard.teaser_group_list', []);
        }

        return $this->render('dashboard/mediabuyer/teaser/form.html.twig', [
            'h1_header_text' => 'Редактировать группу',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/mediabuyer/teasers_group/delete/{id}", name="mediabuyer_dashboard.teaser_group_delete")
     * @param TeasersGroup $teasersGroup
     * @return JsonResponse
     */
    public function deleteTeaserGroupAction(TeasersGroup $teasersGroup)
    {
       return $this->teaserGroupDelete($teasersGroup);
    }


    /**
     * @Route("/mediabuyer/teasers_group/{id}/sub-group/add", name="mediabuyer_dashboard.teaser_sub_group_add")
     * @param TeasersGroup $teasersGroup
     * @return RedirectResponse|Response
     */
    public function addSubGroupAction(TeasersGroup $teasersGroup)
    {
        return $this->addSubGroup($teasersGroup);
    }

    /**
     * @Route("/mediabuyer/sub-group/delete/{id}", name="mediabuyer_dashboard.teaser_sub_group_delete")
     * @param TeasersSubGroup $subGroup
     * @return JsonResponse
     */
    public function deleteTeaserSubGroupAction(TeasersSubGroup $subGroup)
    {
        return $this->subGroupDelete($subGroup);
    }

    /**
     * @Route("/mediabuyer/sub-group/edit/{id}", name="mediabuyer_dashboard.teaser_sub_group_edit")
     * @param TeasersSubGroup $subGroup
     * @return RedirectResponse|Response
     */
    public function editSubGroupAction(TeasersSubGroup $subGroup)
    {
        return $this->editSubGroup($subGroup);
    }

    /**
     * @Route("/mediabuyer/teasers-groups/bulk-set-active", name="mediabuyer_dashboard.teasers_groups_bulk_set_active", methods={"POST"})
     *
     * @return RedirectResponse
     */
    public function bulkSetActiveAction()
    {
        $groupList = $this->request->request->get('group');

        if(is_null($groupList)){
            return $this->redirectToRoute('mediabuyer_dashboard.teaser_group_list');
        }

        return $this->dualBulkSetActive($groupList, 'mediabuyer_dashboard.teaser_group_list');
    }

    /**
     * @Route("/mediabuyer/teasers-groups/bulk-set-disable", name="mediabuyer_dashboard.teasers_groups_bulk_set_disable", methods={"POST"})
     *
     * @return RedirectResponse
     */
    public function bulkSetDisableAction()
    {
        $groupList = $this->request->request->get('group');

        if(is_null($groupList)) return $this->redirectToRoute('mediabuyer_dashboard.teaser_group_list');

        return $this->dualBulkSetDisabled($groupList, 'mediabuyer_dashboard.teaser_group_list');
    }

    /**
     * @Route("/mediabuyer/teasers-groups/bulk-delete", name="mediabuyer_dashboard.teasers_groups_bulk_delete", methods={"POST"})
     *
     * @return RedirectResponse
     */
    public function bulkDeleteAction()
    {
        $groupList = $this->request->request->get('group');

        if(is_null($groupList)) return $this->redirectToRoute('mediabuyer_dashboard.teaser_group_list');

        return $this->dualBulkSafeDeleted($groupList, 'mediabuyer_dashboard.teaser_group_list');
    }
}