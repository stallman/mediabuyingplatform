<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="designs")
 * @ORM\Entity(repositoryClass="App\Repository\DesignRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Design implements EntityInterface
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
     * @ORM\Column(type="boolean", nullable=false, options={"default" : 1})
     */
    private $isActive;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeasersClick", mappedBy="design")
     */
    private $teasersClick;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Visits", mappedBy="design")
     */
    private $visits;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\StatisticNews", mappedBy="design")
     */
    private $statisticNews;

    /**
     * @ORM\OneToMany(targetEntity=DesignsAggregatedStatistics::class, mappedBy="design")
     */
    private $designsAggregatedStatistics;

    /**
     * @ORM\OneToMany(targetEntity=Conversions::class, mappedBy="design")
     */
    private $conversions;

    public function __construct()
    {
        $this->designsAggregatedStatistics = new ArrayCollection();
        $this->showNews = new ArrayCollection();
    }

    /**
     * @ORM\OneToMany(targetEntity=StatisticPromoBlockNews::class, mappedBy="design")
     */
    private $statisticPromoBlockNews;

    /**
     * @ORM\OneToMany(targetEntity=StatisticPromoBlockTeasers::class, mappedBy="design")
     */
    private $statisticPromoBlockTeasers;

    /**
     * @ORM\OneToMany(targetEntity=MediabuyerDesigns::class, mappedBy="design")
     */
    private $mediabuyerDesigns;

    /**
     * @ORM\OneToMany(targetEntity=NewsClick::class, mappedBy="design")
     */
    private $newsClick;

    /**
     * @ORM\OneToMany(targetEntity=NewsClickShortToFull::class, mappedBy="design")
     */
    private $newsClickShortToFull;

    /**
     * @ORM\OneToMany(targetEntity=ShowNews::class, mappedBy="design")
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
     * @return Collection|DesignsAggregatedStatistics[]
     */
    public function getDesignsAggregatedStatistics(): Collection
    {
        return $this->designsAggregatedStatistics;
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
            $showNews->setDesign($this);
        }

        return $this;
    }

    public function removeShowNews(ShowNews $showNews): self
    {
        if ($this->showNews->contains($showNews)) {
            $this->showNews->removeElement($showNews);
            // set the owning side to null (unless already changed)
            if ($showNews->getDesign() === $this) {
                $showNews->setDesign(null);
            }
        }

        return $this;
    }

}
