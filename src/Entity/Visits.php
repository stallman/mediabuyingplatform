<?php

namespace App\Entity;

use App\Repository\VisitsRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass=VisitsRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="visits", indexes={
 *     @ORM\Index(name="idx_created_at", columns={"created_at"}),
 * })
 */
class Visits
{
    /**
     * @var UuidInterface
     * @ORM\Id
     * @ORM\Column(name="uuid", type="uuid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    private $countryCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity=Sources::class, inversedBy="visits")
     */
    private $source;

    /**
     * @ORM\ManyToOne(targetEntity=News::class, inversedBy="visits")
     */
    private $news;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="visits")
     */
    private $mediabuyer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $utmMedium;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $utmTerm;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $utmContent;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $utmCampaign;

    /**
     * @ORM\Column(type="string", length=39)
     */
    private $ip;

    /**
     * @ORM\Column(type="string", length=255, columnDefinition="ENUM('desktop', 'tablet', 'mobile')")
     */
    private $trafficType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $os;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $osWithVersion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $browser;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $browserWithVersion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mobileBrand;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mobileModel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mobileOperator;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $screenSize;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subid1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subid2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subid3;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subid4;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subid5;

    /**
     * @ORM\Column(type="string", length=355, nullable=true)
     */
    private $userAgent;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255, columnDefinition="CHAR(2)")
     */
    private $timesOfDay;

    /**
     * @ORM\Column(type="string", length=255, columnDefinition="ENUM('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday')")
     */
    private $dayOfWeek;

    /**
     * @ORM\ManyToOne(targetEntity=DomainParking::class, inversedBy="visits")
     */
    private $domain;

    /**
     * @ORM\ManyToOne(targetEntity=Design::class, inversedBy="visits")
     */
    private $design;

    /**
     * @ORM\ManyToOne(targetEntity=Algorithm::class, inversedBy="visits")
     */
    private $algorithm;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $createdAt;

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function setUuid(UuidInterface $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getSource(): ?Sources
    {
        return $this->source;
    }

    public function setSource(?Sources $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getNews(): ?News
    {
        return $this->news;
    }

    public function setNews(?News $news): self
    {
        $this->news = $news;

        return $this;
    }

    public function getMediabuyer(): ?User
    {
        return $this->mediabuyer;
    }

    public function setMediabuyer(?User $mediabuyer): self
    {
        $this->mediabuyer = $mediabuyer;

        return $this;
    }

    public function getUtmMedium(): ?string
    {
        return $this->utmMedium;
    }

    public function setUtmMedium(?string $utmMedium): self
    {
        $this->utmMedium = $utmMedium;

        return $this;
    }

    public function getUtmTerm(): ?string
    {
        return $this->utmTerm;
    }

    public function setUtmTerm(?string $utmTerm): self
    {
        $this->utmTerm = $utmTerm;

        return $this;
    }

    public function getUtmContent(): ?string
    {
        return $this->utmContent;
    }

    public function setUtmContent(?string $utmContent): self
    {
        $this->utmContent = $utmContent;

        return $this;
    }

    public function getUtmCampaign(): ?string
    {
        return $this->utmCampaign;
    }

    public function setUtmCampaign(?string $utmCampaign): self
    {
        $this->utmCampaign = $utmCampaign;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getTrafficType(): ?string
    {
        return $this->trafficType;
    }

    public function setTrafficType(string $trafficType): self
    {
        $this->trafficType = $trafficType;

        return $this;
    }

    public function getOs(): ?string
    {
        return $this->os;
    }

    public function setOs(?string $os): self
    {
        $this->os = $os;

        return $this;
    }

    public function getOsWithVersion(): ?string
    {
        return $this->osWithVersion;
    }

    public function setOsWithVersion(?string $osWithVersion): self
    {
        $this->osWithVersion = $osWithVersion;

        return $this;
    }

    public function getBrowser(): ?string
    {
        return $this->browser;
    }

    public function setBrowser(?string $browser): self
    {
        $this->browser = $browser;

        return $this;
    }

    public function getBrowserWithVersion(): ?string
    {
        return $this->browserWithVersion;
    }

    public function setBrowserWithVersion(?string $browserWithVersion): self
    {
        $this->browserWithVersion = $browserWithVersion;

        return $this;
    }

    public function getMobileBrand(): ?string
    {
        return $this->mobileBrand;
    }

    public function setMobileBrand(?string $mobileBrand): self
    {
        $this->mobileBrand = $mobileBrand;

        return $this;
    }

    public function getMobileModel(): ?string
    {
        return $this->mobileModel;
    }

    public function setMobileModel(?string $mobileModel): self
    {
        $this->mobileModel = $mobileModel;

        return $this;
    }

    public function getMobileOperator(): ?string
    {
        return $this->mobileOperator;
    }

    public function setMobileOperator(?string $mobileOperator): self
    {
        $this->mobileOperator = $mobileOperator;

        return $this;
    }

    public function getScreenSize(): ?string
    {
        return $this->screenSize;
    }

    public function setScreenSize(?string $screenSize): self
    {
        $this->screenSize = $screenSize;

        return $this;
    }

    public function getSubid1(): ?string
    {
        return $this->subid1;
    }

    public function setSubid1(?string $subid1): self
    {
        $this->subid1 = $subid1;

        return $this;
    }

    public function getSubid2(): ?string
    {
        return $this->subid2;
    }

    public function setSubid2(?string $subid2): self
    {
        $this->subid2 = $subid2;

        return $this;
    }

    public function getSubid3(): ?string
    {
        return $this->subid3;
    }

    public function setSubid3(?string $subid3): self
    {
        $this->subid3 = $subid3;

        return $this;
    }

    public function getSubid4(): ?string
    {
        return $this->subid4;
    }

    public function setSubid4(?string $subid4): self
    {
        $this->subid4 = $subid4;

        return $this;
    }

    public function getSubid5(): ?string
    {
        return $this->subid5;
    }

    public function setSubid5(?string $subid5): self
    {
        $this->subid5 = $subid5;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getTimesOfDay(): ?string
    {
        return $this->timesOfDay;
    }

    public function setTimesOfDay(string $timesOfDay): self
    {
        $this->timesOfDay = $timesOfDay;

        return $this;
    }

    public function getDayOfWeek(): ?string
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(string $dayOfWeek): self
    {
        $this->dayOfWeek = $dayOfWeek;

        return $this;
    }

    public function getDomain(): ?DomainParking
    {
        return $this->domain;
    }

    public function setDomain(?DomainParking $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function getDesign(): ?Design
    {
        return $this->design;
    }

    public function setDesign(?Design $design): self
    {
        $this->design = $design;

        return $this;
    }

    public function getAlgorithm(): ?Algorithm
    {
        return $this->algorithm;
    }

    public function setAlgorithm(?Algorithm $algorithm): self
    {
        $this->algorithm = $algorithm;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist()
     * @return $this
     */
    public function setCreatedAt($createdAt = null): self
    {
        $this->createdAt = (null !== $createdAt && $createdAt instanceof \DateTimeInterface)
            ? $createdAt
            : new \DateTime() ;

        return $this;
    }
}
