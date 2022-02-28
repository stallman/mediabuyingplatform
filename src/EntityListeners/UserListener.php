<?php

namespace App\EntityListeners;

use App\Entity\CurrencyList;
use App\Entity\User;
use App\Entity\UserSettings;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;


class UserListener
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $user = $args->getObject();

        if (!$user instanceof User) {
            return;
        }

        $this->generateEcrmViewCount('ecrm_teasers_view_count', $user);
        $this->generateEcrmViewCount('ecrm_news_view_count', $user);
        $this->generateDefaultUserCurrency('default_currency', $user);
    }

    private function generateEcrmViewCount($slug, User $user) {
        $userSettingsRepo = $this->entityManager->getRepository(UserSettings::class)->findBy(
            ['slug' => $slug, 'user' => $user]
        );

        if(empty($userSettingsRepo)) {
            $userSettings = new UserSettings();
            $userSettings->setUser($user);
            $userSettings->setSlug($slug);
            $userSettings->setValue(1000);
            $this->entityManager->persist($userSettings);
        } else {
            $userSettingsRepo[0]->setValue(1000);
        }

        $this->entityManager->flush();
    }

    private function generateDefaultUserCurrency($slug, User $user) {
        $userSettingsRepo = $this->entityManager->getRepository(UserSettings::class)->findBy(
            ['slug' => $slug, 'user' => $user]
        );
        $currency = $this->entityManager->getRepository(CurrencyList::class)->getByIsoCode('rub');

        if(empty($userSettingsRepo)) {
            $userSettings = new UserSettings();
            $userSettings->setUser($user);
            $userSettings->setSlug($slug);
            $userSettings->setValue($currency->getId());
            $this->entityManager->persist($userSettings);
        } else {
            $userSettingsRepo[0]->setValue(1000);
        }

        $this->entityManager->flush();
    }
}
