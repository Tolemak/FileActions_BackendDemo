<?php

namespace App\Service;

use App\Enum\ExtensionToConvert;
use App\Exception\ImageTooLargeException;
use App\Exception\InvalidImageException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileServiceInterface
{
    /**
     * @throws ImageTooLargeException
     * @throws InvalidImageException
     */
    public function resize(UploadedFile $uploadedFile, int $size): string;

    /**
     * @throws ImageTooLargeException
     * @throws InvalidImageException
     */
    public function changeExtension(UploadedFile $uploadedFile, ExtensionToConvert $extensionToConvert): string;

    /**
     * @throws ImageTooLargeException
     * @throws InvalidImageException
     */
    public function compress(UploadedFile $uploadedFile, int $ratio): string;

    /**
     * @throws ImageTooLargeException
     * @throws InvalidImageException
     */
    public function rotate(UploadedFile $uploadedFile, int $degrees): string;

    /**
     * @throws ImageTooLargeException
     * @throws InvalidImageException
     */
    public function applySepiaTone(UploadedFile $uploadedFile, int $intensity): string;
}
