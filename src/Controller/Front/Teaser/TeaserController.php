<?php

namespace App\Controller\Front\Teaser;

use App\Controller\Front\FrontController;
use App\Entity\Country;
use App\Entity\Ip2locationCityMultilingual;
use App\Entity\Sources;
use App\Entity\StatisticPromoBlockTeasers;
use App\Entity\Teaser;
use App\Entity\Design;
use App\Entity\Algorithm;
use App\Entity\TeasersClick;
use App\Entity\News;
use App\Entity\TeasersSubGroup;
use App\Entity\TeasersSubGroupSettings;
use App\Entity\CropVariant;
use App\Entity\User;
use App\Entity\Visits;
use App\Service\Algorithms\HiddenBlocksAlgorithm;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\{Response, Session\SessionInterface};
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Annotation\Route;

class TeaserController extends FrontController
{
    protected CropVariant $cropVariant;
    protected string $pageType;
    protected ArrayCollection $teasers;

    public function __construct(EntityManagerInterface $entityManager, Container $container,
                                ParameterBagInterface $parameters, LoggerInterface $logger, SessionInterface $session
    ) {
        parent::__construct($entityManager, $container, $parameters, $logger, $session);

        $this->initialize();
    }

    /**
     * @return $this
     */
    protected function initialize()
    {
        $this
            ->setCropVariant();

        return $this;
    }

    /**
     * @param int $page
     * @return $this
     */
    protected function getTeasers(int $page = 1)
    {
        $this->teasers = $this->getTopTeasers($page);

        if($this->teasers){
            $this->promoBlockTeasers->setPageType($this->pageType);
            $this->promoBlockTeasers->saveStatistic($this->teasers);
        }

        return $this;
    }

    private function setCropVariant()
    {
        $this->cropVariant = $this->entityManager->getRepository(CropVariant::class)->find($this->getCurrentThemeNumber());

        return $this;
    }

    protected function getCurrentThemeNumber()
    {
        $theme = isset($_ENV['THEME']) && !empty($_ENV['THEME']) ? $_ENV['THEME'] : $this->visitorInformation->getDesign();

        $theme = str_replace("theme_", "", $theme);

        return $theme;
    }

    protected function getTopTeasers($page)
    {
        $algorithm = $this->algorithmBuilder->getInstance($this->visitorInformation->getAlgorithm());
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
        foreach($algorithm->getTeaserForTop($page) as $key => $teaser){
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

    protected function getCity()
    {
        $userCity = $this->ip2location->getUserCity();
        /** @var Ip2locationCityMultilingual $cityTranslate */
        $cityTranslate = $this->entityManager->getRepository(Ip2locationCityMultilingual::class)->findOneBy([
            'langCode' => 'RU',
            'countryName' => $this->ip2location->getUserCountry(),
            'cityName' => $userCity,
        ]);

        return $cityTranslate ? $cityTranslate->getLangCityName() : $userCity;
    }

    /**
     * @Route("/counting/{teaser}/{news}", name="front.counting_teasers",  defaults={"news" = "null"})
     * @param Teaser $teaser
     * @param News|null $news
     * @return Response
     */
    public function teaserClicksCounting(Teaser $teaser, ?News $news)
    {
        $source = $this->entityManager->getRepository(Sources::class)->find($this->visitorInformation->getSource());
        $uuid = Uuid::fromString($this->visitorInformation->getUserUuid());

        $teaserClicksCounting = new TeasersClick();
        $teaserClicksCounting->setId(Uuid::uuid4())
            ->setBuyer($this->entityManager->getRepository(User::class)->find($this->visitorInformation->getMediaBuyer()))
            ->setSource($source)
            ->setTeaser($teaser)
            ->setNews($news)
            ->setTrafficType('desktop')
            ->setPageType(str_replace(array("'", "\""), "", $this->request->get('pageType')))
            ->setUserIp($this->request->getClientIp())
            ->setUuid($uuid)
            ->setCountryCode($this->ip2location->getUserCountryCode())
            ->setDesign($this->entityManager->getRepository(Design::class)->findOneBy([
                'slug' => $this->visitorInformation->getDesign()
            ]))
            ->setAlgorithm(
                $this->entityManager->getRepository(Algorithm::class)->find(
                    $this->visitorInformation->getAlgorithm()
                )
            );

        $this->entityManager->persist($teaserClicksCounting);
        $this->entityManager->flush();

        $visit = $this->entityManager->getRepository(Visits::class)->getVisitByUuid($uuid);

        return $this->redirect($this->changeQuery(
            $this->getTeaserLink($teaser->getTeasersSubGroup()),
            $visit,
            $teaserClicksCounting)
        );
    }

    private function getTeaserLink(TeasersSubGroup $subGroup)
    {
        $country = $this->entityManager->getRepository(Country::class)->getCountryByIsoCode($this->ip2location->getUserCountryCode());
        $country = $country ? $country : $this->entityManager->getRepository(Country::class)->getCountryByIsoCode('UA');
        $subGroupSettings = $this->entityManager->getRepository(TeasersSubGroupSettings::class)->getCountrySubGroupSettings($subGroup, $country);
        $subGroupSettings = $subGroupSettings ? $subGroupSettings : $this->entityManager->getRepository(TeasersSubGroupSettings::class)->getDefaultSubGroupSettings($subGroup);

        return $subGroupSettings->getLink();
    }

    /**
     * @param News $news
     * @param int $page
     * @return Teaser[]|Collection
     */
    protected function getNewsTeasers(News $news, $page = 1)
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
        foreach($algorithm->getTeaserForNews($news, $page) as $key => $teaser){
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

    protected function setPreparedTeasers(News $news, $page)
    {
        $this->teasers = $this->getNewsTeasers($news, $page);

        if($this->teasers){
            $this->promoBlockTeasers->setPageType($this->pageType)
                ->setNews($news->getId());
            $this->promoBlockTeasers->saveStatistic($this->teasers);
        }

        return $this;
    }

    /**
     * @param $link
     * @param Visits|null $visit
     * @param TeasersClick $teaserClick
     * @return string
     */
    private function changeQuery($link, ?Visits $visit, TeasersClick $teaserClick)
    {
        $parts = parse_url($link);
        $clickId = $teaserClick->getId()->toString();
        if($visit){
            if(!isset($parts['query'])){
                return $link . (isset($parts['path']) ? '' : '/') . "?data1=$clickId";
            }

            parse_str($parts['query'], $query);
            foreach($query as $key => &$item) {
                if($item == '{utm_term}' OR $item == '(utm_term})' OR $item == '[utm_term]'){
                    if($visit->getUtmTerm()){
                        $item = $visit->getUtmTerm();
                        continue;
                    }
                    unset($query[$key]);
                }
                if($item == '{utm_content}' OR $item == '(utm_content)' OR $item == '[utm_content]'){
                    if($visit->getUtmContent()){
                        $item = $visit->getUtmContent();
                        continue;
                    }
                    unset($query[$key]);
                }
                if($item == '{utm_source}' OR $item == '(utm_source)' OR $item == '[utm_source]'){
                    if($visit->getSource()){
                        $item = $visit->getSource()->getId();
                        continue;
                    }
                    unset($query[$key]);
                }
                if($item == '{utm_campaign}' OR $item == '(utm_campaign)' OR $item == '[utm_campaign]'){
                    if($visit->getUtmCampaign()){
                        $item = $visit->getUtmCampaign();
                        continue;
                    }
                    unset($query[$key]);
                }
                if(false !== stripos($item, '{subid1}') OR false !== stripos($item, '(subid1)') OR false !== stripos($item, '[subid1]')){
                    if($visit->getSubid1()){
                        $item = str_replace("{subid1}", $visit->getSubid1(), $item);
                        $item = str_replace("(subid1)", $visit->getSubid1(), $item);
                        $item = str_replace("[subid1]", $visit->getSubid1(), $item);
                    }
                }
                if(false !== stripos($item, '{subid2}') OR false !== stripos($item, '(subid2)') OR false !== stripos($item, '[subid2]')){
                    if($visit->getSubid2()){
                        $item = str_replace("{subid2}", $visit->getSubid2(), $item);
                        $item = str_replace("(subid2)", $visit->getSubid2(), $item);
                        $item = str_replace("[subid2]", $visit->getSubid2(), $item);
                    }
                }
                if(false !== stripos($item, '{subid3}') OR false !== stripos($item, '(subid3)') OR false !== stripos($item, '[subid3]')){
                    if($visit->getSubid3()){
                        $item = str_replace("{subid3}", $visit->getSubid3(), $item);
                        $item = str_replace("(subid3)", $visit->getSubid3(), $item);
                        $item = str_replace("[subid3]", $visit->getSubid3(), $item);
                    }
                }
                if(false !== stripos($item, '{subid4}') OR false !== stripos($item, '(subid4)') OR false !== stripos($item, '[subid4]')){
                    if($visit->getSubid4()){
                        $item = str_replace("{subid4}", $visit->getSubid4(), $item);
                        $item = str_replace("(subid4)", $visit->getSubid4(), $item);
                        $item = str_replace("[subid4]", $visit->getSubid4(), $item);
                    }
                }
                if(false !== stripos($item, '{subid5}') OR false !== stripos($item, '(subid5)') OR false !== stripos($item, '[subid5]')){
                    if($visit->getSubid5()){
                        $item = str_replace("{subid5}", $visit->getSubid5(), $item);
                        $item = str_replace("(subid5)", $visit->getSubid5(), $item);
                        $item = str_replace("[subid5]", $visit->getSubid5(), $item);
                    }
                }
                if($item == '{clickid}' OR $item == '(clickid)' OR $item == '[clickid]'){
                    $item = $clickId;
                    continue;
                }

                if($item == '{newsid}' OR $item == '(newsid)' OR $item == '[newsid]'){
                    if(!$teaserClick->getNews()){
                        unset($query[$key]);
                        continue;
                    }
                    $item = $teaserClick->getNews()->getId();
                    continue;
                }
                if($item == '{teaserid}' OR $item == '(teaserid)' OR $item == '[teaserid]'){
                    $item = $teaserClick->getTeaser()->getId();
                    continue;
                }
            }
            $parts['query'] = $query;
            $link = $this->build_url($parts);
        }

        return $link;
    }

    /**
     * @param array $elements
     * @return string
     */
    private function build_url(array $elements) {
        $e = $elements;

        return
            (isset($e['host']) ? (
                (isset($e['scheme']) ? "$e[scheme]://" : '//') .
                (isset($e['user']) ? $e['user'] . (isset($e['pass']) ? ":$e[pass]" : '') . '@' : '') .
                $e['host'] .
                (isset($e['port']) ? ":$e[port]" : '')
            ) : '') .
            (isset($e['path']) ? $e['path'] : '/') .
            (isset($e['query']) ? '?' . (is_array($e['query']) ? http_build_query($e['query'], '', '&') : $e['query']) : '') .
            (isset($e['fragment']) ? "#$e[fragment]" : '')
            ;
    }
}