<?php


namespace App\Entity\Traits;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

trait UploadFileTrait
{
    /**
     * @ORM\Column(name="full_image", type="string")
     * @var string
     */
    private $fullImage;

    /**
     * @Vich\UploadableField(mapping="images", fileNameProperty="image")
     * @var File
     */
    private $fullImageFile;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $updatedAt = null;

    /**
     * @return File
     */
    public function getFullImageFile(): File
    {
        return $this->fullImageFile;
    }

    /**
     * @param File $fullImageFile
     * @return $this
     */
    public function setFullImageFile(File $fullImageFile = null): self
    {
        $this->fullImageFile = $fullImageFile;

        if ($fullImageFile) {
            $this->updatedAt = new \DateTime('now');
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFullImage(): ?string
    {
        return $this->fullImage;
    }

    /**
     * @param string $fullImage
     * @return UploadFileTrait
     */
    public function setFullImage(string $fullImage): UploadFileTrait
    {
        $this->fullImage = $fullImage;
        return $this;
    }
}