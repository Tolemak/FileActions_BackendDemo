<?php

namespace App\Service;

use Imagick;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileService
{
    function __construct(private readonly Filesystem $filesystem)
    {
        // Constructor code here
    }

    function resize(UploadedFile $uploadedFile, int $size): string
    {
        $sizePercentage = $size / 100;
        $temp = tempnam(sys_get_temp_dir(), $uploadedFile->getClientOriginalName());
        $this->filesystem->dumpFile($temp, $uploadedFile->getContent());
        $image = new Imagick($temp);
        $image->scaleImage($image->getImageWidth() * $sizePercentage, $image->getImageHeight() * $sizePercentage);
        $image->setImageFormat($uploadedFile->getClientOriginalExtension());
        $image->writeImage($temp);
        return $temp;
    }
}
