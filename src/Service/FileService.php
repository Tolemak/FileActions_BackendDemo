<?php

namespace App\Service;

use App\Enum\ExtensionToConvert;
use App\Exception\ImageTooLargeException;
use App\Exception\InvalidImageException;
use Imagick;
use ImagickPixel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileService implements FileServiceInterface
{
    /**
     * A 5 MB upload can still decode to hundreds of megapixels, so the byte
     * limit alone does not bound memory. Dimensions are read from the header
     * before decoding and checked against this. At Q16 a pixel costs 8 bytes,
     * so this has to stay comfortably under MEMORY_LIMIT_BYTES.
     */
    private const int MAX_PIXELS = 24_000_000;

    private const int MEMORY_LIMIT_BYTES = 256 * 1024 * 1024;

    public function __construct(private readonly Filesystem $filesystem)
    {
    }

    public function resize(UploadedFile $uploadedFile, int $size): string
    {
        $factor = $size / 100;

        return $this->process($uploadedFile, function (Imagick $image) use ($factor): void {
            $width = (int) ($image->getImageWidth() * $factor);
            $height = (int) ($image->getImageHeight() * $factor);
            $this->assertWithinPixelBudget($width, $height);

            $image->scaleImage($width, $height);
        });
    }

    public function changeExtension(UploadedFile $uploadedFile, ExtensionToConvert $extensionToConvert): string
    {
        return $this->process($uploadedFile, static function (Imagick $image) use ($extensionToConvert): void {
            $image->setImageFormat($extensionToConvert->value);
        });
    }

    public function compress(UploadedFile $uploadedFile, int $ratio): string
    {
        return $this->process($uploadedFile, static function (Imagick $image) use ($ratio): void {
            $image->setImageCompressionQuality($ratio);
        });
    }

    public function rotate(UploadedFile $uploadedFile, int $degrees): string
    {
        return $this->process($uploadedFile, static function (Imagick $image) use ($degrees): void {
            $image->rotateImage(new ImagickPixel('white'), $degrees);
        });
    }

    public function applySepiaTone(UploadedFile $uploadedFile, int $intensity): string
    {
        return $this->process($uploadedFile, static function (Imagick $image) use ($intensity): void {
            $image->sepiaToneImage($intensity / 100 * $image->getQuantumRange()['quantumRangeLong']);
        });
    }

    /**
     * @param callable(Imagick): void $operation
     */
    private function process(UploadedFile $uploadedFile, callable $operation): string
    {
        $temp = tempnam(sys_get_temp_dir(), 'file_action_');

        try {
            $this->filesystem->dumpFile($temp, $uploadedFile->getContent());
            $this->assertDecodableWithinBudget($temp);

            Imagick::setResourceLimit(Imagick::RESOURCETYPE_MEMORY, self::MEMORY_LIMIT_BYTES);
            Imagick::setResourceLimit(Imagick::RESOURCETYPE_MAP, self::MEMORY_LIMIT_BYTES);
            Imagick::setResourceLimit(Imagick::RESOURCETYPE_AREA, self::MAX_PIXELS);
            Imagick::setResourceLimit(Imagick::RESOURCETYPE_DISK, 0);

            $image = new Imagick();

            try {
                try {
                    $image->readImage($temp);
                } catch (\ImagickException $e) {
                    throw new InvalidImageException('Image could not be decoded.', previous: $e);
                }

                $operation($image);
                $image->writeImage($temp);
            } finally {
                $image->clear();
            }

            return $temp;
        } catch (\Throwable $e) {
            $this->filesystem->remove($temp);

            throw $e;
        }
    }

    private function assertDecodableWithinBudget(string $path): void
    {
        $probe = new Imagick();

        try {
            try {
                $probe->pingImage($path);
            } catch (\ImagickException $e) {
                throw new InvalidImageException('Image header could not be read.', previous: $e);
            }

            $this->assertWithinPixelBudget($probe->getImageWidth(), $probe->getImageHeight());
        } finally {
            $probe->clear();
        }
    }

    private function assertWithinPixelBudget(int $width, int $height): void
    {
        if ($width * $height > self::MAX_PIXELS) {
            throw new ImageTooLargeException('Image exceeds the supported pixel budget.');
        }
    }
}
