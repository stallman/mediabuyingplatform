<?php

namespace App\DataFixtures;

use App\Entity\Image;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use Symfony\Component\Filesystem\Filesystem;


abstract class FakeImagesFixtures extends Fixture
{
    public function saveImages($entity, $entityName)
    {
        $imagePath = $this->getRandomImagePath();
        $this->saveImage($entity, $entityName, $imagePath);
    }

    private function saveImage($entity, $entityName, $imagePath)
    {
        $newImageName = md5(uniqid().basename($imagePath).time()).'.'.$this->getExtention($imagePath);
        $previewFolderName = $this->generatePreviewFolderName($newImageName);
        $newImagePath = $_ENV['IMAGES_PATH'] . "/" . $entityName . "/". $previewFolderName;
        $this->filesystem->copy($imagePath, $newImagePath . "/" . $newImageName);
        $this->filesystem->copy($imagePath, $newImagePath . "/crop_" . $newImageName);

        $image = new Image();
        $image->setEntityId($entity->getId());
        $image->setEntityFQN(get_class($entity));
        $image->setFileName($newImageName);
        $image->setFilePath("/uploads/images/" . $entityName . "/". $previewFolderName);
        $image->setExtension($this->getExtention($imagePath));

        $this->entityManager->persist($image);
        $this->entityManager->flush();
    }

    private function getExtention($filename)
    {
        $exploded = explode('.', $filename);
        return array_pop($exploded);
    }

    private function generatePreviewFolderName($filename)
    {
        return substr($filename, 0, 2);
    }

    function getRandomImagePath()
    {
        $files = glob($_ENV['SOURCE_IMAGES_PATH'] . '/*.*');
        $file = array_rand($files);
        return $files[$file];
    }
}