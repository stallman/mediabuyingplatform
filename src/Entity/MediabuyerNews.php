<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MediabuyerNewsRepository")
 */
class MediabuyerNews
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="mediabuyerNews", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $mediabuyer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\News", cascade={"persist"}, inversedBy="mediabuyerNews")
     * @ORM\JoinColumn(nullable=false)
     */
    private $news;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $dropTeasers = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $dropSources = [];


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

    public function getDropTeasers(): ?string
    {
        $this->dropTeasers = $this->dropTeasers ? $this->dropTeasers : [];

        return implode(",", $this->dropTeasers);
    }

    public function setDropTeasers(?string $dropTeasers): self
    {
        $this->dropTeasers = array_unique(explode(",", $dropTeasers));

        return $this;
    }

    public function getDropSources(): ?string
    {
        $this->dropSources = $this->dropSources ? $this->dropSources : [];

        return implode(",", $this->dropSources);
    }

    public function setDropSources(?string $dropSources): self
    {
        $this->dropSources = array_unique(explode(",", $dropSources));

        return $this;
    }
}
