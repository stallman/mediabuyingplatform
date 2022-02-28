<?php

namespace App\Traits;

use App\Entity\EntityInterface;
use App\Entity\Image;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

trait DefaultImageTrait
{
    public $fileName;
    public $filePath;
    public $extension;

    public function getDefaultImage($imagePath = null)
    {
        if (!$imagePath)
        {
            $imagePath = '/assets/' . $this->request->cookies->get('design') . '/static/images/default.jpg';
        }

        $this->explodeImagePath($imagePath);

        $image = new Image();
        $image->setFilePath($this->filePath);
        $image->setFileName($this->fileName);
        $image->setExtension($this->extension);

        return $image;
    }

    private function explodeImagePath($fullPath)
    {
        $pathArr = explode('/', $fullPath);
        $this->fileName = $pathArr[array_key_last($pathArr)];
        $this->filePath = str_replace($this->fileName, '', $fullPath);
        $this->extension = explode('.', $this->fileName)[0];
    }
}