<?php


namespace App\Controller\Front;

use App\Entity\Design;
use App\Service\AccountingShow\PromoBlockNews;
use App\Service\AccountingShow\PromoBlockTeasers;
use App\Service\Algorithms\AlgorithmBuilder;
use App\Service\Algorithms\IAlgorithm;
use App\Service\Algorithms\ScreenAlgorithm;
use App\Service\VisitorInformation;
use App\Service\Ip2Location;
use App\Traits\DeviceTrait;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    use DeviceTrait;

    public EntityManagerInterface $entityManager;
    public ParameterBagInterface $parameters;
    public Request $request;
    public VisitorInformation $visitorInformation;
    public string $theme;
    public $container;
    public Ip2Location $ip2location;
    public PromoBlockNews $promoBlockNews;
    public PromoBlockTeasers $promoBlockTeasers;
    public ?string $device;
    public LoggerInterface $logger;
    public AlgorithmBuilder $algorithmBuilder;

    public function __construct(EntityManagerInterface $entityManager, Container $container,
                                ParameterBagInterface $parameters, LoggerInterface $logger, SessionInterface $session
    ) {
        $this->request = Request::createFromGlobals();
        $this->container = $container;
        $this->parameters = $parameters;
        $this->entityManager = $entityManager;
        $this->logger = $logger;

        $this->visitorInformation = new VisitorInformation($this->request, $this->entityManager, $session);
        $this->ip2location = new Ip2Location($this->request, $this->parameters);
        $this->theme =  $this->visitorInformation->getDesign() ? $this->visitorInformation->getDesign() : $_ENV['THEME'];
        $this->promoBlockNews = new PromoBlockNews($entityManager, $logger);
        $this->promoBlockTeasers = new PromoBlockTeasers($entityManager, $logger);
        $this->device = $this->getUserDevice();
        $this->promoBlockNews->setMediaBuyer((int) $this->visitorInformation->getMediaBuyer())
            ->setCountryCode($this->ip2location->getUserCountryCode())
            ->setSource($this->visitorInformation->getSource())
            ->setTrafficType($this->device)
            ->setAlgorithm((int)$this->visitorInformation->getAlgorithm())
            ->setDesign(
                $this->entityManager->getRepository(Design::class)->findOneBy([
                    'slug' => $this->visitorInformation->getDesign()
                ])->getId()
            );
        $this->promoBlockTeasers->setMediaBuyer((int) $this->visitorInformation->getMediaBuyer())
            ->setCountryCode($this->ip2location->getUserCountryCode())
            ->setSource($this->visitorInformation->getSource())
            ->setTrafficType($this->device)
            ->setAlgorithm((int)$this->visitorInformation->getAlgorithm())
            ->setDesign(
                $this->entityManager->getRepository(Design::class)->findOneBy([
                    'slug' => $this->visitorInformation->getDesign()
                ])->getId()
            );
        $this->algorithmBuilder = new AlgorithmBuilder();
    }

    /**
     * @Route("/autoreload", name="front.autoreload")
     */
    public function autoreload(): JsonResponse
    {
        $data = ['success' => false];

        try {
            $this->visitorInformation->setAutoreload(true);
            $data['success'] = true;
        } catch (\Throwable $e) {}

        return $this->json($data);
    }

    protected function getPage(IAlgorithm $algorithm, string $slug = null, string $key = null): int
    {
        $page = 1;

        if ($algorithm instanceof ScreenAlgorithm && null !== $slug) {
            $interaction = $this->visitorInformation->getInteraction($slug, $key);

            $topPageSorts       = [0 => 1, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6];
            $newsPageSorts      = [0 => 2, 1 => 2, 2 => 1, 3 => 3, 4 => 4, 5 => 5, 6 => 6];

            if ($slug === VisitorInformation::INTERACTION_NAME_NEWS) {
                $page = null === $key ? $newsPageSorts[$interaction] : $topPageSorts[$interaction] ;
            } else {
                $page = $topPageSorts[$interaction];
            }
        }

        return $page;
    }
}