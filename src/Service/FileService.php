<?php

namespace App\Service;

use App\Enum\ExtensionToConvert;
use Imagick;
use ImagickPixel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileService
{
    public function __construct(private readonly Filesystem $filesystem)
    {
    }

    public function resize(UploadedFile $uploadedFile, int $size): string
    {
        $sizePercentage = $size / 100;
        $temp = tempnam(sys_get_temp_dir(), $uploadedFile->getClientOriginalName());
        $this->filesystem->dumpFile($temp, $uploadedFile->getContent());
        $image = new Imagick($temp);
        $image->scaleImage(
            (int) ($image->getImageWidth() * $sizePercentage),
            (int) ($image->getImageHeight() * $sizePercentage)
        );
        $image->setImageFormat($uploadedFile->getClientOriginalExtension());
        $image->writeImage($temp);
        $image->destroy();

        return $temp;
    }

    public function changeExtension(UploadedFile $uploadedFile, ExtensionToConvert $extensionToConvert): string
    {
        $temp = tempnam(sys_get_temp_dir(), $extensionToConvert->value);
        $this->filesystem->dumpFile($temp, $uploadedFile->getContent());
        $image = new Imagick($temp);
        $image->setImageFormat($extensionToConvert->value);
        $image->writeImage($temp);
        $image->destroy();

        return $temp;
    }

    public function compress(UploadedFile $uploadedFile, int $ratio): string
    {
        $temp = tempnam(sys_get_temp_dir(), $uploadedFile->getClientOriginalName());
        $this->filesystem->dumpFile($temp, $uploadedFile->getContent());
        $image = new Imagick($temp);
        $image->setImageCompressionQuality($ratio);
        $image->setImageFormat($uploadedFile->getClientOriginalExtension());
        $image->writeImage($temp);
        $image->destroy();

        return $temp;
    }

    public function rotate(UploadedFile $uploadedFile, int $degrees): string
    {
        $temp = tempnam(sys_get_temp_dir(), $uploadedFile->getClientOriginalName());
        $this->filesystem->dumpFile($temp, $uploadedFile->getContent());
        $image = new Imagick($temp);
        $image->rotateImage(new ImagickPixel('white'), $degrees);
        $image->setImageFormat($uploadedFile->getClientOriginalExtension());
        $image->writeImage($temp);
        $image->destroy();

        return $temp;
    }

    public function applySepiaTone(UploadedFile $uploadedFile, int $intensity): string
    {
        $temp = tempnam(sys_get_temp_dir(), $uploadedFile->getClientOriginalName());
        $this->filesystem->dumpFile($temp, $uploadedFile->getContent());
        $image = new Imagick($temp);
        $image->sepiaToneImage($intensity / 100 * $image->getQuantumRange()['quantumRangeLong']);
        $image->setImageFormat($uploadedFile->getClientOriginalExtension());
        $image->writeImage($temp);
        $image->destroy();

        return $temp;
    }
}
