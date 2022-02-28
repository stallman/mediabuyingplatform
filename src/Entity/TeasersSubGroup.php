<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Service\DateHelper;

/**
 * @ORM\Table(name="teasers_sub_groups")
 * @ORM\Entity(repositoryClass="App\Repository\TeasersSubGroupRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class TeasersSubGroup implements EntityInterface
{
    /**
     * @var int|null
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TeasersGroup", inversedBy="teasersSubGroup", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $teaserGroup;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Teaser", mappedBy="teasersSubGroup")
     */
    private $teasers;

    /**
     * @ORM\Column(type="string", length=80, nullable=false)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     */
    private $isActive;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\NewsCategory", inversedBy="teasersSubGroup")
     * @ORM\JoinTable(name="teasersSubGroup_newsCategories_relations")
     */
    private $newsCategories;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=TeasersSubGroupSettings::class, mappedBy="teasersSubGroup", cascade={"persist"})
     * @ORM\OrderBy({"geoCode" = "ASC"})
     */
    private $teasersSubGroupSettings;

    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $is_deleted = false;

    /**
     * TeasersSubGroup constructor.
     */
    public function __construct()
    {
        $this->newsCategories = new ArrayCollection();
        $this->teasersSubGroupSettings = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name ? $this->name : get_class($this);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeaserGroup(): ?TeasersGroup
    {
        return $this->teaserGroup;
    }

    public function setTeaserGroup(TeasersGroup $teaserGroup): self
    {
        $this->teaserGroup = $teaserGroup;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getTeasers(): ?Collection
    {
        return $this->teasers;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return DateHelper::formatDefaultDateTime( $this->createdAt );
    }

    /**
     * @ORM\PrePersist()
     * @return $this
     */
    public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTime();

        return $this;
    }

    /**
     * @return Collection|NewsCategory[]
     */
    public function getNewsCategories(): Collection
    {
        return $this->newsCategories;
    }

    public function addNewsCategory(NewsCategory $category): self
    {
        if(!$this->newsCategories->contains($category)){
            $this->newsCategories[] = $category;
        }

        return $this;
    }

    public function removeNewsCategory(NewsCategory $category): self
    {
        if($this->newsCategories->contains($category)){
            $this->newsCategories->removeElement($category);
        }

        return $this;
    }

    public function getTeasersSubGroupSettings(): ?Collection
    {
        return $this->teasersSubGroupSettings;
    }

    public function setTeasersSubGroupSettings(TeasersSubGroupSettings $teasersSubGroupSetting): self
    {
        $this->teasersSubGroupSettings = new ArrayCollection([$teasersSubGroupSetting]);

        return $this;
    }

    public function addTeasersSubGroupSetting(TeasersSubGroupSettings $teasersSubGroupSetting): self
    {
        if(!$this->teasersSubGroupSettings->contains($teasersSubGroupSetting)){
            $this->teasersSubGroupSettings[] = $teasersSubGroupSetting;
            $teasersSubGroupSetting->setTeasersSubGroup($this);
        }

        return $this;
    }

    public function removeTeasersSubGroupSetting(TeasersSubGroupSettings $teasersSubGroupSetting): self
    {
        if($this->teasersSubGroupSettings->contains($teasersSubGroupSetting)){
            $this->teasersSubGroupSettings->removeElement($teasersSubGroupSetting);
            // set the owning side to null (unless already changed)
            if($teasersSubGroupSetting->getTeasersSubGroup() === $this){
                $teasersSubGroupSetting->setTeasersSubGroup(null);
            }
        }

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(?bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }
}
