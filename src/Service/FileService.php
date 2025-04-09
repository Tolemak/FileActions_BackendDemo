<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileService
{
    function __construct()
    {
        // Constructor code here
    }

    function resize(UploadedFile $uploadedFile, int $size) {}
}
