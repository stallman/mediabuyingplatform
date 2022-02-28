<?php

namespace App\Entity;

use App\Repository\StatisticNewsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StatisticNewsRepository::class)
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_map_idx", columns={
 *         "news_id",
 *         "mediabuyer_id"
 *     })
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class StatisticNews
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=News::class, inversedBy="statistic")
     * @ORM\JoinColumn(nullable=false)
     */
    private $news;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $mediabuyer;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default" : 0})
     */
    private $innerShow = 0;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default" : 0})
     */
    private $innerClick = 0;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=4, nullable=false, options={"default" : 0})
     */
    private $innerCTR = 0;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=4, nullable=false, options={"default" : 0})
     */
    private $inner_eCPM = 0;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default" : 0})
     */
    private $click = 0;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default" : 0})
     */
    private $clickOnTeaser = 0;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4, nullable=false, options={"default" : 0})
     */
    private $probiv = 0;

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
     * @ORM\Column(type="decimal", precision=9, scale=4, nullable=false, options={"default" : 0})
     */
    private $involvement = 0;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=4, nullable=false, options={"default" : 0})
     */
    private $EPC = 0;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=4, nullable=false, options={"default" : 0})
     */
    private $CR = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default" : 0})
     */
    private ?int $uniqVisits;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNews(): ?News
    {
        return $this->news;
    }

    public function setNews(?News $news): self
    {
        $this->news = $news;

        return $this;
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

    public function getInnerShow(): ?string
    {
        return $this->innerShow;
    }

    public function setInnerShow(?string $innerShow): self
    {
        $this->innerShow = $innerShow;

        return $this;
    }

    public function getInnerClick(): ?int
    {
        return $this->innerClick;
    }

    public function setInnerClick(?int $innerClick): self
    {
        $this->innerClick = $innerClick;

        return $this;
    }

    public function getInnerCTR(): ?string
    {
        return $this->innerCTR;
    }

    public function setInnerCTR(?string $innerCTR): self
    {
        $this->innerCTR = $innerCTR;

        return $this;
    }

    public function getInnerECPM(): ?string
    {
        return $this->inner_eCPM;
    }

    public function setInnerECPM(?string $inner_eCPM): self
    {
        $this->inner_eCPM = $inner_eCPM;

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

    public function getUniqVisits(): ?int
    {
        return $this->uniqVisits;
    }

    public function setUniqVisits(?int $uniqVisits): self
    {
        $this->uniqVisits = $uniqVisits;

        return $this;
    }

    public function getClickOnTeaser(): ?int
    {
        return $this->clickOnTeaser;
    }

    public function setClickOnTeaser(?int $clickOnTeaser): self
    {
        $this->clickOnTeaser = $clickOnTeaser;

        return $this;
    }

    public function getProbiv(): ?string
    {
        return $this->probiv;
    }

    public function setProbiv(?string $probiv): self
    {
        $this->probiv = $probiv;

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

    public function getInvolvement(): ?string
    {
        return $this->involvement;
    }

    public function setInvolvement(?string $involvement): self
    {
        $this->involvement = $involvement;

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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updateAt): self
    {
        $this->updatedAt = $updateAt ? $updateAt: new \DateTime();

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
    */
    public function updatedTimestamps(): void
    {
        $this->setUpdatedAt(new \DateTime('now'));
    }

}
