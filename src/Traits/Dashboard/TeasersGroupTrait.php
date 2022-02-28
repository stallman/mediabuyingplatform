<?php


namespace App\Traits\Dashboard;

use App\Form as Form;
use App\Entity as Entity;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Traits\Dashboard\FlashMessagesTrait;

trait TeasersGroupTrait
{
    use FlashMessagesTrait;

    public function getTeasersGroupTableHeader()
    {
        // Дефолтная сортировка должна быть на колонке "Порядковый номер", asc.
        // Для других полей при этом сортировку лучше не использовать, иначе потеряется иерархия подгрупп.
      return [
          [
              'label' => 'Название',
          ],
          [
              'label' => 'Создана',
          ],
          [
              'label' => 'Тизеров',
          ],
          [
              'label' => 'Статус',
          ],
          [
              'label' => 'Порядковый номер',
              'hidden' => true,
              'defaultTableOrder' => 'asc',
              'paging' => 0
          ],
          [
              'label' => ''
          ],
      ];
    }

    /**
     * @param Entity\TeasersGroup|null $teasersGroup
     * @return FormInterface
     */
    public function createTeasersGroupForm(?Entity\TeasersGroup $teasersGroup = null)
    {
        $teasersGroup = !$teasersGroup ? new Entity\TeasersGroup() : $teasersGroup;

        return $this
            ->createForm(Form\TeasersGroupType::class, $teasersGroup)
            ->handleRequest($this->request);
    }

    public function teaserGroupDelete(Entity\TeasersGroup $teasersGroup)
    {
        try{
            if($this->checkTeasersSubGroup($teasersGroup) != 0) {
                $this->addFlash('error', $this->getFlashMessage('teaser_groups_delete_has_sub_groups_error'));

                return JsonResponse::create('', 200);
            }

            $teasersGroup->setIsDeleted(true);

            $this->entityManager->flush();
            $this->addFlash('success', $this->getFlashMessage('teaser_groups_delete'));

            return JsonResponse::create('', 200);
        } catch(\Exception $exception) {
            return JsonResponse::create($this->getFlashMessage('teaser_groups_delete_error', [$exception->getMessage()]), 500);
        }
    }

    private function checkTeasersSubGroup($teasersGroup)
    {
        $countSubGroup = $this->entityManager->getRepository(Entity\TeasersSubGroup::class)->countSubGroupByParent($teasersGroup);

        return $countSubGroup ? $countSubGroup : 0;
    }
}