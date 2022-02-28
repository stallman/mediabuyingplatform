<?php

namespace App\Controller\Dashboard\MediaBuyer;

use App\Controller\Dashboard\DashboardController;
use App\Entity\BlackList;
use App\Entity\Visits;
use App\Entity\Sources;
use App\Entity\WhiteList;
use App\Form\BlackWhiteListType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class BlacklistController extends DashboardController
{
    const DATA_TYPE = [
        'getUtmTerm',
        'getUtmContent',
        'getUtmCampaign',
        'Тизеры (новостник)',
        'getSubid1',
        'getSubid2',
        'getSubid3',
        'getSubid4',
        'getSubid5'
    ];

    /**
     * @Route("/mediabuyer/blacklist/list", name="mediabuyer_dashboard.black_list")
     */
    public function listAction()
    {
        $BlackWhiteListForm = $this->createForm(BlackWhiteListType::class, null, ['user' => $this->getUser(),
            'action' => $this->generateUrl('mediabuyer_dashboard.black_get'),
            'method' => 'POST',
            'attr' => [
                'id' => 'black-white-lists-form'
            ]
        ])->handleRequest($this->request);

        return $this->render('dashboard/mediabuyer/blacklist/list.html.twig', [
            'h1_header_text' => 'Все листы',
            'blackWhiteListForm' => $BlackWhiteListForm->createView()
        ]);
    }

    /**
     * @Route("/mediabuyer/blacklist/add/{visit}", name="mediabuyer_dashboard.black_list_add", methods={"POST"}))
     */
    public function addAction(array $groups, array $item): int
    {
        /** @var WhiteList[] $inWhiteListItems */
        $inWhiteListItems = $this->entityManager->getRepository(WhiteList::class)->inWhiteList($this->getUser(), $groups, $item);

        try{
            foreach ($inWhiteListItems as $inWhiteListItem) {
                $this->entityManager->remove($inWhiteListItem);
            }

            $this->entityManager->flush();
        } catch(\Exception $exception) {
            return 0;
        }

        /** @var array $notInBlackListItems */
        $notInBlackListItems = $this->entityManager->getRepository(BlackList::class)->notInBlackList($this->getUser(), $groups, $item);

        try{
            $i = 0;
            foreach ($notInBlackListItems as $notInBlackListItem) {
                //$sortedField = explode(',', $notInBlackListItem['field']);
                //sort($sortedField);
                $blackList = new BlackList();
                $blackList->setBuyer($this->getUser())
                    //->setField(implode(',', $sortedField))
                    ->setGroupId($notInBlackListItem['groupId'])
                    ->setGroupName($notInBlackListItem['groupName'])
                ;

                $this->entityManager->persist($blackList);
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
     * @Route("/mediabuyer/blacklist/bulk-add/", name="mediabuyer_dashboard.bulk_black_list_add", methods={"POST"}))
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

        $this->addFlash('success', $this->getFlashMessage('add_to_black_list_counter', [$added, count($checkedGroups)]));

        return JsonResponse::create(['route_to_redirect' => 'list']);
    }

    /**
     * @Route("/mediabuyer/blacklist/bulk-remove/", name="mediabuyer_dashboard.bulk_black_list_remove", methods={"POST"}))
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
        /** @var BlackList[] $items */
        $items = $this->entityManager->getRepository(BlackList::class)->inBlackList($this->getUser(), $groups, $item);

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

    /**
     * @Route("/mediabuyer/blacklist/get", name="mediabuyer_dashboard.black_get")
     */
    public function getAction(): JsonResponse
    {
        $data_type = $this->request->request->get('data_type');
        if($data_type == 'news') return JsonResponse::create(' ', 500);

        $source = $this->entityManager->getRepository(Sources::class)->getMediaBuyerSource($this->getUser(), $this->request->request->get('source'));
        if(is_null($source)) {
            return JsonResponse::create(' ', 500);
        }

        $glue = $this->request->request->get('format') == 0 ? "\n" : ",";
        $listClass =  WhiteList::class;
        if($this->request->request->get('report_type') == 0){
            $listClass =  BlackList::class;
        }
        $data = $this->entityManager->getRepository($listClass)->getReport($this->getUser(), $this->request->request->get('campaign'), $source, $data_type, $glue);

        return JsonResponse::create($data == "" ? "Список пуст" : $data, 200);
    }
}
