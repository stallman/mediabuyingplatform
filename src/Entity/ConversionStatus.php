<?php

namespace App\Entity;

use App\Repository\ConversionStatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConversionStatusRepository::class)
 */
class ConversionStatus
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $label_ru;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $label_en;

    /**
     * @ORM\OneToMany(targetEntity=Conversions::class, mappedBy="status")
     */
    private $conversions;

    public function __construct()
    {
        $this->conversions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getLabelRu(): ?string
    {
        return $this->label_ru;
    }

    public function setLabelRu(string $label_ru): self
    {
        $this->label_ru = $label_ru;

        return $this;
    }

    public function getLabelEn(): ?string
    {
        return $this->label_en;
    }

    public function setLabelEn(string $label_en): self
    {
        $this->label_en = $label_en;

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
            $conversion->setStatus($this);
        }

        return $this;
    }

    public function removeConversion(Conversions $conversion): self
    {
        if ($this->conversions->contains($conversion)) {
            $this->conversions->removeElement($conversion);
            // set the owning side to null (unless already changed)
            if ($conversion->getStatus() === $this) {
                $conversion->setStatus(null);
            }
        }

        return $this;
    }
}
