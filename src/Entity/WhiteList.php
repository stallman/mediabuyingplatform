<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="white_list")
 * @ORM\Entity(repositoryClass="App\Repository\WhiteListRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class WhiteList implements EntityInterface
{
    /**
     * @var int|null
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="whiteList", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $buyer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $field;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $groupId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $groupName;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBuyer(): ?User
    {
        return $this->buyer;
    }

    public function setBuyer(User $buyer): self
    {
        $this->buyer = $buyer;

        return $this;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(?string $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function getGroupId(): ?string
    {
        return $this->groupId;
    }

    public function setGroupId(?string $groupId): self
    {
        $this->groupId = $groupId;

        return $this;
    }

    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    public function setGroupName(?string $groupName): self
    {
        $this->groupName = $groupName;

        return $this;
    }

}