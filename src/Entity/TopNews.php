<?php

namespace App\Entity;

use App\Repository\TopNewsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TopNewsRepository::class)
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_map_idx", columns={
 *     "news_id",
 *     "mediabuyer_id",
 *     "geo_code",
 *     "traffic_type"
 * })
 * })
 */
class TopNews
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=News::class, inversedBy="topNews")
     * @ORM\JoinColumn(nullable=false)
     */
    private $news;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $geoCode;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $mediabuyer;

    /**
     * @ORM\Column(name="traffic_type", type="string", length=255, columnDefinition="ENUM('desktop', 'tablet', 'mobile')")
     */
    private $trafficType;

    /**

     * @ORM\Column(type="float")
     */
    private $eCPM;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default" : 0})
     */
    private $impressions = 0;

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

    public function getGeoCode(): ?string
    {
        return $this->geoCode;
    }

    public function setGeoCode(string $geoCode): self
    {
        $this->geoCode = $geoCode;

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

    public function getTrafficType(): ?string
    {
        return $this->trafficType;
    }

    public function setTrafficType(string $trafficType): self
    {
        $this->trafficType = $trafficType;

        return $this;
    }

    public function getECPM(): ?string
    {
        return $this->eCPM;
    }

    public function setECPM(string $eCPM): self
    {
        $this->eCPM = $eCPM;

        return $this;
    }

    public function getImpressions(): ?int
    {
        return $this->impressions;
    }

    public function setImpressions(int $impressions): self
    {
        $this->impressions = $impressions;

        return $this;
    }
}
