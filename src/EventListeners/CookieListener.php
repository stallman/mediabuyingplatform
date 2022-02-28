<?php

namespace App\EventListeners;

use App\Entity\DomainParking;
use App\Entity\News;
use App\Entity\Sources;
use App\Entity\User;
use App\Entity\Visits;
use App\Entity\Design;
use App\Entity\Algorithm;
use App\Service\Ip2Location;
use App\Service\VisitorInformation;
use App\Traits\DeviceTrait;
use App\Traits\TimeInformationTrait;
use App\UtmSourcesLogger;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class CookieListener implements EventSubscriberInterface
{
    use DeviceTrait;
    use TimeInformationTrait;

    private EntityManagerInterface $entityManager;
    private Request $request;
    private ParameterBagInterface $parameters;
    public Ip2Location $ip2location;
    public VisitorInformation $visitorInformation;
    private User $mediaBuyer;
    private UrlGeneratorInterface $urlGenerator;
    private LoggerInterface $logger;
    private SessionInterface $session;
    private \UAParser\Result\Client $userAgent;

    const DISREGARDED_VISIT = ['spider', 'Spider'];

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $parameters,
                                UrlGeneratorInterface $urlGenerator, LoggerInterface $logger, SessionInterface $session
    ) {
        $this->entityManager = $entityManager;
        $this->parameters = $parameters;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $logger;
        $this->session = $session;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if(!$event->isMasterRequest()){
            return;
        }

        $this->request = $event->getRequest();
        [$status, $response] = $this->postBackListener();

        if($status){
            return $response;
        }

        $this->visitorInformation = new VisitorInformation($this->request, $this->entityManager, $this->session);
        $this->ip2location = new Ip2Location($this->request, $this->parameters);

        // получение из реквеста метки источника трафика
        $this->visitorInformation->setUtmSource($this->request->query->get('utm_source'));
        $this->visitorInformation->setVisitCookiesFromSource();
        $this->setMediaBuyer();

        if(!$this->visitorInformation->getDesign()) $this->visitorInformation->setDesignCookie($this->mediaBuyer);
        if(!$this->visitorInformation->getAlgorithm()) $this->visitorInformation->setAlgorithmCookie($this->mediaBuyer);
        if(!$this->visitorInformation->getCountryCode()) $this->visitorInformation->setCountryCodeCookie($this->ip2location->getUserCountryCode());

        if(!$this->request->cookies->get('unique_index') || ($this->request->cookies->get('unique_index') && $this->request->get('utm_source'))){
            $this->userAgent = $this->parseUserAgent();
            if((!in_array($this->userAgent->device->brand, self::DISREGARDED_VISIT) &&
                $this->request->getMethod() === 'GET' && !UtmSourcesLogger::isExceptedUrl()) ||
                UtmSourcesLogger::isPostback()
            ) {
                $this->addVisit($this->visitorInformation->setUserIdCookie());
            }
        }
    }

    private function addVisit($uuid)
    {
        $domain = $this->entityManager->getRepository(DomainParking::class)->getDomainByName($this->request->server->get('HTTP_HOST'));

        $visit = new Visits();
        $visit->setUuid($uuid)
            ->setCountryCode($this->ip2location->getUserCountryCode())
            ->setCity($this->ip2location->getUserCity())
            ->setMediabuyer($this->mediaBuyer)
            ->setUtmTerm($this->request->query->get('utm_term')) // опциональная метка, id сайта, на котором будет отображаться реклама какой-либо новости
            ->setUtmMedium($this->request->query->get('utm_medium')) // метка типа оплаты трафика (например cpc – оплата за клик, cpm – оплата за 1000 показов)
            ->setUtmContent($this->request->query->get('utm_content')) // опциональная метка, id рекламного объявления, ссылающегося на какую-либо новость
            ->setUtmCampaign($this->request->query->get('utm_campaign')) // id или наименование рекламной кампании
            ->setIp($this->request->getClientIp())
            ->setTrafficType($this->getUserDevice())
            ->setOs($this->userAgent->os->family)
            ->setOsWithVersion($this->userAgent->os->toString())
            ->setBrowser($this->userAgent->ua->family)
            ->setBrowserWithVersion($this->userAgent->ua->toString())
            ->setMobileBrand($this->userAgent->device->brand)
            ->setMobileModel($this->userAgent->device->model)
            ->setMobileOperator(null)
            ->setSubid1($this->request->query->get('subid1'))
            ->setSubid2($this->request->query->get('subid2'))
            ->setSubid3($this->request->query->get('subid3'))
            ->setSubid4($this->request->query->get('subid4'))
            ->setSubid5($this->request->query->get('subid5'))
            ->setUserAgent($this->parseUserAgent()->originalUserAgent)
            ->setUrl($this->request->getUri())
            ->setDomain($domain)
            ->setTimesOfDay($this->getTimesOfDay(new \DateTime()))
            ->setDayOfWeek($this->getDayOfWeek(new \DateTime()))
            ->setDesign($this->entityManager->getRepository(Design::class)->findOneBy([
                'slug' => $this->visitorInformation->getDesign()
            ]))
            ->setAlgorithm($this->entityManager->getRepository(Algorithm::class)->find(
                $this->visitorInformation->getAlgorithm()
            ));

        if($this->visitorInformation->getSource()){
            $visit->setSource($this->entityManager->getRepository(Sources::class)->find($this->visitorInformation->getSource()));
        }

        if(stristr($this->request->getPathInfo(), 'news') != false) {
            $newsId = preg_replace('/[^0-9]/', '', $this->request->getPathInfo());
            $news = $this->entityManager->getRepository(News::class)->find($newsId);
            $visit->setNews($news);
        }

        $this->entityManager->persist($visit);
        $this->entityManager->flush();
    }

    private function setMediaBuyer()
    {
        $this->mediaBuyer = $this->entityManager->getRepository(User::class)->find($this->visitorInformation->getMediaBuyer());

        return $this;
    }

    private function postBackListener()
    {
        if($this->request->getPathInfo() == $this->urlGenerator->generate('front.postback')){
            return [true, null];
        }

        if($this->request->getPathInfo() == '/postback/'){
            $client = new Client();
            try{
                $response = $client->request('GET', $this->request->server->get('SERVER_NAME') . $this->urlGenerator->generate('front.postback') . '?' . $this->request->getQueryString());
                $this->logger->error($response->getBody());
            } catch(GuzzleException $e) {
                $this->logger->error($e->getMessage());
            }
            die();
        }

        return [false, null];
    }
}
