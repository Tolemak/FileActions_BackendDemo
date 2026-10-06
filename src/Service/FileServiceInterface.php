<?php

namespace App\Service;

use App\Exception\ImageTooLargeException;
use App\Exception\InvalidImageException;
use Imagick;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileServiceInterface
{
    /**
     * @param callable(Imagick): void $operation
     *
     * @throws ImageTooLargeException
     * @throws InvalidImageException
     */
    public function process(UploadedFile $uploadedFile, callable $operation): string;
}
