<?php

namespace App\Controller\Dashboard\MediaBuyer;

use App\Controller\Dashboard\DashboardController;
use App\Entity\BlackList;
use App\Entity\Visits;
use App\Entity\WhiteList;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class WhitelistController extends DashboardController
{
    /**
     * @Route("/mediabuyer/whitelist/add/{visit}", name="mediabuyer_dashboard.white_list_add", methods={"POST"}))
     */
    public function addAction(array $groups, array $item): int
    {
        /** @var BlackList[] $inBlackListItems */
        $inBlackListItems = $this->entityManager->getRepository(BlackList::class)->inBlackList($this->getUser(), $groups, $item);

        try{
            foreach ($inBlackListItems as $inBlackListItem) {
                $this->entityManager->remove($inBlackListItem);
            }

            $this->entityManager->flush();
        } catch(\Exception $exception) {
            return 0;
        }

        /** @var array $notInWhiteListItems */
        $notInWhiteListItems = $this->entityManager->getRepository(WhiteList::class)->notInWhiteList($this->getUser(), $groups, $item);

        try{
            $i = 0;
            foreach ($notInWhiteListItems as $notInWhiteListItem) {
                //$sortedField = explode(',', $notInWhiteListItem['field']);
                //sort($sortedField);
                $whiteList = new WhiteList();
                $whiteList->setBuyer($this->getUser())
                    //->setField(implode(',', $sortedField))
                    ->setGroupId($notInWhiteListItem['groupId'])
                    ->setGroupName($notInWhiteListItem['groupName'])
                ;

                $this->entityManager->persist($whiteList);
                $i++;
            }

            $this->entityManager->flush();

            return $i;
        } catch(\Exception $exception) {
            return 0;
        }
    }

    private function explodeData(array $subject): array
    {
        $newArray = [];
        foreach ($subject as $item) {
            $row = explode(',', $item);
            $newArray[] = $row;
        }
        return $newArray;
    }


    /**
     * @Route("/mediabuyer/whitelist/bulk-add/", name="mediabuyer_dashboard.bulk_white_list_add", methods={"POST"}))
     */
    public function bulkAddAction(): JsonResponse
    {
        $checkedGroups = $this->request->request->get('checkedGroups');
        $checkedItems = $this->request->request->get('checkedItems');

        $checkedGroups = $this->explodeData($checkedGroups);
        $checkedGroups = array_shift($checkedGroups);
        $checkedItems = $this->explodeData($checkedItems);

        $added = 0;
        foreach ($checkedItems as $item) {
            $added += $this->addAction($checkedGroups, $item);
        }

        $this->addFlash('success', $this->getFlashMessage('add_to_white_list_counter', [$added, count($checkedGroups)]));
        
        return JsonResponse::create(['route_to_redirect' => 'list']);
    }

    /**
     * @Route("/mediabuyer/whitelist/bulk-remove/", name="mediabuyer_dashboard.bulk_white_list_remove", methods={"POST"}))
     */
    public function bulkRemoveAction(): JsonResponse
    {
        $checkedItems = $this->request->request->get('checkedItems');
        $checkedGroups = $this->request->request->get('checkedGroups');

        $checkedGroups = $this->explodeData($checkedGroups);
        $checkedGroups = array_shift($checkedGroups);
        $checkedItems = $this->explodeData($checkedItems);


        $removed = 0;
        foreach ($checkedItems as $item) {
            $removed += $this->deleteAction($checkedGroups, $item);
        }

        $this->addFlash('success', $this->getFlashMessage('black_list_delete_counter', [$removed, count($checkedItems)]));

        return JsonResponse::create(['route_to_redirect' => 'list']);
    }

    /**
     * @Route("/mediabuyer/blacklist/delete/{id}", name="admin_dashboard.black_list_delete")
     */
    public function deleteAction(array $groups, array $item): int
    {
        /** @var WhiteList[] $items */
        $items = $this->entityManager->getRepository(WhiteList::class)->inWhiteList($this->getUser(), $groups, $item);

        try{
            foreach ($items as $item) {
                $this->entityManager->remove($item);
            }
            $this->entityManager->flush();
            return 1;
        } catch(\Exception $exception) {
            return 0;
        }
    }
}
