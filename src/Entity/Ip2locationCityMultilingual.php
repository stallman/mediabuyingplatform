<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ip2locationCityMultilingual
 *
 * @ORM\Table(name="ip2location_city_multilingual")
 * @ORM\Entity
 */
class Ip2locationCityMultilingual
{
    /**
     * @var string
     *
     * @ORM\Column(name="country_alpha2_code", type="string", length=2, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $countryAlpha2Code = '';

    /**
     * @var string
     *
     * @ORM\Column(name="region_name", type="string", length=128, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $regionName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="city_name", type="string", length=128, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $cityName = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="country_numeric_code", type="string", length=3, nullable=true)
     */
    private $countryNumericCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="country_name", type="string", length=64, nullable=true)
     */
    private $countryName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="region_code", type="string", length=10, nullable=true)
     */
    private $regionCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="lang_code", type="string", length=5, nullable=true)
     */
    private $langCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="lang_name", type="string", length=50, nullable=true)
     */
    private $langName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="lang_region_name", type="string", length=200, nullable=true)
     */
    private $langRegionName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="lang_city_name", type="string", length=200, nullable=true)
     */
    private $langCityName;

    public function getCountryAlpha2Code(): ?string
    {
        return $this->countryAlpha2Code;
    }

    public function getRegionName(): ?string
    {
        return $this->regionName;
    }

    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function getCountryNumericCode(): ?string
    {
        return $this->countryNumericCode;
    }

    public function setCountryNumericCode(?string $countryNumericCode): self
    {
        $this->countryNumericCode = $countryNumericCode;

        return $this;
    }

    public function getCountryName(): ?string
    {
        return $this->countryName;
    }

    public function setCountryName(?string $countryName): self
    {
        $this->countryName = $countryName;

        return $this;
    }

    public function getRegionCode(): ?string
    {
        return $this->regionCode;
    }

    public function setRegionCode(?string $regionCode): self
    {
        $this->regionCode = $regionCode;

        return $this;
    }

    public function getLangCode(): ?string
    {
        return $this->langCode;
    }

    public function setLangCode(?string $langCode): self
    {
        $this->langCode = $langCode;

        return $this;
    }

    public function getLangName(): ?string
    {
        return $this->langName;
    }

    public function setLangName(?string $langName): self
    {
        $this->langName = $langName;

        return $this;
    }

    public function getLangRegionName(): ?string
    {
        return $this->langRegionName;
    }

    public function setLangRegionName(?string $langRegionName): self
    {
        $this->langRegionName = $langRegionName;

        return $this;
    }

    public function getLangCityName(): ?string
    {
        return $this->langCityName;
    }

    public function setLangCityName(?string $langCityName): self
    {
        $this->langCityName = $langCityName;

        return $this;
    }


}
