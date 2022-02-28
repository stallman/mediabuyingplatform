<?php

namespace App\Entity;

use App\Repository\SourcesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\Range;

/**
 * @ORM\Entity(repositoryClass=SourcesRepository::class)
 */
class Sources implements EntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="sources", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=120, nullable=false)
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CurrencyList", inversedBy="sources", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $currency;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=4, nullable=false)
     * @Range(
     *      min = 0,
     *      max = 999999,
     * )

     */
    private $multiplier;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $utm_campaign;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $utm_term;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $utm_content;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $subid1;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $subid2;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $subid3;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $subid4;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $subid5;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $is_deleted = false;

    /**
     * @ORM\OneToMany(targetEntity=Conversions::class, mappedBy="source")
     */
    private $conversions;


    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeasersClick", mappedBy="source")
     */
    private $teasersClick;

    /**
     * @ORM\OneToMany(targetEntity=Visits::class, mappedBy="source")
     */
    private $visits;

    /**
     * @ORM\OneToMany(targetEntity=StatisticPromoBlockNews::class, mappedBy="source")
     */
    private $statisticPromoBlockNews;

    /**
     * @ORM\OneToMany(targetEntity=Costs::class, mappedBy="source")
     */
    private $costs;

    /**
     * @ORM\OneToMany(targetEntity=NewsClick::class, mappedBy="source")
     */
    private $newsClick;

    /**
     * @ORM\OneToMany(targetEntity=NewsClickShortToFull::class, mappedBy="source")
     */
    private $newsClickShortToFull;

    /**
     * @ORM\OneToMany(targetEntity=ShowNews::class, mappedBy="source")
     */
    private $showNews;

    public function __construct()
    {
        $this->conversions = new ArrayCollection();
        $this->visits = new ArrayCollection();
        $this->statisticPromoBlockNews = new ArrayCollection();
        $this->costs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getCurrency(): ?CurrencyList
    {
        return $this->currency;
    }

    public function setCurrency(CurrencyList $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getMultiplier(): ?float
    {
        return $this->multiplier;
    }

    public function setMultiplier(float $multiplier): self
    {
        $this->multiplier = $multiplier;

        return $this;
    }

    public function getUtmCampaign(): ?string
    {
        return $this->utm_campaign;
    }

    public function setUtmCampaign(?string $utm_campaign): self
    {
        $this->utm_campaign = $utm_campaign;

        return $this;
    }

    public function getUtmTerm(): ?string
    {
        return $this->utm_term;
    }

    public function setUtmTerm(?string $utm_term): self
    {
        $this->utm_term = $utm_term;

        return $this;
    }

    public function getUtmContent(): ?string
    {
        return $this->utm_content;
    }

    public function setUtmContent(?string $utm_content): self
    {
        $this->utm_content = $utm_content;

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

    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(?bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('title', new Length([
            'max' => 50,
            'maxMessage' => 'Название не может быть длиннее {{ limit }} символов',
            'allowEmptyString' => false,
        ]));
        $metadata->addPropertyConstraint('utm_campaign', new Length([
            'max' => 40,
            'maxMessage' => 'Макрос источника для передачи id или наименования рекламной кампании не может быть длиннее {{ limit }} символов',
            'allowEmptyString' => false,
        ]));
        $metadata->addPropertyConstraint('utm_term', new Length([
            'max' => 40,
            'maxMessage' => 'Макрос источника для передачи id сайта {{ limit }} символов',
            'allowEmptyString' => false,
        ]));
        $metadata->addPropertyConstraint('utm_content', new Length([
            'max' => 40,
            'maxMessage' => 'Макрос источника для передачи id рекламного объявления {{ limit }} символов',
            'allowEmptyString' => false,
        ]));
        $metadata->addPropertyConstraint('subid1', new Length([
            'max' => 40,
            'maxMessage' => 'Кастомный макрос источника {{ limit }} символов',
            'allowEmptyString' => false,
        ]));
        $metadata->addPropertyConstraint('subid2', new Length([
            'max' => 40,
            'maxMessage' => 'Кастомный макрос источника {{ limit }} символов',
            'allowEmptyString' => false,
        ]));
        $metadata->addPropertyConstraint('subid3', new Length([
            'max' => 40,
            'maxMessage' => 'Кастомный макрос источника {{ limit }} символов',
            'allowEmptyString' => false,
        ]));
        $metadata->addPropertyConstraint('subid4', new Length([
            'max' => 40,
            'maxMessage' => 'Кастомный макрос источника {{ limit }} символов',
            'allowEmptyString' => false,
        ]));
        $metadata->addPropertyConstraint('subid5', new Length([
            'max' => 40,
            'maxMessage' => 'Кастомный макрос источника {{ limit }} символов',
            'allowEmptyString' => false,
        ]));
    }

    /**
     * @return Collection|Conversions[]
     */
    public function getConversions(): Collection
    {
        return $this->conversions;
    }

    public function addConversion(Conversions $conversion): self
    {
        if (!$this->conversions->contains($conversion)) {
            $this->conversions[] = $conversion;
            $conversion->setSource($this);
        }

        return $this;
    }

    public function removeConversion(Conversions $conversion): self
    {
        if ($this->conversions->contains($conversion)) {
            $this->conversions->removeElement($conversion);
            // set the owning side to null (unless already changed)
            if ($conversion->getSource() === $this) {
                $conversion->setSource(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Visits[]
     */
    public function getVisits(): Collection
    {
        return $this->visits;
    }

    public function addVisit(Visits $visit): self
    {
        if (!$this->visits->contains($visit)) {
            $this->visits[] = $visit;
            $visit->setSource($this);
        }

        return $this;
    }

    public function removeVisit(Visits $visit): self
    {
        if ($this->visits->contains($visit)) {
            $this->visits->removeElement($visit);
            // set the owning side to null (unless already changed)
            if ($visit->getSource() === $this) {
                $visit->setSource(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|StatisticPromoBlockNews[]
     */
    public function getStatisticPromoBlockNews(): Collection
    {
        return $this->statisticPromoBlockNews;
    }

    public function addStatisticPromoBlockNews(StatisticPromoBlockNews $statisticPromoBlockNews): self
    {
        if (!$this->statisticPromoBlockNews->contains($statisticPromoBlockNews)) {
            $this->statisticPromoBlockNews[] = $statisticPromoBlockNews;
            $statisticPromoBlockNews->setSource($this);
        }

        return $this;
    }

    public function removeStatisticPromoBlockNews(StatisticPromoBlockNews $statisticPromoBlockNews): self
    {
        if ($this->statisticPromoBlockNews->contains($statisticPromoBlockNews)) {
            $this->statisticPromoBlockNews->removeElement($statisticPromoBlockNews);
            // set the owning side to null (unless already changed)
            if ($statisticPromoBlockNews->getSource() === $this) {
                $statisticPromoBlockNews->setSource(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Costs[]
     */
    public function getCosts(): Collection
    {
        return $this->costs;
    }

    public function addCost(Costs $cost): self
    {
        if (!$this->costs->contains($cost)) {
            $this->costs[] = $cost;
            $cost->setSource($this);
        }

        return $this;
    }

    public function removeCost(Costs $cost): self
    {
        if ($this->costs->contains($cost)) {
            $this->costs->removeElement($cost);
            // set the owning side to null (unless already changed)
            if ($cost->getSource() === $this) {
                $cost->setSource(null);
            }
        }

        return $this;
    }
}
