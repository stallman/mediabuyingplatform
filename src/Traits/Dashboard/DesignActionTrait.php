<?php


namespace App\Traits\Dashboard;

use App\Entity\CurrencyList;
use App\Entity\Design;
use App\Entity\MediabuyerDesigns;
use Symfony\Component\HttpFoundation\JsonResponse;

trait DesignActionTrait
{
    public function designList()
    {
        return $this->render('dashboard/mediabuyer/split-tests/design/list.html.twig', [
            'designs' => $this->entityManager->getRepository(Design::class)->getDesignWithStatistic($this->getUser()),
            'ruble' => $this->entityManager->getRepository(CurrencyList::class)->getByIsoCode('rub'),
            'columns' => $this->getDesignTableHeader(),
            'h1_header_text' => 'Дизайн страниц',
            'save_button_label' => 'Сохранить',
            'save_button_action_link' => $this->generateUrl('mediabuyer_dashboard.split_tests.design_active'),
        ]);
    }

    public function designActive(array $designs)
    {
        if(isset($designs['deactive'])) $this->designDeactivated($designs['deactive']);
        if(isset($designs['active'])) $this->designActivated($designs['active']);

        return JsonResponse::create(['route_to_redirect' => $this->generateUrl('mediabuyer_dashboard.split_tests.design_list')]);
    }

    private function designDeactivated(array $idList)
    {
        foreach($idList as $id) {
            $mediabuyerDesign = $this->entityManager->getRepository(MediabuyerDesigns::class)->getMediabuyerDesign($id, $this->getUser());
            if($mediabuyerDesign){
                $this->entityManager->remove($mediabuyerDesign);
                $this->entityManager->flush();
            }
        }
    }

    private function designActivated(array $idList)
    {
        foreach($idList as $id) {
            $mediabuyerDesign = $this->entityManager->getRepository(MediabuyerDesigns::class)->getMediabuyerDesign($id, $this->getUser());

            if(!$mediabuyerDesign){
                $mediabuyerDesign = new MediabuyerDesigns();
                $mediabuyerDesign->setDesign($this->entityManager->getRepository(Design::class)->find($id))
                    ->setMediabuyer($this->getUser());

                $this->entityManager->persist($mediabuyerDesign);
                $this->entityManager->flush();
            }
        }
    }

    private function getDesignTableHeader()
    {
        return [

            [
                'label' => ''
            ],
            [
                'label' => 'Скрин'
            ],
            [
                'label' => 'Название',
            ],
            [
                'label' => 'Пробив'
            ],
            [
                'label' => 'CTR тизеров'
            ],
            [
                'label' => 'Лиды'
            ],
            [
                'label' => 'Подтвержденные лиды'
            ],
            [
                'label' => 'EPC'
            ],
            [
                'label' => 'CR',
                'searching' => false,
                'paging' => 0,
                'binfo' => false
            ],
            [
                'label' => 'ROI'
            ]
        ];
    }
}