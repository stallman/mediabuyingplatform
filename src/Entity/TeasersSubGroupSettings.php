<?php

namespace App\Entity;

use App\Repository\TeasersSubGroupSettingsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=TeasersSubGroupSettingsRepository::class)
 * @UniqueEntity(
 *     fields={"geoCode", "teasersSubGroup"},
 *     message="duplicate"
 * )
 */
class TeasersSubGroupSettings
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Country", inversedBy="teasersSubGroupSettings")
     * @ORM\JoinColumn(name="geo_code", referencedColumnName="id", nullable=true)
     */
    private $geoCode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $link;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2)
     */
    private $approveAveragePercentage;

    /**
     * @ORM\ManyToOne(targetEntity=TeasersSubGroup::class, inversedBy="teasersSubGroupSettings", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $teasersSubGroup;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isAutoCalculate = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGeoCode(): ?Country
    {
        return $this->geoCode;
    }

    public function setGeoCode(?Country $geoCode): self
    {
        $this->geoCode = $geoCode;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getApproveAveragePercentage(): ?float
    {
        return $this->approveAveragePercentage;
    }

    public function setApproveAveragePercentage(float $approveAveragePercentage): self
    {
        $this->approveAveragePercentage = $approveAveragePercentage;

        return $this;
    }

    public function getTeasersSubGroup(): ?TeasersSubGroup
    {
        return $this->teasersSubGroup;
    }

    public function setTeasersSubGroup(?TeasersSubGroup $teasersSubGroup): self
    {
        $this->teasersSubGroup = $teasersSubGroup;

        return $this;
    }

    public function getIsAutoCalculate(): ?bool
    {
        return $this->isAutoCalculate;
    }

    public function setIsAutoCalculate(?bool $isAutoCalculate): self
    {
        $this->isAutoCalculate = $isAutoCalculate ? $isAutoCalculate : false;

        return $this;
    }
}
