<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CurrencyListRepository")
 */
class CurrencyList
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $iso_code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $symbol;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Sources", mappedBy="currency")
     */
    private $sources;

    /**
     * @ORM\OneToMany(targetEntity=Costs::class, mappedBy="currency")
     */
    private $costs;

    public function __construct()
    {
        $this->costs = new ArrayCollection();
    }

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

    public function getIsoCode(): ?string
    {
        return $this->iso_code;
    }

    public function setIsoCode(string $iso_code): self
    {
        $this->iso_code = $iso_code;

        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * @return Collection|Costs[]
     */
    public function getCosts(): Collection
    {
        return $this->costs;
    }

    public function addCosts(Costs $costs): self
    {
        if (!$this->costs->contains($costs)) {
            $this->costs[] = $costs;
            $costs->setCurrency($this);
        }

        return $this;
    }

    public function removeCosts(Costs $costs): self
    {
        if ($this->costs->contains($costs)) {
            $this->costs->removeElement($costs);
            // set the owning side to null (unless already changed)
            if ($costs->getCurrency() === $this) {
                $costs->setCurrency(null);
            }
        }

        return $this;
    }
}
