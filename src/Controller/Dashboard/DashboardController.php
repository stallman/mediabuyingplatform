<?php

namespace App\Controller\Dashboard;

use App\Controller\Dashboard\Traits\ActionsTrait;
use App\Controller\Dashboard\Traits\BulkActionsTrait;
use App\Entity\EntityInterface;
use App\Entity\Image;
use App\Service\CurrencyConverter;
use App\Service\ImageProcessor;
use App\Service\VisitorInformation;
use App\Traits\Dashboard\DomainParkingTrait;
use App\Traits\Dashboard\PartnersTrait;
use App\Traits\Dashboard\SettingsTrait;
use App\Traits\Dashboard\TeasersTrait;
use App\Traits\Dashboard\FlashMessagesTrait;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Yaml\Yaml;

class DashboardController extends AbstractController
{
    use SettingsTrait;
    use BulkActionsTrait;
    use TeasersTrait;
    use DomainParkingTrait;
    use PartnersTrait;
    use ActionsTrait;
    use FlashMessagesTrait;

    public Request $request;
    public VisitorInformation $visitorInformation;
    public Yaml $yaml;
    public EntityManagerInterface $entityManager;
    public SluggerInterface $slugger;
    public ImageProcessor $imageProcessor;
    public LoggerInterface $logger;
    public CurrencyConverter $currencyConverter;

    public function __construct(Yaml $yaml, EntityManagerInterface $entityManager, SluggerInterface $slugger,
                                ImageProcessor $imageProcessor, LoggerInterface $logger,
                                CurrencyConverter $currencyConverter, SessionInterface $session
    ) {
        $this->request = Request::createFromGlobals();
        $this->yaml = $yaml;
        $this->entityManager = $entityManager;
        $this->visitorInformation = new VisitorInformation($this->request, $this->entityManager, $session);
        $this->slugger = $slugger;
        $this->imageProcessor = $imageProcessor;
        $this->logger = $logger;
        $this->visitorInformation->updateSessionCookie();
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * @param EntityInterface $entity
     * @return object|null
     */
    public function getEntityImage(EntityInterface $entity)
    {
        return $this->entityManager->getRepository(Image::class)->findOneBy([
            'entityFQN' => get_class($entity),
            'entityId' => $entity->getId(),
        ]);
    }

    public function convertToUserCurrency(float $price, UserInterface $user)
    {
        return $this->currencyConverter->convertRubleToUserCurrency($price, $user);
    }

}