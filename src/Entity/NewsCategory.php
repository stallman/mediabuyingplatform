<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @ORM\Table(name="news_categories")
 * @ORM\Entity(repositoryClass="App\Repository\NewsCategoryRepository")
 */
class NewsCategory implements EntityInterface
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
    private $title;

    /**
     * @ORM\Column(type="string", unique=true,  length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isEnabled;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\News", mappedBy="categories")
     */
    private $news;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\TeasersSubGroup", mappedBy="newsCategories")
     */
    private $teasersSubGroup;

    public function __construct()
    {
        $this->news = new ArrayCollection();
        $this->teasersSubGroup = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getTitle() ? $this->getTitle() : get_class($this);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getIsEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * @return Collection|News[]
     */
    public function getNews(): Collection
    {
        return $this->news;
    }

    public function addNews(News $news): self
    {
        if (!$this->news->contains($news)) {
            $this->news[] = $news;
            $news->addCategory($this);
        }

        return $this;
    }

    public function removeNews(News $news): self
    {
        if ($this->news->contains($news)) {
            $this->news->removeElement($news);
            $news->removeCategory($this);
        }

        return $this;
    }

    /**
     * @return Collection|TeasersSubGroup[]
     */
    public function getTeasersSubGroup(): Collection
    {
        return $this->teasersSubGroup;
    }

    public function addTeasersSubGroup(TeasersSubGroup $teasersSubGroup): self
    {
        if (!$this->teasersSubGroup->contains($teasersSubGroup)) {
            $this->teasersSubGroup[] = $teasersSubGroup;
            $teasersSubGroup->addNewsCategory($this);
        }

        return $this;
    }

    public function removeTeasersSubGroup(TeasersSubGroup $teasersSubGroup): self
    {
        if ($this->teasersSubGroup->contains($teasersSubGroup)) {
            $this->teasersSubGroup->removeElement($teasersSubGroup);
            $teasersSubGroup->removeNewsCategory($this);
        }

        return $this;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new UniqueEntity([
            'fields' => 'slug',
        ]));
    }
}
