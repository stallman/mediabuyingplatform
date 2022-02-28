<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Service\DateHelper;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @ORM\Table(name="teasers_groups")
 * @ORM\Entity(repositoryClass="App\Repository\TeasersGroupRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class TeasersGroup implements EntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=80, nullable=false, unique=true)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeasersSubGroup", mappedBy="teaserGroup")
     */
    private $teasersSubGroup;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     */
    private $isActive;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $createdAt;

    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $is_deleted = false;

    public function __construct()
    {
        $this->teasersSubGroup = new ArrayCollection();
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

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

    /**
     * @return Collection|TeasersSubGroup[]
     */
    public function getTeasersSubGroup(): Collection
    {
        return $this->teasersSubGroup;
    }

    public function addTeasersSubGroup(TeasersSubGroup $teaserSubGroup): self
    {
        if (!$this->teasersSubGroup->contains($teaserSubGroup)) {
            $this->teasersSubGroup[] = $teaserSubGroup;
        }

        return $this;
    }

    public function removeTeasersSubGroup(TeasersSubGroup $teaserSubGroup): self
    {
        if ($this->teasersSubGroup->contains($teaserSubGroup)) {
            $this->teasersSubGroup->removeElement($teaserSubGroup);
        }

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

    public function getCreatedAt(): string
    {
        return DateHelper::formatDefaultDateTime( $this->createdAt );
    }

    public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTime();

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

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new UniqueEntity([
            'fields' => 'name',
        ]));
    }
}
