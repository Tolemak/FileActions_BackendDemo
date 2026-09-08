<?php

namespace App\Tests\Service;

use App\Enum\ExtensionToConvert;
use App\Service\FileService;
use Imagick;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileServiceTest extends TestCase
{
    private FileService $fileService;
    private array $tempFiles = [];

    protected function setUp(): void
    {
        $this->fileService = new FileService(new Filesystem());
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            @unlink($file);
        }
    }

    private function createUploadedImage(string $extension = 'png', int $width = 20, int $height = 10, string $color = 'blue'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'fixture') . '.' . $extension;
        $image = new Imagick();
        $image->newImage($width, $height, $color);
        $image->setImageFormat($extension);
        $image->writeImage($path);
        $image->destroy();

        $this->tempFiles[] = $path;

        return new UploadedFile($path, 'sample.' . $extension, 'image/' . $extension, null, true);
    }

    public function testResizeScalesImageDimensions(): void
    {
        $file = $this->createUploadedImage(width: 100, height: 50);

        $resultPath = $this->fileService->resize($file, 50);
        $this->tempFiles[] = $resultPath;

        $result = new Imagick($resultPath);
        $this->assertSame(50, $result->getImageWidth());
        $this->assertSame(25, $result->getImageHeight());
    }

    public function testChangeExtensionConvertsFormat(): void
    {
        $file = $this->createUploadedImage('png');

        $resultPath = $this->fileService->changeExtension($file, ExtensionToConvert::JPEG);
        $this->tempFiles[] = $resultPath;

        $result = new Imagick($resultPath);
        $this->assertSame('JPEG', $result->getImageFormat());
    }

    public function testCompressSetsCompressionQuality(): void
    {
        $file = $this->createUploadedImage('jpeg');

        $resultPath = $this->fileService->compress($file, 30);
        $this->tempFiles[] = $resultPath;

        $result = new Imagick($resultPath);
        $this->assertSame(30, $result->getImageCompressionQuality());
    }

    public function testRotateSwapsDimensionsOnNinetyDegrees(): void
    {
        $file = $this->createUploadedImage(width: 40, height: 20);

        $resultPath = $this->fileService->rotate($file, 90);
        $this->tempFiles[] = $resultPath;

        $result = new Imagick($resultPath);
        $this->assertSame(20, $result->getImageWidth());
        $this->assertSame(40, $result->getImageHeight());
    }

    public function testRotateByFullCircleKeepsOriginalDimensions(): void
    {
        $file = $this->createUploadedImage(width: 40, height: 20);

        $resultPath = $this->fileService->rotate($file, 360);
        $this->tempFiles[] = $resultPath;

        $result = new Imagick($resultPath);
        $this->assertSame(40, $result->getImageWidth());
        $this->assertSame(20, $result->getImageHeight());
    }

    public function testApplySepiaToneChangesPixelColors(): void
    {
        $file = $this->createUploadedImage(width: 10, height: 10, color: 'blue');

        $resultPath = $this->fileService->applySepiaTone($file, 80);
        $this->tempFiles[] = $resultPath;

        $result = new Imagick($resultPath);
        $pixel = $result->getImagePixelColor(0, 0)->getColor();
        $this->assertNotSame(['r' => 0, 'g' => 0, 'b' => 255, 'a' => 1], $pixel);
    }
}
