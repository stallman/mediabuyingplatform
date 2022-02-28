<?php

namespace App\Service;

use App\Entity\EntityInterface;
use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Provider\File;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ImageProcessor
{
    const CROP_PREFIX = 'crop_';

    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;
    private UploadedFile $uploadedFile;
    private EntityInterface $entity;
    private Filesystem $filesystem;
    private Image $image;
    private string $publicDirPath;
    private string $filePath;
    private string $fileName;
    private string $pathToSave;
    private string $sectionName;
    private ?string $fileExtension;
    private ?array $cropParams;


    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag)
    {
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->filesystem = new Filesystem();
        $this->publicDirPath = $parameterBag->get('public_dir_path');
    }

    /**
     * @param Request $request
     * @param EntityInterface $formData
     * @return $this
     * @throws \Exception
     */
    public function checkFormImage(Request $request, EntityInterface $formData)
    {
        /** @var UploadedFile $image */
        $image = $request->files->get('image');

        if($request->request->get('cropParams')){
            if(!array_search('', $request->request->get('cropParams'))){
                $this->cropParams = $request->request->get('cropParams');
            }
        }

        if (isset($image) && !empty($image)) {
            $oldImage = $this->entityManager->getRepository(Image::class)
                ->getEntityImage($formData);

            if ($oldImage) {
                $this->deleteImage($formData);
            }

            $this->uploadImage($formData, $image);
            $this->uploadCropImage($formData);
        }

        if(is_null($image) && isset($this->cropParams)){
            $this->reCropImage($formData);
        }

        return $this;
    }


    public function validateImage(Request $request, EntityInterface $formData)
    {
        /** @var UploadedFile $image */
        $image = $request->files->get('image');

        if (isset($image) && !empty($image)) {
            if (!$this->isImageSizeValid($image)) {
                throw New \Exception("Превышен максимальный размер изображения");
            }
            if (!$this->isImageExtValid($image)) {
                throw New \Exception("Неподдерживаемый тип изображения");
            }
            if (!$this->isImageMaxPxSideSizeValid($image)) {
                throw New \Exception("Одна из сторон изображения слишком велика");
            }
        }
    }

    private function isImageSizeValid($image)
    {
        return $image->getSize() < $this->getEnvImageFileSize();
    }

    private function getEnvImageFileSize()
    {
        return $this->convertToBytes($_ENV['IMAGE_UPLOADER_MAX_SIZE']);
    }

    function convertToBytes($value) {
        $unit = mb_strtolower(mb_substr($value, -2, 1));
        return (int) $value * pow(1024, array_search($unit, array(1 =>'к','м','г')));
    }

    private function isImageExtValid($image)
    {
        $allowedExtensions = explode(',', $_ENV['IMAGE_UPLOADER_EXT']);
        return in_array($this->getImageExt($image), $allowedExtensions);
    }

    private function getImageExt($image)
    {
        return pathinfo($image->getClientOriginalName())['extension'];
    }

    private function isImageMaxPxSideSizeValid($image)
    {
        $maxSize = str_replace('px', "", mb_strtolower($_ENV['IMAGE_UPLOADER_MAX_PX_SIDE']));
        $width = getimagesize($image)[0];
        $height = getimagesize($image)[1];

        if ($maxSize) {
            return ($maxSize > $height && $maxSize > $width);
        }
        return true;
    }

    /**
     * @param EntityInterface $entity
     * @param UploadedFile $uploadedFile
     * @throws \Exception
     */
    public function uploadImage(EntityInterface $entity, UploadedFile $uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;
        $this->entity = $entity;

        return $this
            ->getSectionName($entity)
            ->processUploadedFile()
            ->saveUploadedFile()
            ;
    }

    public function copyImage(EntityInterface $sourceEntity, EntityInterface $targetEntity, Image $image)
    {

        $this->getSectionName($sourceEntity);
        $publicFolder = $this->parameterBag->get('public_dir_path');

        $sourceImage = $publicFolder . $image->getFullImagePath();
        $sourceCropImage = $publicFolder . $image->getFullCropImagePath();

        $imagesDir = $this->parameterBag->get($this->sectionName . '_images_dir_upload_path');

        $targetImageName = $this->md5FileNameHash(uniqid()) . '.' . $image->getExtension();
        $dirToSave = $this->getDestDirName($targetImageName);

        $this->pathToSave = $imagesDir . DIRECTORY_SEPARATOR . $dirToSave;
        $this->filePath = $this->parameterBag->get($this->sectionName . '_images_dir_asset_path') . DIRECTORY_SEPARATOR . $dirToSave;

        $targetImage = $this->pathToSave . DIRECTORY_SEPARATOR . $targetImageName;
        $targetCropImage = $this->pathToSave . DIRECTORY_SEPARATOR . self::CROP_PREFIX . $targetImageName;

        $this->checkDir($this->pathToSave);
        $this->filesystem->copy($sourceImage, $targetImage);
        $this->filesystem->copy($sourceCropImage, $targetCropImage);

        $copiedImage = new Image();

        $copiedImage
            ->setEntityFQN(get_class($targetEntity))
            ->setEntityId($targetEntity->getId())
            ->setFileName($targetImageName)
            ->setFilePath($this->filePath)
            ->setExtension($image->getExtension());

        $this->entityManager->persist($copiedImage);
        $this->entityManager->flush();

        return $this;
    }

    /**
     * @param EntityInterface $entity
     * @return $this
     * @throws \Exception
     */
    public function deleteImage(EntityInterface $entity)
    {
        $image = $this->entityManager->getRepository(Image::class)->getEntityImage($entity);

        if ($image) {
            try {
                $this->removeAllPreviews($image);
                $this->entityManager->remove($image);
                $this->entityManager->flush();
            } catch (\Exception $exception) {
                throw $exception;
            }
        }

        return $this;
    }

    public function removeAllPreviews($image)
    {
        foreach (glob($this->publicDirPath . $image->getFilePath() . "/*". $image->getFileName()) as $filename) {
            $this->filesystem->remove($filename);
        }
    }

    /**
     * @return $this
     */
    private function saveUploadedFile()
    {
        $this->uploadedFile->move($this->pathToSave, $this->fileName);
        $image = new Image();
        $image
            ->setEntityFQN(get_class($this->entity))
            ->setEntityId($this->entity->getId())
            ->setFileName($this->fileName)
            ->setFilePath($this->filePath)
            ->setExtension($this->fileExtension);
        $this->entityManager->persist($image);
        $this->entityManager->flush();

        return $this;
    }

    /**
     * @return $this
     */
    private function processUploadedFile()
    {
        $originalFilename = pathinfo($this->uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $originalFilenameMd5Hash = $this->md5FileNameHash($originalFilename);
        $dirToSave = $this->getDestDirName($originalFilenameMd5Hash);
        $originalFilenameMd5Hash = 'original_'.$originalFilenameMd5Hash;
        $imagesDir = $this->parameterBag->get($this->sectionName . '_images_dir_upload_path');

        $this->pathToSave = $imagesDir . DIRECTORY_SEPARATOR . $dirToSave;
        $this->filePath = $this->parameterBag->get($this->sectionName . '_images_dir_asset_path') . DIRECTORY_SEPARATOR . $dirToSave;
        $this->fileName = $originalFilenameMd5Hash . '.' . $this->uploadedFile->guessExtension();
        $this->fileExtension = $this->uploadedFile->guessExtension();

        $this->checkDir($this->pathToSave);

        return $this;
    }

    /**
     * @param EntityInterface $entity
     *
     * @return $this
     */
    private function getSectionName($entity)
    {
        $entityName = get_class($entity);
        $entityName = explode('\\', $entityName);
        $this->sectionName = strtolower($entityName[2]);

        return $this;
    }

    /**
     * Возвращает первые 2 символа из md5-хешированого названия файла
     *
     * @param string $fileNameHash
     * @return false|string
     */
    private function getDestDirName(string $fileNameHash)
    {
        return substr($fileNameHash, 0, 2);
    }

    /**
     * Возвращает название конечной папки нахождения изображения
     *
     * @param string $filePath
     * @return false|string
     */
    private function getImageFolderName(string $filePath)
    {
        return substr($filePath, -2);
    }

    /**
     * @param string $fileName
     * @return string
     */
    private function md5FileNameHash(string $fileName)
    {
        return md5($fileName.time());
    }

    /**
     * @param string $dirName
     * @return $this
     */
    private function checkDir(string $dirName)
    {
        if (!$this->filesystem->exists($dirName)) {
            $this->filesystem->mkdir($dirName);
        }

        return $this;
    }

    public function uploadCropImage($entity)
    {
        $originalImage = $this->entityManager->getRepository(Image::class)->getEntityImage($entity);
        $originalFilePath = $_SERVER['DOCUMENT_ROOT'].$originalImage->getFullImagePath();
        $cropImageName = self::CROP_PREFIX.$originalImage->getFileName();

        $cropImage = $this->cropImage($originalFilePath);
        $cropImage->writeImage($_SERVER['DOCUMENT_ROOT'].$originalImage->getFilePath() . '/' . $cropImageName);

        return $this;
    }

    private function cropImage($originalFilePath)
    {
        $imagick = new \Imagick($originalFilePath);
        if(isset($this->cropParams) && !empty($this->cropParams)){
            $imagick->cropImage($this->cropParams['width'], $this->cropParams['height'], $this->cropParams['coord_x'], $this->cropParams['coord_y']);
        }

        return $imagick;
    }

    private function reCropImage(EntityInterface $entity)
    {
        $this->moveOldImage($entity);
        $this->uploadCropImage($entity);

        return $this;
    }

    private function moveOldImage(EntityInterface $entity)
    {
        $originalImage = $this->entityManager->getRepository(Image::class)->getEntityImage($entity);

        $this->getSectionName($entity);
        $publicFolder = $this->parameterBag->get('public_dir_path');
        $sourceImage = $publicFolder . $originalImage->getFullImagePath();
        $imagesDir = $this->parameterBag->get($this->sectionName . '_images_dir_upload_path');
        $targetImageName = $this->md5FileNameHash(uniqid()) . '.' . $originalImage->getExtension();
        $dirToSave = $this->getDestDirName($targetImageName);
        $this->pathToSave = $imagesDir . DIRECTORY_SEPARATOR . $dirToSave;
        $this->filePath = $this->parameterBag->get($this->sectionName . '_images_dir_asset_path') . DIRECTORY_SEPARATOR . $dirToSave;
        $targetImage = $this->pathToSave . DIRECTORY_SEPARATOR . $targetImageName;
        $this->checkDir($this->pathToSave);
        $this->filesystem->copy($sourceImage, $targetImage);

        $this->deleteImage($entity);

        $originalImage
            ->setFileName($targetImageName)
            ->setFilePath($this->filePath);

        $this->entityManager->persist($originalImage);
        $this->entityManager->flush();

        return $this;
    }
}