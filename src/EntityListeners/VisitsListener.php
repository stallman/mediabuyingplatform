<?php

namespace App\EntityListeners;

use App\Entity\User;
use App\Entity\Visits;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;


class VisitsListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $visit = $args->getObject();
        $entityManager = $args->getEntityManager();

        if(!($visit instanceof Visits)){
            return;
        }

        $this->addCampaignIfNotExists($entityManager, $visit->getMediabuyer(), $visit->getUtmCampaign());
    }

    private function addCampaignIfNotExists(EntityManagerInterface $entityManager, ?User $mediabuyer = null, ?string $title = null): void
    {
        if (null === $mediabuyer) {
            return;
        }

        try {
            $conn = $entityManager->getConnection();
            $mediabuyerId = intval($mediabuyer->getId());
            $res = $conn->fetchOne("SELECT COUNT(*) AS count FROM campaign WHERE mediabuyer_id = $mediabuyerId AND title = '$title'");

            if (0 === intval($res)) {
                $conn->executeQuery("INSERT INTO campaign (mediabuyer_id, title) VALUES ($mediabuyerId, '$title')");
            }
        } catch (\Throwable $e) {
            $this->logger->error('Cannot add new campaign.', ['message' => $e->getMessage(), 'trace' => $e->getTrace()]);
        }
    }
}
