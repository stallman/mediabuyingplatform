<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\Length;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CountryRepository")
 */
class Country
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=2,  unique=true)
     */
    private $iso_code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\News", mappedBy="countries")
     */
    private $news;

    /**
     * @ORM\OneToMany(targetEntity=Conversions::class, mappedBy="country")
     */
    private $conversions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeasersSubGroupSettings", mappedBy="geoCode")
     */
    private $teasersSubGroupSettings;


    public function __construct()
    {
        $this->news = new ArrayCollection();
        $this->conversions = new ArrayCollection();
        $this->teasersSubGroupSettings = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function __toString()
    {
        return $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsoCode(): ?string
    {
        return $this->iso_code;
    }

    public function setIsoCode(?string $iso_code): self
    {
        $this->iso_code = strtoupper($iso_code);

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|News[]
     */
    public function getNews(): Collection
    {
        return $this->news;
    }

    public function addNews(News $news): self
    {
        if (!$this->news->contains($news)) {
            $this->news[] = $news;
            $news->addCountry($this);
        }

        return $this;
    }

    public function removeNews(News $news): self
    {
        if ($this->news->contains($news)) {
            $this->news->removeElement($news);
            $news->removeCountry($this);
        }

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
            $conversion->setCountry($this);
        }

        return $this;
    }

    public function removeConversion(Conversions $conversion): self
    {
        if ($this->conversions->contains($conversion)) {
            $this->conversions->removeElement($conversion);
            // set the owning side to null (unless already changed)
            if ($conversion->getCountry() === $this) {
                $conversion->setCountry(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Conversions[]
     */
    public function getTeasersSubGroupSettings(): Collection
    {
        return $this->teasersSubGroupSettings;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new UniqueEntity([
            'fields' => 'iso_code',
        ]));
        $metadata->addPropertyConstraint('name', new Length([
            'max' => 50,
            'maxMessage' => 'Название страны не может быть длиннее {{ limit }} символов',
            'allowEmptyString' => false,
        ]));

        $metadata->addPropertyConstraint('iso_code', new Length([
            'max' => 2,
            'maxMessage' => 'Код страны в формате ISO не может быть длиннее {{ limit }} символов',
            'allowEmptyString' => false,
        ]));
    }
}
