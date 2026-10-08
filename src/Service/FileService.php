<?php

namespace App\Service;

use App\Exception\InvalidImageException;
use Imagick;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileService implements FileServiceInterface
{
    private const int MEMORY_LIMIT_BYTES = 256 * 1024 * 1024;

    public function __construct(private readonly Filesystem $filesystem)
    {
    }

    /**
     * @param callable(Imagick): void $operation
     */
    public function process(UploadedFile $uploadedFile, callable $operation): string
    {
        $temp = tempnam(sys_get_temp_dir(), 'file_action_');

        try {
            $this->filesystem->dumpFile($temp, $uploadedFile->getContent());
            $this->assertDecodableWithinBudget($temp);

            Imagick::setResourceLimit(Imagick::RESOURCETYPE_MEMORY, self::MEMORY_LIMIT_BYTES);
            Imagick::setResourceLimit(Imagick::RESOURCETYPE_MAP, self::MEMORY_LIMIT_BYTES);
            Imagick::setResourceLimit(Imagick::RESOURCETYPE_AREA, PixelBudget::MAX_PIXELS);
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

            PixelBudget::assertWithin($probe->getImageWidth(), $probe->getImageHeight());
        } finally {
            $probe->clear();
        }
    }
}
