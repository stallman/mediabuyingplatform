<?php

namespace App\Entity;

use App\Repository\OtherFiltersDataRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OtherFiltersDataRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="map_idx", columns={
 *     "mediabuyer_id",
 *      "type"
 * })
 * })
 *
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_map_idx", columns={
 *     "mediabuyer_id",
 *     "type",
 *     "options"
 * })
 * })
*/

class OtherFiltersData
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $mediabuyer;

    /**
     * @ORM\Column(type="string", length=255, columnDefinition="ENUM('utm_term', 'utm_content', 'utm_campaign', 'subid1', 'subid2', 'subid3', 'subid4', 'subid5')")
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $options;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getOptions(): ?string
    {
        return $this->options;
    }

    public function setOptions(string $options): self
    {
        $this->options = $options;

        return $this;
    }
}
