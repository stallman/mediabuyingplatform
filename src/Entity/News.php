<?php

namespace App\Entity;

use App\Entity\Traits\UploadFileTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PhpParser\Node\Expr\Cast\Object_;
use PhpParser\Node\Scalar\String_;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @ORM\Table(name="news", indexes={
 *     @ORM\Index(name="is_active_is_deleted", columns={"is_active", "is_deleted"}),
 *     @ORM\Index(name="user_active", columns={"user_id", "is_active", "is_deleted"}),
 * })
 * @ORM\Entity(repositoryClass="App\Repository\NewsRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class News implements EntityInterface
{
    public const STATUS_ACTIVE = 1;
    public const STATUS_DISABLED = 0;

    /**
     * @var int|null
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="news", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255, columnDefinition="ENUM('own', 'common')")
     */
    private $type;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $shortDescription;

    /**
     * @ORM\Column(type="text")
     */
    private $fullDescription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sourceLink;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\NewsCategory", inversedBy="news")
     * @ORM\JoinTable(name="news_categories_relations")
     */
    private $categories;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MediabuyerNews", cascade={"persist", "remove"}, mappedBy="news")
     * @ORM\JoinTable(name="mediabuyer_news")
     */
    private $mediabuyerNews;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MediabuyerNewsRotation", cascade={"persist", "remove"}, mappedBy="news")
     * @ORM\JoinTable(name="mediabuyer_news_rotation")
     */
    private $mediabuyerNewsRotation;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Country", inversedBy="news")
     * @ORM\JoinTable(name="news_countries_relations")
     */
    private $countries;

    /**
     * @ORM\OneToMany(targetEntity=StatisticNews::class, mappedBy="news", orphanRemoval=true)
     */
    private $statistic;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeasersClick", mappedBy="news")
     */
    private $teasersClick;

    /**
     * @ORM\OneToMany(targetEntity=NewsClick::class, mappedBy="news")
     */
    private $newsClick;

    /**
     * @ORM\OneToMany(targetEntity=NewsClickShortToFull::class, mappedBy="news")
     */
    private $newsClickShortToFull;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $is_deleted = false;

     /**
     * @ORM\OneToMany(targetEntity=Conversions::class, mappedBy="news")
     */
    private $conversions;

    /**
     * @ORM\OneToMany(targetEntity=Visits::class, mappedBy="news")
     */
    private $visits;

    /**
     * @ORM\OneToMany(targetEntity=ShowNews::class, mappedBy="news")
     */
    private $showNews;

    /**
     * @ORM\OneToMany(targetEntity=TopNews::class, mappedBy="news")
     */
    private $topNews;

    /**
     * @ORM\OneToMany(targetEntity=StatisticPromoBlockNews::class, mappedBy="news")
     */
    private $statisticPromoBlock;

    public function __clone()
    {
        $this->id = null;
    }

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->mediabuyerNews = new ArrayCollection();
        $this->mediabuyerNewsRotation = new ArrayCollection();
        $this->countries = new ArrayCollection();
        $this->statistic = new ArrayCollection();
        $this->showNews = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getTitle() ? $this->getTitle() : get_class($this);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist()
     * @return $this
     */
    public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTime();

        return $this;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     * @return $this
     */
    public function setUpdatedAt($updateAt): self
    {
        $this->updatedAt = new \DateTime();
        return $this;
    }



    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getFullDescription(): ?string
    {
        return $this->fullDescription;
    }

    public function setFullDescription(string $fullDescription): self
    {
        $this->fullDescription = $fullDescription;

        return $this;
    }

    public function getSourceLink(): ?string
    {
        return $this->sourceLink;
    }

    public function setSourceLink(string $sourceLink): self
    {
        $this->sourceLink = $sourceLink;

        return $this;
    }

    /**
     * @return Collection|NewsCategory[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(NewsCategory $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(NewsCategory $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getMediabuyerNews(): ?Collection
    {
        return $this->mediabuyerNews;
    }

    public function setMediabuyerNews($mediabuyerNews): self
    {
        $this->mediabuyerNews = $mediabuyerNews;

        return $this;
    }

    public function getMediabuyerNewsRotation(): ?Collection
    {
        return $this->mediabuyerNewsRotation;
    }

    public function setMediabuyerNewsRotation($mediabuyerNewsRotation): self
    {
        $this->mediabuyerNewsRotation = $mediabuyerNewsRotation;

        return $this;
    }

    /**
     * @return Collection|Country[]
     */
    public function getCountries(): Collection
    {
        return $this->countries;
    }

    public function addCountry(Country $country): self
    {
        if (!$this->countries->contains($country)) {
            $this->countries[] = $country;
        }

        return $this;
    }

    public function removeCountry(Country $country): self
    {
        if ($this->countries->contains($country)) {
            $this->countries->removeElement($country);
        }

        return $this;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('title', new Length([
            'max' => 255,
            'maxMessage' => 'Название новости не может быть длиннее {{ limit }} символов',
            'allowEmptyString' => false,
        ]))
            ->addPropertyConstraint('shortDescription', new Length([
                'max' => 255,
                'maxMessage' => 'Короткая новость не может быть длиннее {{ limit }} символов',
                'allowEmptyString' => false,
            ]))
            ->addPropertyConstraint('sourceLink', new Length([
                'max' => 255,
                'maxMessage' => 'Ссылка на источник не может быть длиннее {{ limit }} символов',
                'allowEmptyString' => false,
            ]));
    }

    /**
     * @return Collection|StatisticNews[]
     */
    public function getStatistic(): Collection
    {
        return $this->statistic;
    }

    public function addStatistic(StatisticNews $statistic): self
    {
        if (!$this->statistic->contains($statistic)) {
            $this->statistic[] = $statistic;
            $statistic->setNewsId($this);
        }

        return $this;
    }

    public function removeStatistic(StatisticNews $statistic): self
    {
        if ($this->statistic->contains($statistic)) {
            $this->statistic->removeElement($statistic);
            // set the owning side to null (unless already changed)
            if ($statistic->getNewsId() === $this) {
                $statistic->setNewsId(null);
            }
        }

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(?bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    /**
     * @return Collection|ShowNews[]
     */
    public function getShowNews(): Collection
    {
        return $this->showNews;
    }

    public function addShowNews(ShowNews $showNews): self
    {
        if (!$this->showNews->contains($showNews)) {
            $this->showNews[] = $showNews;
            $showNews->setNews($this);
        }

        return $this;
    }

    public function removeShowNews(ShowNews $showNews): self
    {
        if ($this->showNews->contains($showNews)) {
            $this->showNews->removeElement($showNews);
            // set the owning side to null (unless already changed)
            if ($showNews->getNews() === $this) {
                $showNews->setNews(null);
            }
        }

        return $this;
    }
}
