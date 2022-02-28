<?php

namespace App\Entity;

use App\Repository\PostbackRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PostbackRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Postback
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Partners::class, inversedBy="postbacks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $affiliate;

    /**
     * @ORM\ManyToOne(targetEntity=TeasersClick::class, inversedBy="postbacks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $click;

    /**
     * @ORM\Column(type="string", length=255, columnDefinition="ENUM('pending', 'declined', 'approved', 'new')")
     */
    private $status = 'new';

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     */
    private $payout;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     */
    private $payoutRub;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $currencyCode;

    /**
     * @ORM\Column(type="array")
     */
    private $fulldata = [];

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAffiliate(): ?Partners
    {
        return $this->affiliate;
    }

    public function setAffiliate(?Partners $affiliate): self
    {
        $this->affiliate = $affiliate;

        return $this;
    }

    public function getClick(): ?TeasersClick
    {
        return $this->click;
    }

    public function setClick(?TeasersClick $click): self
    {
        $this->click = $click;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPayout(): ?string
    {
        return $this->payout;
    }

    public function setPayout(?string $payout): self
    {
        $this->payout = $payout;

        return $this;
    }

    public function getPayoutRub(): ?string
    {
        return $this->payoutRub;
    }

    public function setPayoutRub(?string $payoutRub): self
    {
        $this->payoutRub = $payoutRub;

        return $this;
    }

    public function getCurrencyCode(): ?string
    {
        return $this->currencyCode;
    }

    public function setCurrencyCode(string $currencyCode): self
    {
        $this->currencyCode = $currencyCode;

        return $this;
    }

    public function getFulldata(): ?array
    {
        $this->fulldata = $this->fulldata ? $this->fulldata : [];

        return $this->fulldata;
    }

    public function setFulldata(array $fulldata): self
    {
        $this->fulldata = $fulldata;

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
    public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTime();

        return $this;
    }
}
