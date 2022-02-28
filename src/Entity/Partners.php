<?php

namespace App\Entity;

use App\Repository\PartnersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PartnersRepository::class)
 */
class Partners
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $postback;

    /**
     * @ORM\ManyToOne(targetEntity=CurrencyList::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $Currency;

    /**
     * @ORM\Column(type="text")
     */
    private $status_approved;

    /**
     * @ORM\Column(type="text")
     */
    private $status_declined;

    /**
     * @ORM\Column(type="text")
     */
    private $status_pending;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $macros_uniq_click;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $macros_payment;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $macros_status;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     */
    private $is_deleted = false;

    /**
     * @ORM\OneToMany(targetEntity=Conversions::class, mappedBy="affilate")
     */
    private $conversions;

    /**
     * @ORM\OneToMany(targetEntity=Postback::class, mappedBy="affiliate", orphanRemoval=true)
     */
    private $postbacks;

    public function __construct()
    {
        $this->conversions = new ArrayCollection();
        $this->postbacks = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getPostback(): ?string
    {
        return $this->postback;
    }

    public function setPostback(string $postback): self
    {
        $this->postback = $postback;

        return $this;
    }

    public function getCurrency(): ?CurrencyList
    {
        return $this->Currency;
    }

    public function setCurrency(?CurrencyList $Currency): self
    {
        $this->Currency = $Currency;

        return $this;
    }

    public function getStatusPending(): ?string
    {
        return $this->status_pending;
    }

    public function setStatusPending(string $status_pending): self
    {
        $this->status_pending = $status_pending;

        return $this;
    }

    public function getStatusDeclined(): ?string
    {
        return $this->status_declined;
    }

    public function setStatusDeclined(string $status_declined): self
    {
        $this->status_declined = $status_declined;

        return $this;
    }

    public function getStatusApproved(): ?string
    {
        return $this->status_approved;
    }

    public function setStatusApproved(string $status_approved): self
    {
        $this->status_approved = $status_approved;

        return $this;
    }

    public function getMacrosUniqClick(): ?string
    {
        return $this->macros_uniq_click;
    }

    public function setMacrosUniqClick(?string $macros_uniq_click): self
    {
        $this->macros_uniq_click = $macros_uniq_click;

        return $this;
    }

    public function getMacrosPayment(): ?string
    {
        return $this->macros_payment;
    }

    public function setMacrosPayment(?string $macros_payment): self
    {
        $this->macros_payment = $macros_payment;

        return $this;
    }

    public function getMacrosStatus(): ?string
    {
        return $this->macros_status;
    }

    public function setMacrosStatus(?string $macros_status): self
    {
        $this->macros_status = $macros_status;

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
            $conversion->setAffilateId($this);
        }

        return $this;
    }

    public function removeConversion(Conversions $conversion): self
    {
        if ($this->conversions->contains($conversion)) {
            $this->conversions->removeElement($conversion);
            // set the owning side to null (unless already changed)
            if ($conversion->getAffilateId() === $this) {
                $conversion->setAffilateId(null);
            }
        }

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
            $postback->setAffiliate($this);
        }

        return $this;
    }

    public function removePostback(Postback $postback): self
    {
        if ($this->postbacks->contains($postback)) {
            $this->postbacks->removeElement($postback);
            // set the owning side to null (unless already changed)
            if ($postback->getAffiliate() === $this) {
                $postback->setAffiliate(null);
            }
        }

        return $this;
    }

}
