<?php


namespace App\Controller\Front\News;


use App\Controller\Front\FrontController;
use App\Service\Algorithms\HiddenBlocksAlgorithm;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Entity\{Algorithm,
    Country,
    CropVariant,
    Design,
    Geo,
    Image,
    News,
    ShowNews,
    Sources,
    StatisticPromoBlockTeasers,
    Teaser,
    TeasersClick,
    User};
use App\Traits\DefaultImageTrait;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

abstract class AbstractNewsController extends FrontController
{
    use DefaultImageTrait;

    public const TEASER_BLOCK = "[teaser block]";
    public const TEASER_BLOCK_REGEX = "|\[teaser block\]|";
    protected string $pageType;
    protected User $mediaBuyer;
    protected CropVariant $cropVariant;
    protected Image $newsCroppedImage;
    protected ArrayCollection $teasers;

    public function __construct(EntityManagerInterface $entityManager, Container $container,
                                ParameterBagInterface $parameters, LoggerInterface $logger, SessionInterface $session
    ) {
        parent::__construct($entityManager, $container, $parameters, $logger, $session);
        $this->initialize();
    }

    protected function initialize()
    {
        $this
            ->setMediaBuyer()
            ->setCropVariant();

        return $this;
    }

    /**
     * @return $this
     */
    protected function setMediaBuyer()
    {
        $this->mediaBuyer = $this->entityManager->getRepository(User::class)->find($this->visitorInformation->getMediaBuyer());

        return $this;
    }

    /**
     * @return $this
     */
    protected function setCropVariant()
    {
        $theme = isset($_ENV['THEME']) && !empty($_ENV['THEME']) ? $_ENV['THEME'] : $this->theme;
        $this->cropVariant = $this->entityManager->getRepository(CropVariant::class)->find($this->getCurrentThemeNumber($theme));

        return $this;
    }

    protected function setNewsCroppedImage(News $news)
    {
        $newsCroppedImage = $this->entityManager->getRepository(Image::class)->getEntityImage($news);
        if(!$newsCroppedImage){
            $this->newsCroppedImage = $this->getDefaultImage();
        } else {
            $this->newsCroppedImage = $newsCroppedImage;
        }
        return $this;
    }

    protected function getCurrentThemeNumber(string $theme)
    {
        return str_replace("theme_", "", $theme);
    }

    /**
     * @return object[]
     */
    protected function getAllNews()
    {
        return $this->entityManager->getRepository(News::class)->findAll();
    }

    /**
     * @param News $news
     * @param string $pageType
     * @param User $mediaBuyer
     * @return $this
     */
    protected function createShowNews(News $news, string $pageType, User $mediaBuyer)
    {
        try{
            $showNews = new ShowNews();
            $showNews->setNews($news)
                ->setPageType($pageType)
                ->setMediabuyer($mediaBuyer)
                ->setUuid(Uuid::fromString($this->visitorInformation->getUserUuid()))
                ->setDesign($this->entityManager->getRepository(Design::class)->find(preg_replace('/[^0-9]/', '', $this->visitorInformation->getDesign())))
                ->setSource($this->entityManager->getRepository(Sources::class)->find($this->visitorInformation->getSource()))
                ->setAlgorithm($this->entityManager->getRepository(Algorithm::class)->find($this->visitorInformation->getAlgorithm()));

            $this->entityManager->persist($showNews);

            $this->entityManager->flush();
        } catch(\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $this;
    }

    /**
     * @param News $news
     * @return Teaser[]|Collection
     */
    protected function getTeasers(News $news, int $page = 1)
    {
        $algorithm = $this->algorithmBuilder->getInstance($this->visitorInformation->getAlgorithm());

        if($algorithm instanceof HiddenBlocksAlgorithm){
            $algorithm->setPageType($this->pageType);
        }

        $algorithm->setEntityManager($this->entityManager)
            ->setGeoCode($this->visitorInformation->getCountryCode())
            ->setTrafficType($this->device)
            ->setBuyerId($this->visitorInformation->getMediaBuyer())
            ->setSourceId($this->visitorInformation->getSource())
            ->setCacheService(new RedisAdapter(
                RedisAdapter::createConnection($_ENV['REDIS_URL']),
                '',
                $_ENV['CACHE_LIFETIME']
            ));
        $teasersArr = [];

        foreach($algorithm->getTeaserForNews($news, $page) as $key => $teaser) {
            if(isset($_ENV['TITLE_STAT_PARAMS']) && $_ENV['TITLE_STAT_PARAMS']){
                $teaserItem = $this->entityManager->getRepository(Teaser::class)->find($teaser['id']);
                $buyer = $this->entityManager->getRepository(User::class)->find($this->visitorInformation->getMediaBuyer());
                $country = $this->entityManager->getRepository(Country::class)->getCountryByIsoCode($this->visitorInformation->getCountryCode());

                $clickCount = count($this->entityManager->getRepository(TeasersClick::class)->getByTeaserAndTrafficType(
                    $teaserItem,
                    $buyer,
                    $this->device,
                    $country,
                ));
                $showCount = $this->entityManager->getRepository(StatisticPromoBlockTeasers::class)->getTeaserShowCountByTeaser(
                    $teaserItem,
                    $country->getIsoCode(),
                    $this->device,
                );

                $teaser['text'] = $key . ' ' . $teaser['id'] . ' ' . $teaser['text'] . " - {$teaser['e_cpm']}" . "/$showCount/$clickCount";
                if(isset($teaser['impressions'])){
                    $teaser['text'] = '*** ' . $teaser['text'];
                }
            }
            $teasersArr[] = $teaser;
        }
        return new ArrayCollection($teasersArr);
    }

    protected function setPreparedTeasers(News $news, int $page = 1)
    {
        if($news->getIsDeleted()){
            throw $this->createNotFoundException();
        }

        $this->teasers = $this->getTeasers($news, $page);

        if($this->teasers){
            $this->promoBlockTeasers->setPageType($this->pageType)
                ->setNews($news->getId());
            $this->promoBlockTeasers->saveStatistic($this->teasers);
        }

        return $this;
    }

    protected function getCity()
    {
        $userCity = $this->ip2location->getUserCity();
        $cityTranslate = $this->entityManager->getRepository(Geo::class)->getCityTranslate($this->ip2location->getUserCountry(), $userCity);

        return $cityTranslate ? $cityTranslate->getCityNameRu() : $userCity;
    }
}