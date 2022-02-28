<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MediabuyerNewsRotationRepository")
 */
class MediabuyerNewsRotation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="mediabuyerNewsRotation", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $mediabuyer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\News", cascade={"persist"}, inversedBy="mediabuyerNewsRotation")
     * @ORM\JoinColumn(nullable=false)
     */
    private $news;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isRotation = false;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return User
     */
    public function getMediabuyer(): ?User
    {

        return $this->mediabuyer;
    }

    /**
     * @param User $mediabuyer
     * @return $this
     */
    public function setMediabuyer(?User $mediabuyer): self
    {
        $this->mediabuyer = $mediabuyer;

        return $this;
    }

    /**
     * @return News
     */
    public function getNews(): ?News
    {
        return $this->news;
    }

    /**
     * @param News $news
     * @return $this
     */
    public function setNews(?News $news): self
    {
        $this->news = $news;

        return $this;
    }

    public function getIsRotation(): ?bool
    {
        return $this->isRotation;
    }

    public function setIsRotation(?bool $isRotation): self
    {
        $this->isRotation = $isRotation;

        return $this;
    }
}