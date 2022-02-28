<?php

namespace App\Entity;

use App\Repository\MediabuyerDesignsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MediabuyerDesignsRepository::class)
 */
class MediabuyerDesigns
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="mediabuyerDesigns")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mediabuyer;

    /**
     * @ORM\ManyToOne(targetEntity=Design::class, inversedBy="mediabuyerDesigns")
     * @ORM\JoinColumn(nullable=false)
     */
    private $design;

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

    public function getDesign(): ?Design
    {
        return $this->design;
    }

    public function setDesign(?Design $design): self
    {
        $this->design = $design;

        return $this;
    }
}
