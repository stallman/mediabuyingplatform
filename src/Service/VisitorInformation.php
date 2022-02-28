<?php

namespace App\Service;

use App\Entity\Algorithm;
use App\Entity\Design;
use App\Entity\News;
use App\Entity\Sources;
use App\Entity\Teaser;
use App\Entity\User;
use App\Entity\UserSettings;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Yaml\Yaml;

class VisitorInformation
{

    /**
     * Interactions - Взаимодействия
     * cчётчики посещении алгоритма "Экраны"
     */
    public const INTERACTION_NAME = 'algorithm_screen_interaction';
    public const INTERACTION_NAME_TOP = 'algorithm_screen_interaction_top';
    public const INTERACTION_NAME_CATEGORY = 'algorithm_screen_interaction_category';
    public const INTERACTION_NAME_NEWS = 'algorithm_screen_interaction_news';
    public const AUTORELOAD_NAME = 'autoreload';

    private Request $request;
    private EntityManagerInterface $entityManager;
    private $utmSource;
    private SessionInterface $session;

    public function __construct(Request $request, EntityManagerInterface $entityManager, SessionInterface $session)
    {
        $this->request = $request;
        $this->entityManager = $entityManager;
        $this->session = $session;
    }

    public function getMediaBuyer(): string
    {
        return $this->request->cookies->get('buyer') ? $this->request->cookies->get('buyer') : $this->getCookieFromHeaders('buyer');
    }

    /**
     * @return int|string|null
     */
    public function getSource()
    {
        $source = $this->getCookieFromHeaders('source');
        if ($source === 0 && null !== $this->request->cookies->get('source')) {
            $source = $this->request->cookies->get('source');
        }

        return $source;
    }

    public function getDesign(): ?string
    {
        return $this->request->cookies->get('design') ? $this->request->cookies->get('design') : $this->getCookieFromHeaders('design');
    }

    public function getAlgorithm(): ?string
    {
        return $this->request->cookies->get('algorithm') ? $this->request->cookies->get('algorithm') : $this->getCookieFromHeaders('algorithm');
    }

    public function getUserUuid(): string
    {
        return $this->request->cookies->get('unique_index') ? $this->request->cookies->get('unique_index') : $this->getCookieFromHeaders('unique_index');
    }

    public function getCountryCode(): string
    {
        return $this->request->cookies->get('country_code') ? $this->request->cookies->get('country_code') : $this->getCookieFromHeaders('country_code');
    }

    /**
     * @param $name
     * @return string|int|null
     */
    public function getCookieFromHeaders($name)
    {
        $cookies = [];
        $headers = headers_list();
        foreach($headers as $header) {
            if (strpos($header, 'Set-Cookie: ') === 0) {
                $value = str_replace('&', urlencode('&'), substr($header, 12));
                parse_str(current(explode(';', $value)), $pair);
                $cookies = array_merge_recursive($cookies, $pair);
            }
        }

        return isset($cookies[$name]) ? $cookies[$name] : 0;
    }


    private function getSessionExpireTime()
    {
        $basicSettings = $this->getBasicSetting();

        return time() + $basicSettings['parameters']['session_expire_time'];
    }

    private function getBasicSetting()
    {
        $basicSettingsFile = $this->request->server->get('DOCUMENT_ROOT') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config/basic-settings.yaml';

        return Yaml::parseFile($basicSettingsFile);
    }

    private function getCookieMaxExpireTime()
    {
        return time() + (10 * 365 * 24 * 60 * 60); //срок жизни куки 10 лет
    }

    public function setUserIdCookie()
    {
        $uuid = Uuid::uuid4();
        setcookie("unique_index", $uuid, $this->getCookieMaxExpireTime(), "/");

        return $uuid;
    }

    public function setVisitCookiesFromSource()
    {
        $source = null;

        if(isset($this->utmSource) && !empty($this->utmSource)){
            $source = $this->entityManager->getRepository(Sources::class)->find($this->utmSource);
            if($source){
                setcookie("source", $source->getId(), $this->getCookieMaxExpireTime(), "/");
            }
        }
        $this->setMediaBuyerIdCookie($source);
    }

    private function setMediaBuyerIdCookie(?Sources $source)
    {
        if($source){
            setcookie("buyer", $source->getUser()->getId(), $this->getCookieMaxExpireTime(), "/");
        }

        if(!$source && !$this->getMediaBuyer()){
            $basicSettings = $this->getBasicSetting();
            $defaultMediaBuyer = $this->entityManager->getRepository(User::class)->findOneBy([
                'email' => $basicSettings['parameters']['default_mediabuyer']
            ]);
            setcookie("buyer", $defaultMediaBuyer->getId(), $this->getCookieMaxExpireTime(), "/");
        }
    }

    public function setDesignCookie(User $mediaBuyer)
    {
        if(isset($_ENV['THEME']) && !empty($_ENV['THEME'])){
            $design = $this->entityManager->getRepository(Design::class)->findOneBy(['slug' => $_ENV['THEME']]);
        } else {
            $designs = $this->entityManager->getRepository(Design::class)->getDesignForBuyer($mediaBuyer);
            if(!$designs) $designs = $this->entityManager->getRepository(Design::class)->findBy(['isActive' => true]);
            $design = $designs[array_rand($designs)];
        }
        setcookie("design", "theme_{$design->getId()}", 0, "/");
    }

    public function setAlgorithmCookie(User $mediaBuyer)
    {
        setcookie("algorithm", $this->getMediaBuyerAlgorithm($mediaBuyer), 0, "/");
    }

    public function setCountryCodeCookie(string $code)
    {
        setcookie("country_code", $code, 0, "/");
    }

    public function setUtmSource($utmSource)
    {
        $this->utmSource = $utmSource;

        return $this;
    }

    public function updateSessionCookie()
    {
        if(!$this->request->cookies->get('REMEMBERME') && $this->request->cookies->get('PHPSESSID')){
            $cookieValue = $this->request->cookies->get('PHPSESSID');
            setcookie("PHPSESSID", $cookieValue, $this->getSessionExpireTime(), "/");
        }
    }

    private function getMediaBuyerAlgorithm(User $mediaBuyer)
    {
        if(isset($_ENV['ALGORITHM']) && !empty($_ENV['ALGORITHM'])) {
            $algorithms = $this->entityManager->getRepository(Algorithm::class)->findBy(['slug' => $_ENV['ALGORITHM']]);
        } else {
            if($this->isNotDefaultAlgorithm($mediaBuyer)){
                $algorithms = $this->entityManager->getRepository(Algorithm::class)->getAlgorithmForBuyer($mediaBuyer);
                if(empty($algorithms)){
                    $algorithms = $this->entityManager->getRepository(Algorithm::class)->getDefaultAlgorithm($mediaBuyer);
                }
            } else {
                $algorithms = $this->entityManager->getRepository(Algorithm::class)->getDefaultAlgorithm($mediaBuyer);
            }
        }
        return $algorithms[array_rand($algorithms)]->getId();
    }

    private function isNotDefaultAlgorithm(User $mediaBuyer)
    {
        $impressionsNews = $this->entityManager->getRepository(UserSettings::class)->getUserSetting($mediaBuyer->getId(), 'ecrm_news_view_count');
        $impressionsTeasers = $this->entityManager->getRepository(UserSettings::class)->getUserSetting($mediaBuyer->getId(), 'ecrm_teasers_view_count');

        $countNews = $this->entityManager->getRepository(News::class)->getMediaBuyerNewsCount($mediaBuyer);
        $countNewsOwnECPM = $this->entityManager->getRepository(News::class)->getMediaBuyerNewsOwnECPMCount($mediaBuyer, $impressionsNews);

        $countTeasers = $this->entityManager->getRepository(Teaser::class)->getCountTeasers($mediaBuyer);
        $countTeasersOwnECPM = $this->entityManager->getRepository(Teaser::class)->getCountTeasersOwnECPM($mediaBuyer, $impressionsTeasers);

        return ($this->getCountPercent($countNews, $countNewsOwnECPM) < 50 || $this->getCountPercent($countTeasers, $countTeasersOwnECPM) < 50) ? false : true;
    }

    private function getCountPercent(int $count, int $countOwnECPM): int
    {
        return  $countOwnECPM / ($count / 100);
    }

    public function getDefaultInteractions(): array
    {
        return [
            self::INTERACTION_NAME_TOP => 0,
            self::INTERACTION_NAME_NEWS => 0,
            self::INTERACTION_NAME_CATEGORY => [],
        ];
    }

    /**
     * Назначаются как идентификаторы категорий + на стр. топа тизеров только для сортировки но взаимодействия новостей общие
     * @param string|null $key
     */
    public function getInteraction(?string $slug = null, ?string $key = null): int
    {
        $interaction = 0;

        $interactions = $this->session->get(self::INTERACTION_NAME, $this->getDefaultInteractions());

        if (null !== $slug) {
            if ($slug === self::INTERACTION_NAME_CATEGORY) {
                if (!isset($interactions[$slug][$key])) {
                    $interactions[$slug][$key] = 0;
                }
                $interaction = $interactions[$slug][$key];
            } else {
                $interaction = $interactions[$slug];
            }
        }

        return $interaction;
    }

    public function getInteractions(): array
    {
        return $this->session->get(self::INTERACTION_NAME, $this->getDefaultInteractions());
    }

    public function rewindInteraction(?string $slug = null, ?string $key = null): self
    {

        if (null === $slug) {
            $this->session->set(self::INTERACTION_NAME, $this->getDefaultInteractions());
        } else {
            $interactions = $this->session->get(self::INTERACTION_NAME, $this->getDefaultInteractions());

            if (null === $key) {
                $interactions[$slug] = 0;
            } else {
                $interactions[$slug][$key] = 0;
            }

            $this->session->set(self::INTERACTION_NAME, $interactions);
        }

        return $this;
    }

    public function incrementInteraction(?string $slug = null, ?string $key = null): self
    {
        if (null !== $slug) {
            $interactions = $this->session->get(self::INTERACTION_NAME, $this->getDefaultInteractions());

            if (null === $key) {
                $interactions[$slug] += 1;

                if ($interactions[$slug] > 6) {
                    $interactions[$slug] = 1;
                }
            } else {
                if (!isset($interactions[$slug][$key])) {
                    $interactions[$slug][$key] = 0;
                }

                $interactions[$slug][$key] += 1;

                if ($interactions[$slug][$key] > 6) {
                    $interactions[$slug][$key] = 1;
                }
            }

            $this->session->set(self::INTERACTION_NAME, $interactions);
        }

        return $this;
    }

    public function isAutoreload(): bool
    {
        return $this->session->get(self::AUTORELOAD_NAME, false);
    }

    public function setAutoreload(bool $autoreloaded = true): self
    {
        $this->session->set(self::AUTORELOAD_NAME, $autoreloaded);

        return $this;
    }
}