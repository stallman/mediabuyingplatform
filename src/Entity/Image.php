<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="image", indexes={
 *     @ORM\Index(name="entity_id_entity_fqn", columns={"entity_id", "entity_fqn"}),
 * })
 * @ORM\Entity(repositoryClass="App\Repository\ImageRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Image
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
    private $entityFQN;

    /**
     * @ORM\Column(type="integer")
     */
    private $entityId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filePath;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $extension;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntityFQN(): ?string
    {
        return $this->entityFQN;
    }

    public function setEntityFQN(string $entityFQN): self
    {
        $this->entityFQN = $entityFQN;

        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getFullPath(): string
    {
        return $_SERVER['DOCUMENT_ROOT'] . $this->filePath  . "/" . $this->fileName;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
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

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullImagePath()
    {
        return $this->getFilePath() . DIRECTORY_SEPARATOR . $this->getFileName();
    }

    /**
     * @return string
     */
    public function getFullCropImagePath()
    {
        return $this->getFilePath() . DIRECTORY_SEPARATOR . 'crop_' .$this->getFileName();
    }

    /**
     * @return mixed|string
     */
    public function getFileNameWithoutExtension()
    {
        $fileNameArray = explode('.', $this->getFileName());

        return $fileNameArray[0];
    }
}
