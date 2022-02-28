<?php

namespace App\Entity;

use App\Repository\DomainParkingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\IsFalse;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @ORM\Entity(repositoryClass=DomainParkingRepository::class)
 */
class DomainParking
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="domains")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $domain;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_main;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $sendPulseId;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $certEndDate;

    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $is_deleted = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $errorMessage;

    /**
     * @ORM\OneToMany(targetEntity=Visits::class, mappedBy="domain")
     */
    private $visits;

    public function __construct()
    {
        $this->visits = new ArrayCollection();
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

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function getIsMain(): ?bool
    {
        return $this->is_main;
    }

    public function setIsMain(?bool $is_main): self
    {
        $this->is_main = $is_main;

        return $this;
    }

    public function getSendPulseId(): ?string
    {
        return $this->sendPulseId;
    }

    public function setSendPulseId(?string $sendPulseId): self
    {
        $this->sendPulseId = $sendPulseId;

        return $this;
    }

    public function getCertEndDate(): ?\DateTimeInterface
    {
        return $this->certEndDate;
    }

    /**
     * @param \DateTime|null $certEndDate
     * @return $this
     */
    public function setCertEndDate(?\DateTime $certEndDate): self
    {
        $this->certEndDate = $certEndDate;

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


    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new UniqueEntity([
            'fields' => 'domain',
        ]));
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
            $visit->setDomain($this);
        }

        return $this;
    }

    public function removeVisit(Visits $visit): self
    {
        if ($this->visits->contains($visit)) {
            $this->visits->removeElement($visit);
            // set the owning side to null (unless already changed)
            if ($visit->getDomain() === $this) {
                $visit->setDomain(null);
            }
        }

        return $this;
    }
}
