<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="algorithms")
 * @ORM\Entity(repositoryClass="App\Repository\AlgorithmRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Algorithm implements EntityInterface
{
    /**
     * @var int|null
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $slug;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" : 0})
     */
    private $isDefault;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" : 1})
     */
    private $isActive;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeasersClick", mappedBy="algorithm")
     */
    private $teasersClick;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Visits", mappedBy="algorithm")
     */
    private $visits;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StatisticNews", mappedBy="algorithm")
     */
    private $statisticNews;

    /**
     * @ORM\OneToMany(targetEntity=AlgorithmsAggregatedStatistics::class, mappedBy="algorithm")
     */
    private $algorithmsAggregatedStatistics;

    /**
     * @ORM\OneToMany(targetEntity=MediabuyerAlgorithms::class, mappedBy="algorithm")
     */
    private $mediabuyerAlgorithms;

    /**
     * @ORM\OneToMany(targetEntity=NewsClick::class, mappedBy="algorithm")
     */
    private $newsClick;

    /**
     * @ORM\OneToMany(targetEntity=NewsClickShortToFull::class, mappedBy="algorithm")
     */
    private $newsClickShortToFull;

    /**
     * @ORM\OneToMany(targetEntity=Conversions::class, mappedBy="algorithm")
     */
    private $conversions;

    public function __construct()
    {
        $this->mediabuyerAlgorithms = new ArrayCollection();
        $this->algorithmsAggregatedStatistics = new ArrayCollection();
        $this->showNews = new ArrayCollection();
    }

    /**
     * @ORM\OneToMany(targetEntity=StatisticPromoBlockNews::class, mappedBy="algorithm")
     */
    private $statisticPromoBlockNews;

    /**
     * @ORM\OneToMany(targetEntity=StatisticPromoBlockTeasers::class, mappedBy="algorithm")
     */
    private $statisticPromoBlockTeasers;

    /**
     * @ORM\OneToMany(targetEntity=ShowNews::class, mappedBy="algorithm")
     */
    private $showNews;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

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

    /**
     * @return Collection|AlgorithmsAggregatedStatistics[]
     */
    public function getAlgorithmsAggregatedStatistics(): Collection
    {
        return $this->algorithmsAggregatedStatistics;
    }

    /**
     * @return Collection|MediabuyerAlgorithms[]
     */
    public function getMediabuyerAlgorithms(): Collection
    {
        return $this->mediabuyerAlgorithms;
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
            $showNews->setAlgorithm($this);
        }

        return $this;
    }

    public function removeShowNews(ShowNews $showNews): self
    {
        if ($this->showNews->contains($showNews)) {
            $this->showNews->removeElement($showNews);
            // set the owning side to null (unless already changed)
            if ($showNews->getAlgorithm() === $this) {
                $showNews->setAlgorithm(null);
            }
        }

        return $this;
    }


}
