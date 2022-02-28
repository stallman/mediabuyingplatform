<?php


namespace App\Traits\Dashboard;

use App\Entity\TeasersGroup;
use App\Entity\TeasersSubGroup;
use App\Form as Form;
use App\Entity as Entity;
use Symfony\Component\Form\FormInterface;
use App\Entity\Teaser;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Traits\Dashboard\FlashMessagesTrait;

trait TeasersSubGroupTrait
{
    use FlashMessagesTrait;

    /**
     * @param Entity\TeasersSubGroup|null $teasersSubGroup
     * @param Entity\TeasersGroup|null $teasersGroup
     * @return FormInterface
     */
    public function createTeasersSubGroupForm(?Entity\TeasersSubGroup $teasersSubGroup = null, ?Entity\TeasersGroup $teasersGroup = null)
    {
        $teasersSubGroup = !$teasersSubGroup ? new Entity\TeasersSubGroup() : $teasersSubGroup;

        if(!$teasersSubGroup->getId()){
            $teasersSubGroup->getTeasersSubGroupSettings()[0] = new Entity\TeasersSubGroupSettings();
            $teasersSubGroup->setTeaserGroup($teasersGroup);

            $newsCategories = $this->entityManager->getRepository(Entity\NewsCategory::class)->findAll();
            foreach($newsCategories as $category) {
                $teasersSubGroup->addNewsCategory($category);
            }
        }
        return $this
            ->createForm(Form\TeasersSubGroupType::class, $teasersSubGroup)
            ->handleRequest($this->request);
    }

    public function addSubGroup(TeasersGroup $teasersGroup)
    {
        $form = $this->createTeasersSubGroupForm(null, $teasersGroup);

        if($form->isSubmitted() && $form->isValid()){
            $form = $this->getUniqGeo($form);
            /** @var TeasersSubGroup $teaserSubGroup */
            $teaserSubGroup = $form->getData();
            $teaserSubGroup->setTeaserGroup($teasersGroup);

            /** @var Entity\TeasersSubGroupSettings $item */
            foreach ($teaserSubGroup->getTeasersSubGroupSettings()->toArray() as &$item) {
                $item->setTeasersSubGroup($teaserSubGroup);
            }

            $this->entityManager->persist($teaserSubGroup);
            $this->entityManager->flush();

            $this->addFlash('success', $this->getFlashMessage('teasers_sub_group_create'));

            return $this->redirectToRoute('mediabuyer_dashboard.teaser_group_list', []);
        }

        return $this->render('dashboard/mediabuyer/teaser_group/form.html.twig', [
            'h1_header_text' => "Подгруппа будет добавлена в группу {$teasersGroup->getName()}",
            'form' => $form->createView(),
        ]);
    }

    public function subGroupDelete(Entity\TeasersSubGroup $subGroup)
    {
        try{
            if($this->checkTeasers($subGroup) != 0){
                $this->addFlash('error', $this->getFlashMessage('teasers_sub_group_delete_error'));

                return JsonResponse::create('', 200);
            }

            $subGroup->setIsDeleted(true);
            $this->entityManager->flush();

            $this->addFlash('error', $this->getFlashMessage('teasers_sub_group_delete'));

            return JsonResponse::create('', 200);
        } catch(\Exception $exception) {

            return JsonResponse::create('Ошибка при удалении подгруппы: ' . $exception->getMessage(), 500);
        }
    }

    private function checkTeasers(Entity\TeasersSubGroup $subGroup)
    {
        return $this->entityManager->getRepository(Teaser::class)->getCountTeasersBySubGroup($subGroup);
    }

    public function editSubGroup(TeasersSubGroup $subGroup)
    {
        $form = $this->createForm(Form\TeasersSubGroupType::class, $subGroup)->handleRequest($this->request);

        if($form->isSubmitted() && $form->isValid()){

            $form = $this->getUniqGeo($form);
            $this->checkTeaserSubGroup($form, $subGroup);

            $this->entityManager->flush();
            $this->addFlash('success', $this->getFlashMessage('teasers_sub_group_edit'));

            return $this->redirectToRoute('mediabuyer_dashboard.teaser_group_list', []);
        }

        return $this->render('dashboard/mediabuyer/teaser_group/form.html.twig', [
            'h1_header_text' => "Редактирование подгруппы",
            'form' => $form->createView(),
        ]);
    }

    private function checkTeaserSubGroup(FormInterface $form, TeasersSubGroup $subGroup)
    {
        /** @var Entity\TeasersSubGroupSettings $item */
        foreach ($form->getData()->getTeasersSubGroupSettings()->toArray() as &$item) {
            if (!$item->getTeasersSubGroup()) {
                $item->setTeasersSubGroup($subGroup);
            }
        }
    }

    private function getSubGroupWithDefaultSetting(TeasersSubGroup $subGroup)
    {
        $defaultSettings = $this->entityManager->getRepository(Entity\TeasersSubGroupSettings::class)->getDefaultSubGroupSettings($subGroup);
        $defaultSettings = !$defaultSettings ? new Entity\TeasersSubGroupSettings() : $defaultSettings;

        $subGroup->addTeasersSubGroupSetting($defaultSettings);

        return $subGroup;
    }

    private function getUniqGeo(FormInterface $form)
    {
        $geoList = [];
        /** @var Entity\TeasersSubGroupSettings $settingsItem */
        foreach ($form->getData()->getTeasersSubGroupSettings()->toArray() as $key => $settingsItem)
        {
            if (!$settingsItem->getGeoCode()){
                $geoList[$key] = null;
                continue;
            }
            $geoList[$key] = $settingsItem->getGeoCode()->getIsoCode();
        }

        $notUniqList = array_diff_key($form->getData()->getTeasersSubGroupSettings()->toArray(), array_unique(array_reverse($geoList, true)));

        foreach ($notUniqList as $key => $value)
        {
            unset($form->getData()->getTeasersSubGroupSettings()[$key]);
        }

        return $form;
    }
}