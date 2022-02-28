<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="teasers", indexes={
 *     @ORM\Index(name="is_active_is_deleted", columns={"is_active", "is_deleted"}),
 * })
 * @ORM\Entity(repositoryClass="App\Repository\TeasersRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Teaser implements EntityInterface
{
    /**
     * @var int|null
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="teasers", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $text;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" : 0})
     */
    private $isActive;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" : 0})
     */
    private $isTop;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $dropNews = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $dropSources = [];

    /**
     * @var TeasersSubGroup
     *
     * @ORM\ManyToOne(targetEntity=TeasersSubGroup::class, inversedBy="teasers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $teasersSubGroup;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeasersClick", mappedBy="teaser")
     */
    private $teasersClick;

    /**
     * @ORM\OneToOne(targetEntity=StatisticTeasers::class, mappedBy="teaser", orphanRemoval=true)
     */
    private $statistic;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $is_deleted = false;

    /**
     * @ORM\OneToMany(targetEntity=StatisticPromoBlockTeasers::class, mappedBy="teaser")
     */
    private $statisticPromoBlockTeasers;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=TopTeasers::class, mappedBy="teaser")
     */
    private $topTeaser;

    public function __construct()
    {
        $this->statisticPromoBlockTeasers = new ArrayCollection();
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

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

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

    public function getIsTop(): ?bool
    {
        return $this->isTop;
    }

    public function setIsTop(bool $isTop): self
    {
        $this->isTop = $isTop;

        return $this;
    }

    public function getTeasersSubGroup(): ?TeasersSubGroup
    {
        return $this->teasersSubGroup;
    }

    public function setTeasersSubGroup(TeasersSubGroup $teasersSubGroup): self
    {
        $this->teasersSubGroup = $teasersSubGroup;

        return $this;
    }

    public function getDropNews(): ?string
    {
        $this->dropNews = $this->dropNews ? $this->dropNews : [];
        return implode(",", $this->dropNews);
    }

    public function setDropNews(?string $dropNews): self
    {
        $this->dropNews = array_unique(explode(",", $dropNews));

        return $this;
    }

    public function getDropSources(): ?string
    {
        $this->dropSources = $this->dropSources ? $this->dropSources : [];
        return implode(",", $this->dropSources);
    }

    public function setDropSources(?string $dropSources): self
    {
        $this->dropSources = array_unique(explode(",", $dropSources));

        return $this;
    }

    public function getStatistic(): ?StatisticTeasers
    {
        return $this->statistic;
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
     * @return Collection|StatisticPromoBlockTeasers[]
     */
    public function getStatisticPromoBlockTeasers(): Collection
    {
        return $this->statisticPromoBlockTeasers;
    }

    public function addStatisticPromoBlockTeaser(StatisticPromoBlockTeasers $statisticPromoBlockTeaser): self
    {
        if (!$this->statisticPromoBlockTeasers->contains($statisticPromoBlockTeaser)) {
            $this->statisticPromoBlockTeasers[] = $statisticPromoBlockTeaser;
            $statisticPromoBlockTeaser->setTeaser($this);
        }

        return $this;
    }

    public function removeStatisticPromoBlockTeaser(StatisticPromoBlockTeasers $statisticPromoBlockTeaser): self
    {
        if ($this->statisticPromoBlockTeasers->contains($statisticPromoBlockTeaser)) {
            $this->statisticPromoBlockTeasers->removeElement($statisticPromoBlockTeaser);
            // set the owning side to null (unless already changed)
            if ($statisticPromoBlockTeaser->getTeaser() === $this) {
                $statisticPromoBlockTeaser->setTeaser(null);
            }
        }

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PreUpdate()
     * @return $this
     */
    public function setUpdatedAt(): self
    {
        $this->updatedAt = new \DateTime();

        return $this;
    }
}
