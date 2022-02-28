<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StatisticPromoBlockTeasersRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class StatisticPromoBlockTeasers
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Teaser::class, inversedBy="statisticPromoBlockTeasers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $teaser;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $mediabuyer;

    /**
     * @ORM\ManyToOne(targetEntity=Sources::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $source;

    /**
     * @ORM\ManyToOne(targetEntity=Algorithm::class, inversedBy="statisticPromoBlockTeasers")
     */
    private $algorithm;

    /**
     * @ORM\ManyToOne(targetEntity=Design::class, inversedBy="statisticPromoBlockTeasers")
     */
    private $design;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    private $countryCode;

    /**
     * @ORM\Column(type="string", length=255, columnDefinition="ENUM('desktop', 'tablet', 'mobile')")
     */
    private $trafficType;

    /**
     * @ORM\Column(type="string", length=255, columnDefinition="ENUM('top', 'short', 'full')")
     */
    private $pageType;

    /**
     * @ORM\ManyToOne(targetEntity=News::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $news;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeaser(): ?Teaser
    {
        return $this->teaser;
    }

    public function setTeaser(?Teaser $teaser): self
    {
        $this->teaser = $teaser;

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

    public function getSource(): ?Sources
    {
        return $this->source;
    }

    public function setSource(?Sources $source): self
    {
        $this->source = $source;

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

    public function getDesign(): ?Design
    {
        return $this->design;
    }

    public function setDesign(?Design $design): self
    {
        $this->design = $design;

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

    public function getTrafficType(): ?string
    {
        return $this->trafficType;
    }

    public function setTrafficType(string $trafficType): self
    {
        $this->trafficType = $trafficType;

        return $this;
    }

    public function getPageType(): ?string
    {
        return $this->pageType;
    }

    public function setPageType(string $pageType): self
    {
        $this->pageType = $pageType;

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

    public function getCreatedAt(): ?\DateTimeInterface
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
