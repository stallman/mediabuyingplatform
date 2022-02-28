<?php

namespace App\Entity;

use App\Repository\ShowNewsRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass=ShowNewsRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class ShowNews
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=News::class, inversedBy="showNews")
     * @ORM\JoinColumn(nullable=false)
     */
    private $news;

    /**
     * @ORM\Column(type="string", length=255, columnDefinition="ENUM('full', 'short')")
     */
    private $pageType;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $mediabuyer;

    /**
     * @ORM\ManyToOne(targetEntity=Algorithm::class, inversedBy="showNews")
     */
    private $algorithm;

    /**
     * @ORM\ManyToOne(targetEntity=Design::class, inversedBy="showNews")
     * @ORM\JoinColumn(nullable=false)
     */
    private $design;

    /**
     * @ORM\ManyToOne(targetEntity=Sources::class, inversedBy="showNews")
     * @ORM\JoinColumn(nullable=true)
     */
    private $source;

    /**
     * @var UuidInterface
     * @ORM\Column(name="uuid", type="uuid")
     */
    private $uuid;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

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

    public function getPageType(): ?string
    {
        return $this->pageType;
    }

    public function setPageType(string $pageType): self
    {
        $this->pageType = $pageType;

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

    public function getAlgorithm(): ?Algorithm
    {
        return $this->algorithm;
    }

    public function setAlgorithm(?Algorithm $algorithm): self
    {
        $this->algorithm = $algorithm;

        return $this;
    }

    public function getDesign(): ?Design
    {
        return $this->design;
    }

    public function setDesign(?Design $design): self
    {
        $this->design = $design;

        return $this;
    }

    public function getSource(): ?Sources
    {
        return $this->source;
    }

    public function setSource(?Sources $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function setUuid(UuidInterface $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist()
     * @return $this
     */
    public function setCreatedAt($createdAt = null): self
    {
        $this->createdAt = (null !== $createdAt && $createdAt instanceof \DateTimeInterface)
            ? $createdAt
            : new \DateTime() ;

        return $this;
    }
}
