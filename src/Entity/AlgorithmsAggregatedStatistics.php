<?php

namespace App\Entity;

use App\Repository\AlgorithmsAggregatedStatisticsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AlgorithmsAggregatedStatisticsRepository::class)
 */
class AlgorithmsAggregatedStatistics
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="algorithmsAggregatedStatistics")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mediabuyer;

    /**
     * @ORM\ManyToOne(targetEntity=Algorithm::class, inversedBy="algorithmsAggregatedStatistics")
     * @ORM\JoinColumn(nullable=false)
     */
    private $algorithm;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=4)
     */
    private $CTR = 0;

    /**
     * @ORM\Column(type="integer", precision=7, scale=4, nullable=false, options={"default" : 0})
     */
    private $conversion = 0;

    /**
     * @ORM\Column(type="integer", precision=7, scale=4, nullable=false, options={"default" : 0})
     */
    private $approveConversion = 0;

    //* @ORM\Column(type="decimal", precision=7, scale=4, nullable=false, options={"default" : 0})
    /**
     * @ORM\Column(type="float")
     */
    private $eCPM = 0;

    //* @ORM\Column(type="decimal", precision=8, scale=4, nullable=false, options={"default" : 0})
    /**
     * @ORM\Column(type="float")
     */
    private $EPC = 0;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=4, nullable=false, options={"default" : 0})
     */
    private $CR = 0;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=4, nullable=false, options={"default" : 0})
     */
    private $ROI = 0;

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

    public function getAlgorithm(): ?Algorithm
    {
        return $this->algorithm;
    }

    public function setAlgorithm(?Algorithm $algorithm): self
    {
        $this->algorithm = $algorithm;

        return $this;
    }

    public function getCTR(): ?string
    {
        return $this->CTR;
    }

    public function setCTR(string $CTR): self
    {
        $this->CTR = $CTR;

        return $this;
    }

    public function getConversion(): ?string
    {
        return $this->conversion;
    }

    public function setConversion(?string $conversion): self
    {
        $this->conversion = $conversion;

        return $this;
    }

    public function getApproveConversion(): ?string
    {
        return $this->approveConversion;
    }

    public function setApproveConversion(string $approveConversion): self
    {
        $this->approveConversion = $approveConversion;

        return $this;
    }

    public function getECPM(): ?string
    {
        return $this->eCPM;
    }

    public function setECPM(?string $eCPM): self
    {
        $this->eCPM = $eCPM;

        return $this;
    }

    public function getEPC(): ?string
    {
        return $this->EPC;
    }

    public function setEPC(?string $EPC): self
    {
        $this->EPC = $EPC;

        return $this;
    }

    public function getCR(): ?string
    {
        return $this->CR;
    }

    public function setCR(?string $CR): self
    {
        $this->CR = $CR;

        return $this;
    }

    public function getROI(): ?string
    {
        return $this->CR;
    }

    public function setROI(?string $ROI): self
    {
        $this->ROI = $ROI;

        return $this;
    }
}
