<?php


namespace App\Traits\Dashboard;

use App\Form as Form;
use App\Entity as Entity;
use Symfony\Component\Form\FormInterface;

trait SettingsTrait
{
    /**
     * @param Entity\News|null $news
     * @param bool $showTypeSelector
     * @return FormInterface
     */
    public function createSettingsForm(Entity\User $user = null, bool $showTypeSelector = false)
    {
        $user = !$user? new Entity\News() : $user;

        return $this
            ->createForm(Form\SettingsType::class, $user)
            ->handleRequest($this->request);
    }
}