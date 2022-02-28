<?php


namespace App\Traits\Dashboard;

use App\Entity\Teaser;
use App\Form as Form;
use App\Entity as Entity;
use App\Traits\Dashboard\FlashMessagesTrait;
use Symfony\Component\Form\FormInterface;

trait TeasersTrait
{
    use FlashMessagesTrait;

    /**
     * @param Entity\Teaser|null $teaser
     * @return FormInterface
     */
    public function createTeaserForm(Entity\Teaser $teaser = null)
    {
        $teaser = !$teaser ? new Entity\Teaser() : $teaser;

        return $this
            ->createForm(Form\TeaserType::class, $teaser,  ['user' => $this->getUser()])
            ->handleRequest($this->request);
    }

    public function getTeaserTableHeader()
    {
        return [
            [
                'label' => 'ID',
                'defaultTableOrder' => 'desc',
                'sortable' => true,
                'searching' => true,
                'pagingServerSide' => true,
                'columnName' => 'teasers.id',
                'ajaxUrl' => $this->generateUrl('mediabuyer_dashboard.teaser_list_ajax')
            ],
            [
                'label' => 'Изображение',
            ],
            [
                'label' => 'Текст',
            ],
            [
                'label' => 'ГЕО',
            ],
            [
                'label' => 'Показы',
                'sortable' => true,
                'columnName' => 'statistic.teaserShow',
            ],
            [
                'label' => 'Клики',
                'sortable' => true,
                'columnName' => 'statistic.click',
            ],
            [
                'label' => 'Лиды',
                'sortable' => true,
                'columnName' => 'statistic.conversion',
            ],
            [
                'label' => 'Подтв.',
                'title' => 'Подтвержденные конверсии',
                'sortable' => true,
                'columnName' => 'statistic.approveConversion',
            ],
            [
                'label' => 'Аппрув',
                'sortable' => true,
                'columnName' => 'statistic.approve',
            ],
            [
                'label' => 'eCPM',
                'sortable' => true,
                'columnName' => 'statistic.eCPM',
            ],
            [
                'label' => 'EPC',
                'sortable' => true,
                'columnName' => 'statistic.EPC',
            ],
            [
                'label' => 'CTR',
                'sortable' => true,
                'columnName' => 'statistic.CTR',
            ],
            [
                'label' => 'CR',
                'sortable' => true,
                'columnName' => 'statistic.CR',
            ],
            [
                'label' => '',
            ],
        ];
    }

    public function setDropNews($form)
    {
        $dropNewsList = explode(",", $form->getData()->getDropNews());
        $dropNewsCurrentList = "";
        $dropNewsWrongList = "";
        if(array_filter($dropNewsList)){
            [$dropNewsCurrentList, $dropNewsWrongList] = $this->newsValidate($dropNewsList);
            $form->getData()->setDropNews($dropNewsCurrentList);
        } else {
            $form->getData()->setDropNews(null);
        }

        return [$dropNewsCurrentList, $dropNewsWrongList];
    }

    public function setDropSources($form)
    {
        $dropSourcesList = explode(",", $form->getData()->getDropSources());
        $dropSourcesCurrentList = "";
        $dropSourcesWrongList = "";
        if(array_filter($dropSourcesList)){
            [$dropSourcesCurrentList, $dropSourcesWrongList] = $this->sourcesValidate($dropSourcesList);
            $form->getData()->setDropSources($dropSourcesCurrentList);
        } else {
            $form->getData()->setDropSources(null);
        }

        return [$dropSourcesCurrentList, $dropSourcesWrongList];
    }

    /**
     * @param Teaser $teaser
     * @return string
     */

    public function getTeaserActive(Teaser $teaser)
    {
        return $teaser->getIsActive() ? 'active' : 'inactive';
    }

    private function newsValidate($dropNewsList)
    {
        $dropNewsCurrentList = [];
        $dropNewsWrongList = [];
        foreach($dropNewsList as $dropNews) {
            if(!is_numeric($dropNews)){
                $dropNews = preg_replace("/[^0-9]/", '', $dropNews);
            }
            $news = $this->entityManager->getRepository(Entity\News::class)->find($dropNews);
            if(!$news){
                $dropNewsWrongList [] = trim($dropNews);
                continue;
            }
            if($news->getUser() == $this->getUser() || $news->getType() == 'common'){
                $dropNewsCurrentList [] = trim($dropNews);
            } else {
                $dropNewsWrongList [] = trim($dropNews);
            }
        }

        return [implode(",", $dropNewsCurrentList), implode(",", $dropNewsWrongList)];
    }

    private function sourcesValidate($dropSourcesList)
    {
        $dropSourcesCurrentList = [];
        $dropSourcesWrongList = [];
        foreach($dropSourcesList as $dropSource) {
            if(!is_numeric($dropSource)){
                $dropSource = preg_replace("/[^0-9]/", '', $dropSource);
            }
            $source = $this->entityManager->getRepository(Entity\Sources::class)->find($dropSource);
            if(!$source || $source->getUser() != $this->getUser()){
                $dropSourcesWrongList [] = trim($dropSource);
                continue;
            }
            $dropSourcesCurrentList [] = trim($dropSource);
        }

        return [implode(",", $dropSourcesCurrentList), implode(",", $dropSourcesWrongList)];
    }

    public function dropItemsFlashes($dropNews, $dropNewsWrong, $dropSources, $dropSourcesWrong)
    {
        if(!empty($dropNews)){
            $this->addFlash('success', $this->getFlashMessage('news_mass_blocked_list', [$dropNews]));
        }

        if(!empty($dropNewsWrong)){
            $this->addFlash('error', $this->getFlashMessage('news_mass_blocked_list_error', [$dropNewsWrong]));
        }

        if(!empty($dropSources)){
            $this->addFlash('success', $this->getFlashMessage('sources_mass_blocked_list', [$dropSources]));
        }

        if(!empty($dropSourcesWrong)){
            $this->addFlash('error', $this->getFlashMessage('sources_mass_blocked_list', [$dropSourcesWrong]));
        }
    }
}