<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GeoRepository")
 */
class Geo
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
    private $countryName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $cityName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $cityNameRu;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $langCode;



    public function getId(): int
    {
        return $this->id;
    }

    public function getCountryName(): string
    {
        return $this->countryName;
    }

    public function setCountryName(string $countryName): self
    {
        $this->countryName = $countryName;

        return $this;
    }

    public function getCityName(): string
    {
        return $this->cityName;
    }

    public function setCityName(string $cityName): self
    {
        $this->cityName = $cityName;

        return $this;
    }

    public function getCityNameRu(): string
    {
        return $this->cityNameRu;
    }

    public function setCityNameRu(string $cityNameRu): self
    {
        $this->cityNameRu = $cityNameRu;

        return $this;
    }

    public function getLangCode(): string
    {
        return $this->cityNameRu;
    }

    public function setLangCode(string $langCode): self
    {
        $this->langCode = $langCode;

        return $this;
    }
}
