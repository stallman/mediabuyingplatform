<?php

namespace App\EntityListeners;

use App\Entity\Partners;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class PartnerListener
{

    private $entityManager;
    private UrlGeneratorInterface $router;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $router)
    {
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $partner = $args->getObject();

        if (!$partner instanceof Partners) {
            return;
        }

        $partner->setPostback($this->generatePostBack($partner));
        $this->entityManager->flush();
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $partner = $args->getObject();

        if (!$partner instanceof Partners) {
            return;
        }

        $partner->setPostback($this->generatePostBack($partner));
    }

    private function generatePostBack(Partners $partner)
    {
        return "https://{$_ENV['DOMAIN_NAME']}{$this->router->generate('front.postback')}/?postback=1&ppid={$partner->getId()}&click_id={$partner->getMacrosUniqClick()}&status={$partner->getMacrosStatus()}&payout={$partner->getMacrosPayment()}&currency={$partner->getCurrency()->getIsoCode()}";
    }
}
