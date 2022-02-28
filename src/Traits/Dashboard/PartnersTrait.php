<?php


namespace App\Traits\Dashboard;

use App\Form as Form;
use App\Entity as Entity;
use Symfony\Component\Form\FormInterface;

trait PartnersTrait
{
    /**
     * @param Entity\Partners|null $partners
     * @return FormInterface
     */
    public function createPartnersForm(Entity\Partners $partners = null)
    {
        $partners = !$partners ? new Entity\Partners() : $partners;

        return $this
            ->createForm(Form\PartnersType::class, $partners, [])
            ->handleRequest($this->request);
    }
}