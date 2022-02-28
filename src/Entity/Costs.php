<?php

namespace App\Entity;

use App\Repository\CostsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CostsRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_map_idx", columns={
 *     "news_id",
 *     "source_id",
 *     "date",
 * })
 * })
 */
class Costs implements EntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="costs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mediabuyer;

    /**
     * @ORM\ManyToOne(targetEntity=News::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $news;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $campaign;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=Sources::class, inversedBy="costs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $source;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=4)
     */
    private $cost;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=4)
     */
    private $costRub;

    /**
     * @ORM\ManyToOne(targetEntity=CurrencyList::class, inversedBy="costs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $currency;

    /**
     * @ORM\Column(type="boolean",  options={"default" : 0}))
     */
    private $isFinal = 0;

    /**
     * @ORM\Column(type="date")
     */
    private $dateSetData;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNews(): ?News
    {
        return $this->news;
    }

    public function setNews(?News $news): self
    {
        $this->news = $news;

        return $this;
    }

    public function getCampaign(): ?string
    {
        return $this->campaign;
    }

    public function setCampaign(?string $campaign): self
    {
        $this->campaign = $campaign;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    public function getCost(): ?string
    {
        return $this->cost;
    }

    public function setCost(string $cost): self
    {
        $this->cost = $cost;

        return $this;
    }

    public function getCostRub(): ?string
    {
        return $this->costRub;
    }

    public function setCostRub(string $costRub): self
    {
        $this->costRub = $costRub;

        return $this;
    }

    public function getCurrency(): ?CurrencyList
    {
        return $this->currency;
    }

    public function setCurrency(?CurrencyList $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getIsFinal(): ?bool
    {
        return $this->isFinal;
    }

    public function setIsFinal(bool $isFinal): self
    {
        $this->isFinal = $isFinal;

        return $this;
    }

    public function getDateSetData(): ?\DateTimeInterface
    {
        return $this->dateSetData;
    }

    /**
     * @ORM\PrePersist()
     * @return $this
     */
    public function setDateSetData(): self
    {
        $this->dateSetData = new \DateTime();

        return $this;
    }
}
