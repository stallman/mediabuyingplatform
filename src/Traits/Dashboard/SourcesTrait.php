<?php


namespace App\Traits\Dashboard;

use App\Form as Form;
use App\Entity as Entity;
use Symfony\Component\Form\FormInterface;

trait SourcesTrait
{
    /**
     * @param Entity\Sources|null $source
     * @return FormInterface
     */
    public function createSourceForm(Entity\Sources $source = null)
    {
        $source = !$source ? new Entity\Sources() : $source;

        return $this
            ->createForm(Form\SourceType::class, $source)
            ->handleRequest($this->request);
    }

    public function getSourcesTableHeader()
    {
        return [
            [
                'label' => 'ID',
                'defaultTableOrder' => 'desc',
                'searching' => true,
                'sortable' => true
            ],
            [
                'label' => 'Название',
                'sortable' => true
            ],
            [
                'label' => 'Активных тизеров',
                'sortable' => true
            ],
            [
                'label' => 'Заблокированных тизеров',
                'sortable' => true
            ],
            [
                'label' => ''
            ],
        ];
    }
}