<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\String_;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="news_click")
 * @ORM\Entity(repositoryClass="App\Repository\NewsClickRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class NewsClick
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="newsClick", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $buyer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sources", inversedBy="newsClick", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $source;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\News", inversedBy="newsClick", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $news;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    private $countryCode;

    /**
     * @var array $trafficType
     *
     * @ORM\Column(name="traffic_type", type="string", length=255, columnDefinition="ENUM('desktop', 'tablet', 'mobile')")
     */
    private $trafficType;

    /**
     * @var array $pageType
     *
     * @ORM\Column(name="page_type", type="string", length=255, columnDefinition="ENUM('top', 'category')")
     */
    private $pageType;  

    /**
     * @ORM\Column(type="string", length=39, nullable=false)
     */
    private $userIp;

    /**
     * @ORM\Column(type="string", length=39, nullable=false)
     */
    private $uuid;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=Postback::class, mappedBy="click", orphanRemoval=true)
     */
    private $postbacks;

    /**
     * @ORM\ManyToOne(targetEntity=Design::class, inversedBy="newsClick")
     */
    private $design;

    /**
     * @ORM\ManyToOne(targetEntity=Algorithm::class, inversedBy="newsClick")
     */
    private $algorithm;

    public function __construct()
    {
        $this->postbacks = new ArrayCollection();
    }

    public function getBuyer(): ?User
    {
        return $this->buyer;
    }

    public function setBuyer(User $buyer): self
    {
        $this->buyer = $buyer;

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

    public function setNews(News $news): self
    {
        $this->news = $news;

        return $this;
    }

    public function getTrafficType(): ?string
    {
        return $this->trafficType;
    }

    public function setTrafficType(?string $trafficType): self
    {
        $this->trafficType = $trafficType;

        return $this;
    }

    public function getPageType(): ?string
    {
        return $this->pageType;
    }

    public function setPageType(?string $pageType): self
    {
        $this->pageType = $pageType;

        return $this;
    }

    public function getUserIp(): ?string
    {
        return $this->userIp;
    }

    public function setUserIp(?string $userIp): self
    {
        $this->userIp = $userIp;

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

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): self
    {
        $this->uuid = $uuid;

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

    /**
     * @return Collection|Postback[]
     */
    public function getPostbacks(): Collection
    {
        return $this->postbacks;
    }

    public function addPostback(Postback $postback): self
    {
        if (!$this->postbacks->contains($postback)) {
            $this->postbacks[] = $postback;
            $postback->setClick($this); // todo error Expected parameter of type 'TeasersClick', 'NewsClick' provided
        }

        return $this;
    }

    public function removePostback(Postback $postback): self
    {
        if ($this->postbacks->contains($postback)) {
            $this->postbacks->removeElement($postback);
            // set the owning side to null (unless already changed)
            if ($postback->getClick() === $this) {
                $postback->setClick(null);
            }
        }

        return $this;
    }
}
