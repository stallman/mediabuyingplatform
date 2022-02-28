<?php

namespace App\Entity;

use App\Repository\StatisticTeasersRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StatisticTeasersRepository::class)
 */
class StatisticTeasers
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Teaser::class, inversedBy="statistic")
     * @ORM\JoinColumn(nullable=false)
     */
    private $teaser;


    /**
     * @ORM\Column(type="integer", nullable=false, options={"default" : 0})
     */
    private $teaserShow = 0;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default" : 0})
     */
    private $click = 0;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default" : 0})
     */
    private $conversion = 0;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default" : 0})
     */
    private $approveConversion = 0;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default" : 0})
     */
    private $approve = 0;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=4, nullable=false, options={"default" : 0})
     */
    private $eCPM = 0;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=4, nullable=false, options={"default" : 0})
     */
    private $EPC = 0;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=4, nullable=false, options={"default" : 0})
     */
    private $CTR = 0;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=4, nullable=false, options={"default" : 0})
     */
    private $CR = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeaser(): ?Teaser
    {
        return $this->teaser;
    }

    public function setTeaser(?Teaser $teaser): self
    {
        $this->teaser = $teaser;

        return $this;
    }

    public function getClick(): ?int
    {
        return $this->click;
    }

    public function setClick(?int $click): self
    {
        $this->click = $click;

        return $this;
    }

    public function getTeaserShow(): ?string
    {
        return $this->teaserShow;
    }

    public function setTeaserShow(?string $teaserShow): self
    {
        $this->teaserShow = $teaserShow;

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

    public function getApprove(): ?int
    {
        return $this->approve;
    }

    public function setApprove(?int $approve): self
    {
        $this->approve = $approve;

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

    public function getCTR(): ?string
    {
        return $this->CTR;
    }

    public function setCTR(?string $CTR): self
    {
        $this->CTR = $CTR;

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
}
