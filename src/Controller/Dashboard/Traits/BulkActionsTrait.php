<?php

namespace App\Controller\Dashboard\Traits;

use App\Entity\Conversions;
use App\Entity\EntityInterface;
use App\Entity\MediabuyerNewsRotation;
use App\Entity\News;
use App\Entity\Teaser;
use App\Entity\TeasersGroup;
use App\Entity\TeasersSubGroup;
use App\Traits\Dashboard\TeasersGroupTrait;
use App\Traits\Dashboard\TeasersSubGroupTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

trait BulkActionsTrait
{
    use TeasersSubGroupTrait;
    use TeasersGroupTrait;

    public function bulkConvert($entityFQN, array $items_ids, string $routeToRedirect)
    {
        $i = 0;
        foreach ($items_ids as $id) {
            $item = $this->entityManager->getRepository($entityFQN)->findOneBy(['id' => $id]);
            if ($item instanceof Conversions) {
                /** @var Conversions $item */
                if (null !== $item->getCurrency() && null !== $item->getCurrency()->getId() && null !== $item->getCreatedAt()) {
                    $amountRub = $this->currencyConverter->convertCurrencies($item->getCurrency()->getId(),
                        floatval($item->getAmount()), 4, $item->getCreatedAt()->format('Y-m-d'));

                    $item->setAmountRub($amountRub);
                    $i++;
                }
            }
        }

        $msg = 'Сконвертировано записей: ' . $i . ' из ' . count($items_ids);

        return $this->saveAndReturnJsonResponse($routeToRedirect, $msg);
    }

    /**
     * @param $entityFQN
     * @param array $items_ids
     * @param string $routeToRedirect
     * @return JsonResponse
     */
    public function bulkDelete($entityFQN, array $items_ids, string $routeToRedirect)
    {
        foreach ($items_ids as $id) {
            $item = $this->entityManager->getRepository($entityFQN)->findOneBy(['id' => $id]);
            $this->entityManager->remove($item);
            $this->imageProcessor->deleteImage($item);
        }

        return $this->saveAndReturnJsonResponse($routeToRedirect);
    }

    /**
     * @param $entityFQN
     * @param array $items_ids
     * @param string $routeToRedirect
     * @return JsonResponse
     */
    public function bulkSafeDelete($entityFQN, array $items_ids, string $routeToRedirect)
    {
        foreach ($items_ids as $id) {
            $item = $this->entityManager->getRepository($entityFQN)->findOneBy(['id' => $id]);
            $item->setIsDeleted(true);

            if($item instanceof News || $item instanceof Teaser){
                $this->imageProcessor->deleteImage($item);
            }
        }

        return $this->saveAndReturnJsonResponse($routeToRedirect);
    }

    /**
     * @param $entityFQN
     * @param array $items_ids
     * @param string $routeToRedirect
     * @return JsonResponse
     */
    public function bulkSetActive($entityFQN, array $items_ids, string $routeToRedirect)
    {
        foreach ($items_ids as $id) {
            $item = $this->entityManager->getRepository($entityFQN)->findOneBy(['id' => $id]);
            $item->setIsActive(true);
        }

        return $this->saveAndReturnJsonResponse($routeToRedirect);
    }

    /**
     * @param array $itemsList
     * @param string $routeToRedirect
     * @return RedirectResponse
     */
    public function dualBulkSetActive(array $itemsList, string $routeToRedirect)
    {
        foreach ($itemsList as $itemClassName => $idList) {
            foreach($idList as $id){
                $item = $this->entityManager->getRepository($itemClassName)->findOneBy(['id' => $id]);
                $item->setIsActive(true);
            }
        }

        return $this->saveAndRedirect($routeToRedirect, $itemsList, 'активированы');
    }

    /**
     * @param array $itemsList
     * @param string $routeToRedirect
     * @return RedirectResponse
     */
    public function dualBulkSetDisabled(array $itemsList, string $routeToRedirect)
    {
        foreach ($itemsList as $itemClassName => $idList) {
            foreach($idList as $id){
                $item = $this->entityManager->getRepository($itemClassName)->findOneBy(['id' => $id]);
                $item->setIsActive(false);
            }
        }

        return $this->saveAndRedirect($routeToRedirect, $itemsList, 'деактивированы');
    }

    /**
     * @param array $itemsList
     * @param string $routeToRedirect
     * @return RedirectResponse
     */
    public function dualBulkSafeDeleted(array $itemsList, string $routeToRedirect)
    {
        try {
            $this->deleteTeaserSubGroups($itemsList);
            $this->deleteTeaserGroups($itemsList);
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute($routeToRedirect);
    }

    private function deleteTeaserSubGroups($itemsList)
    {
        //Удаляем все подгруппы, если в них нет тизеров
        $undeleted = [];
        $deleted = [];

        if (isset($itemsList[TeasersSubGroup::class])) {
            $teasersSubGroups = $itemsList[TeasersSubGroup::class];
            foreach ($teasersSubGroups as $teasersSubGroupId) {
                if ($this->isSubGroupHaveTeasers($teasersSubGroupId)) {
                    $undeleted[] = $teasersSubGroupId;
                } else {
                    $teasersSubGroup = $this->entityManager->getRepository(TeasersSubGroup::class)->find($teasersSubGroupId);
                    $teasersSubGroup->setIsDeleted(true);
                    $this->entityManager->flush();
                    $deleted[] = $teasersSubGroupId; 
                }
            }    
        }

        if (count($undeleted) > 0) {
            $this->addFlash('error', "Нельзя удалить следующие подгруппы - " . implode(", ", $undeleted) . ". Сперва удалите или переместите их тизеры");
        }

        if (count($deleted) > 0) {
            $this->addFlash('success', "Удалены следующие подгруппы - " . implode(", ", $deleted) . ".");
        }
    }

    private function isSubGroupHaveTeasers($subGroupId)
    {
        $subGroup = $this->entityManager->getRepository(TeasersSubGroup::class)->find($subGroupId);
        return ($this->entityManager->getRepository(Teaser::class)->getCountTeasersBySubGroup($subGroup) > 0);
    }

    private function deleteTeaserGroups($itemsList)
    {

        $undeleted = [];
        $deleted = [];

        if (isset($itemsList[TeasersGroup::class])) {
            $teasersGroups = $itemsList[TeasersGroup::class];
            foreach ($teasersGroups as $teasersGroupId) {
                if ($this->isGroupHasSubGroups($teasersGroupId)) {
                    $undeleted[] = $teasersGroupId;
                } else {
                    $teasersGroup = $this->entityManager->getRepository(TeasersGroup::class)->find($teasersGroupId);               
                    $teasersGroup->setIsDeleted(true);
                    $this->entityManager->flush();
                    $deleted[] = $teasersGroupId; 
                }  
            }
        }

        if (count($undeleted) > 0) {
            $this->addFlash('error', "Нельзя удалить следующие группы - " . implode(", ", $undeleted) . ". Сперва удалите их подгруппы");
        }

        if (count($deleted) > 0) {
            $this->addFlash('success', "Удалены следующие группы - " . implode(", ", $deleted) . ".");
        }
    }

    private function isGroupHasSubGroups($groupId) {
        $group = $this->entityManager->getRepository(TeasersGroup::class)->find($groupId);
        return ($this->entityManager->getRepository(TeasersSubGroup::class)->countSubGroupByParent($group) > 0);
    }

    /**
     * @param $entityFQN
     * @param array $items_ids
     * @param string $routeToRedirect
     * @return JsonResponse
     */
    public function bulkSetDisable($entityFQN, array $items_ids, string $routeToRedirect)
    {
        foreach ($items_ids as $id) {
            $item = $this->entityManager->getRepository($entityFQN)->findOneBy(['id' => $id]);
            $item->setIsActive(false);
        }

        return $this->saveAndReturnJsonResponse($routeToRedirect);
    }

    /**
     * @param $entityFQN
     * @param array $items_ids
     * @param string $subGroupId
     * @param string $routeToRedirect
     * @return JsonResponse
     */
    public function bulkChangeTeasersSubGroup($entityFQN, array $items_ids, string $subGroupId, string $routeToRedirect)
    {
        $subGroup = $this->entityManager->getRepository(TeasersSubGroup::class)->findOneBy(['id' => $subGroupId]);

        foreach ($items_ids as $id) {
            $item = $this->entityManager->getRepository($entityFQN)->findOneBy(['id' => $id]);
            $item->setTeasersSubGroup($subGroup);
        }

        return $this->saveAndReturnJsonResponse($routeToRedirect, 'Установлена подгруппа '.$subGroup->getName());
    }

    /**
     * @param string $routeToRedirect
     * @return JsonResponse
     */
    private function saveAndReturnJsonResponse(string $routeToRedirect, ?string $message = 'Операция завершена успешно')
    {
        try {
            $this->entityManager->flush();
            $this->addFlash('success', $message ?? 'Операция завершена успешно');

        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return JsonResponse::create(['route_to_redirect' => $routeToRedirect]);
    }

    /**
     * @param string $routeToRedirect
     * @param array $itemsList
     * @param $action
     * @return RedirectResponse
     */
    private function saveAndRedirect(string $routeToRedirect,array $itemsList, $action)
    {
        try {
            $this->entityManager->flush();
            foreach($itemsList as $className => $idList){
                $idString = implode(",", $idList);

                if(stripos($className, 'sub') !== false){
                    $this->addFlash('success', $this->getFlashMessage('teasers_sub_groups_save_and_redirect', [$idString, $action]));
                } else {
                    $this->addFlash('success', $this->getFlashMessage('teasers_groups_save_and_redirect', [$idString, $action])) ;
                }

            }

        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute($routeToRedirect);
    }

    /**
     * @param array $deleteList
     * @param array $unDeleteList
     * @param string $object
     * @return RedirectResponse
     */
    private function addFlashesAfterDelete(array $deleteList, array $unDeleteList, string $object)
    {
        if(!empty($deleteList)){
            $deleteStr = implode(',', $deleteList);
            $this->addFlash('success', $this->getFlashMessage('teasers_groups_mass_delete', [$object, $deleteStr]));
        }

        if(!empty($unDeleteList)){
            $unDeleteStr = implode(',', $unDeleteList);
            if($object == 'группы'){
                $this->addFlash('error', $this->getFlashMessage('teasers_groups_mass_delete_error', [$unDeleteStr]));
            } else {
                $this->addFlash('error', $this->getFlashMessage('teasers_sub_groups_mass_delete_error', [$unDeleteStr]));
            }
        }
    }



    private function checkSubGroup(array $subGroupList)
    {
        $deleteList = [];
        $unDeleteList = [];
        foreach($subGroupList as $id){
            $subGroup = $this->entityManager->getRepository(TeasersSubGroup::class)->find($id);
            $count = $this->entityManager->getRepository(Teaser::class)->getCountTeasersBySubGroup($subGroup);

            if($count != 0){
                $unDeleteList [] = $id;
            } else {
                $deleteList [] = $id;
            }
        }
        return [$deleteList, $unDeleteList];
    }

    private function checkGroup(array $groupList)
    {
        $deleteList = [];
        $unDeleteList = [];
        foreach($groupList as $id){
            $group = $this->entityManager->getRepository(TeasersGroup::class)->find($id);
            $count = $this->entityManager->getRepository(TeasersSubGroup::class)->countSubGroupByParent($group);

            if($count != 0 ){
                $unDeleteList [] = $id;
            } else {
                $deleteList [] = $id;
            }
        }
        return [$deleteList, $unDeleteList];
    }

    /**
     * @param array $news_ids
     * @param string $routeToRedirect
     * @return JsonResponse
     */
    public function bulkDeleteBuyerNews(array $news_ids, string $routeToRedirect)
    {
        $skipNews = [];
        $msg = null;

        foreach($news_ids as $id) {
            $news = $this->entityManager->getRepository(News::class)->findOneBy(['id' => $id]);

            if($news->getUser() == $this->getUser() && $news->getType() == 'own'){
                $news->setIsDeleted(true);
                $this->changeBuyerNewsRotation($news, false);
                $this->imageProcessor->deleteImage($news);
            } else {
                $skipNews [] = $id;
            }

            if(!empty($skipNews)){
                $msg = $this->getFlashMessage('mediabuyer_news_delete_is_not_author_error', [implode(',', $skipNews)]);
            }
        }

        return $this->saveAndReturnJsonResponse($routeToRedirect, $msg);
    }

    /**
     * @param array $news_ids
     * @param bool $status
     * @param string $routeToRedirect
     * @return JsonResponse
     */
    public function bulkSetStatusBuyerNews(array $news_ids, bool $status, string $routeToRedirect)
    {
        foreach($news_ids as $id) {
            $news = $this->entityManager->getRepository(News::class)->findOneBy(['id' => $id]);

            if($news->getUser() == $this->getUser() && $news->getType() == 'own'){
                $news->setIsActive($status);
                $this->changeBuyerNewsRotation($news, $status);
            } elseif($news->getType() == 'common') {
                $this->changeBuyerNewsRotation($news, $status);
            }

        }

        return $this->saveAndReturnJsonResponse($routeToRedirect);
    }

    public function changeBuyerNewsRotation(News $news, $status)
    {
        $mediabuyerNewsRotation = $this->entityManager->getRepository(MediabuyerNewsRotation::class)->getMediaBuyerNewsRotationItem($this->getUser(), $news);

        if(!$mediabuyerNewsRotation){
            $mediabuyerNewsRotation = new MediabuyerNewsRotation();
            $mediabuyerNewsRotation->setMediabuyer($this->getUser())
            ->setNews($news);
        }

        $mediabuyerNewsRotation->setIsRotation($status);

        if(!$mediabuyerNewsRotation->getId()){
            $this->entityManager->persist($mediabuyerNewsRotation);
        }
    }

    /**
     * @param EntityInterface|int $entityItem
     */
    public function getBulkCheckBox($entityItem)
    {
        return $this->renderView('dashboard/partials/table/bulk_item_checkbox_without_rules.html.twig', [
            'item_id' => (is_int($entityItem)) ? $entityItem : $entityItem->getId(),
        ]);
    }

    public function getBulkCheckBoxUuid(string $uuid)
    {
        return $this->renderView('dashboard/partials/table/bulk_item_checkbox_without_rules.html.twig', [
            'item_id' => $uuid,
        ]);
    }

    public function getBulkCheckBoxUuids(array $uuids, array $groups = [], array $blackListGroups = [])
    {
        return $this->renderView('dashboard/partials/table/bulk_items_checkbox_without_rules.html.twig', [
            'item_ids' => $uuids,
            'groups' => $groups,
            'bl_groups' => array_keys($blackListGroups),
            'bl_ids' => array_values($blackListGroups),
        ]);
    }

    public function getBulkCheckBoxGroups(array $groups = [])
    {
        return $this->renderView('dashboard/partials/table/bulk_groups_checkbox_without_rules.html.twig', [
            'groups' => $groups,
        ]);
    }
}