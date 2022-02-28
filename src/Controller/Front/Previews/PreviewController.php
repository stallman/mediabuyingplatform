<?php


namespace App\Controller\Front\Previews;

use App\Controller\Front\FrontController;
use App\Entity\CropVariant;
use App\Entity\Image;
use Symfony\Component\HttpFoundation\{Response};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PreviewController extends FrontController
{
    /**
     * @Route("/previews/{parent_folder}/{folder}/{filename}", name="front.get_preview")
     * @param string $parent_folder
     * @param string $folder
     * @param string $filename
     * @return Response
     */
    public function getPreview(string $parent_folder, string $folder, string $filename)
    {
        $filesystem = new Filesystem();

        try {
            $imageParams = $this->explodePreviewFilename($filename);
        } catch (Exception $exception) {
            return Response::create($exception->getMessage());
        }

        $fullpath = $this->getFullPath($parent_folder, $folder, $imageParams['name']);

        if ($filesystem->exists($fullpath)) {
            $croppedImage = $this->createNewCroppedImage($fullpath, $imageParams, $parent_folder);
            return $this->showImage($croppedImage->filename, $croppedImage->filepath . '/' . $croppedImage->filename);
        }
        return Response::create('Изображение не существует');
    }

    private function showImage($filename, $filepath)
    {
        $response = new Response();
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'image/png');
        $response->setContent(file_get_contents($filepath));
        return $response;
    }

    public function getFullPath($parentFolder, $folder, $name)
    {
        return $_ENV['IMAGES_PATH'] . "/" . $parentFolder . "/" . $folder . "/crop_" . $name;
    }

    private function explodePreviewFilename($filename)
    {
        preg_match('/(.+?)x(.+?)_(.*)/i', $filename, $matches);

        foreach ($matches as $match) {
            if (empty($match)) {
                throw new Exception('Неверный формат названия файла');
            }
        }

        return [
            'width' => $matches[1],
            'height' => $matches[2],
            'name' => $matches[3],
        ];
    }

    private function createNewCroppedImage($fullpath, $imageParams, $parentFolder)
    {
        $image = new \Imagick($fullpath);
        $image =  $this->cropIdentify($image, $imageParams);

        return $this->createAndSaveCroppedImage($image, $imageParams, $parentFolder);
    }



    private function cropIdentify($image, $cropParams)
    {
        $imageOrientation = $this->getOrientation($image->getImageHeight(), $image->getImageWidth());
        $cropOrientation = $this->getOrientation($cropParams['height'], $cropParams['width']);

        if (
            ($cropOrientation == 'horisontal' && $imageOrientation == 'horisontal')
            ||
            ($cropOrientation == 'horisontal' && $imageOrientation == 'vertical')
            ||
            ($cropOrientation == 'square' && $imageOrientation == 'vertical')
            ||
            ($cropOrientation == 'horisontal' && $imageOrientation == 'square')
        ) {
            $this->cropAltorithmOne($image, $cropParams);
        }

        if (
            ($cropOrientation == 'vertical' && $imageOrientation == 'horisontal')
            ||
            ($cropOrientation == 'vertical' && $imageOrientation == 'vertical')
            ||
            ($cropOrientation == 'square' && $imageOrientation == 'horisontal')
        ) {
            $this->cropAltorithmTwo($image, $cropParams);
        }

        return $image;
    }

    //Сохраняет ширину оригинального изображения, обрезает снизу
    private function cropAltorithmOne($image, $cropParams)
    {
        $proportionalCoeff = $image->getImageWidth() / $cropParams['width'];
        $newHeight = $cropParams['height'] * $proportionalCoeff;
        $newWidth = $image->getImageWidth();
        $image->cropImage( $newWidth, $newHeight, 0, 0);
        $image->resizeImage($cropParams['width'], $cropParams['height'], \Imagick::FILTER_LANCZOS, 1);
        return $image;
    }

    //Сохраняет высоту оригинального изображения, обрезает по бокам
    private function cropAltorithmTwo($image, $cropParams)
    {
        $proportionalCoeff = $image->getImageHeight() / $cropParams['height'];
        $newWidth = $cropParams['width'] * $proportionalCoeff;
        $offsets = abs($image->getImageWidth() - $newWidth);
        $leftOffset = $offsets / 2;

        $image->cropImage( $newWidth, $image->getImageHeight(), $leftOffset, 0);
        $image->resizeImage($cropParams['width'], $cropParams['height'], \Imagick::FILTER_LANCZOS, 1);
        return $image;
    }

    private function getOrientation($heigth, $width)
    {
        if ($heigth > $width) {
            return 'vertical';
        } elseif ($heigth < $width) {
            return 'horisontal';
        } else {
            return 'square';
        }
    }

    private function createAndSaveCroppedImage($image, $imageParams, $parentFolder)
    {
        $filesystem = new Filesystem();
        $croppedImagePath =  $_ENV['IMAGES_PATH'] . '/' . $parentFolder . "/" .  $this->generatePreviewFolderName( $imageParams['name']);

        $filesystem->mkdir($croppedImagePath);
        $image->writeImage($croppedImagePath . '/' . $this->generatePreviewFileName($imageParams));
        $image->filename = $this->generatePreviewFileName($imageParams);
        $image->filepath = $croppedImagePath;
        return $image;
    }

    private function generatePreviewFolderName($filename)
    {
        $filename = str_replace("crop_", "", $filename);
        return substr($filename, 0, 2);
    }

    private function generatePreviewFileName($imageParams)
    {
        return $imageParams['width'] . 'x' . $imageParams['height'] . '_' . $imageParams['name'];
    }

}