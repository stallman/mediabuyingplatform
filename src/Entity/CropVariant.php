<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CropVariantRepository")
 */
class CropVariant
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $design_number;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $width_teaser_block;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $height_teaser_block;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $width_news_block;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $height_news_block;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignNumber(): ?string
    {
        return $this->design_number;
    }

    public function setDesignNumber(string $design_number): self
    {
        $this->design_number = $design_number;

        return $this;
    }

    public function getWidthTeaserBlock(): ?int
    {
        return $this->width_teaser_block;
    }

    public function setWidthTeaserBlock(?int $width_teaser_block): self
    {
        $this->width_teaser_block = $width_teaser_block;

        return $this;
    }

    public function getHeightTeaserBlock(): ?int
    {
        return $this->height_teaser_block;
    }

    public function setHeightTeaserBlock(?int $height_teaser_block): self
    {
        $this->height_teaser_block = $height_teaser_block;

        return $this;
    }

    public function getWidthNewsBlock(): ?int
    {
        return $this->width_news_block;
    }

    public function setWidthNewsBlock(?int $width_news_block): self
    {
        $this->width_news_block = $width_news_block;

        return $this;
    }

    public function getHeightNewsBlock(): ?int
    {
        return $this->height_news_block;
    }

    public function setHeightNewsBlock(?int $height_news_block): self
    {
        $this->height_news_block = $height_news_block;

        return $this;
    }
}
