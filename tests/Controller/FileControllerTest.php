<?php

namespace App\Tests\Controller;

use Imagick;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileControllerTest extends WebTestCase
{
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            @unlink($file);
        }
        parent::tearDown();
    }

    private function createUploadedImage(string $extension = 'png', int $width = 20, int $height = 10): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'fixture') . '.' . $extension;
        $image = new Imagick();
        $image->newImage($width, $height, 'blue');
        $image->setImageFormat($extension);
        $image->writeImage($path);
        $image->destroy();

        $this->tempFiles[] = $path;

        return new UploadedFile($path, 'sample.' . $extension, 'image/' . $extension, null, true);
    }

    public function testResizeRejectsMissingFile(): void
    {
        $client = static::createClient();
        $client->request('POST', '/file/resize/50');

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('File not found', $client->getResponse()->getContent());
    }

    public function testResizeRejectsUnsupportedMimeType(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'fixture') . '.txt';
        file_put_contents($path, 'not an image');
        $this->tempFiles[] = $path;
        $file = new UploadedFile($path, 'sample.txt', 'text/plain', null, true);

        $client = static::createClient();
        $client->request('POST', '/file/resize/50', [], ['file' => $file]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('must be a JPEG, PNG or GIF', $client->getResponse()->getContent());
    }

    public function testResizeRejectsFileDisguisedAsImage(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'fixture') . '.png';
        file_put_contents($path, '<svg xmlns="http://www.w3.org/2000/svg"><image href="/etc/passwd"/></svg>');
        $this->tempFiles[] = $path;
        $file = new UploadedFile($path, 'sample.png', 'image/png', null, true);

        $client = static::createClient();
        $client->request('POST', '/file/resize/50', [], ['file' => $file]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('must be a JPEG, PNG or GIF', $client->getResponse()->getContent());
    }

    public function testResizeRejectsUndecodableImageAsClientError(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'fixture') . '.png';
        file_put_contents($path, $this->createPngHeaderOnly(64, 64));
        $this->tempFiles[] = $path;
        $file = new UploadedFile($path, 'sample.png', 'image/png', null, true);

        $client = static::createClient();
        $client->request('POST', '/file/resize/50', [], ['file' => $file]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('must be a JPEG, PNG or GIF', $client->getResponse()->getContent());
    }

    /**
     * Passes the magic-byte sniff but carries no pixel data, so it exercises the
     * path where Imagick itself refuses the file.
     */
    private function createPngHeaderOnly(int $width, int $height): string
    {
        $chunk = static function (string $type, string $data): string {
            return pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data));
        };

        $ihdr = pack('NN', $width, $height) . pack('CCCCC', 8, 2, 0, 0, 0);

        return "\x89PNG\r\n\x1a\n" . $chunk('IHDR', $ihdr) . $chunk('IEND', '');
    }

    public function testResizeRejectsSizeOutsideAllowedRange(): void
    {
        $file = $this->createUploadedImage();

        $client = static::createClient();
        $client->request('POST', '/file/resize/500', [], ['file' => $file]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Size must be between 10 and 300', $client->getResponse()->getContent());
    }

    public function testResizeReturnsResizedImage(): void
    {
        $file = $this->createUploadedImage(width: 100, height: 50);

        $client = static::createClient();
        $client->request('POST', '/file/resize/50', [], ['file' => $file]);

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertSame('image/png', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('sample.png', $response->headers->get('Content-Disposition'));
    }

    public function testConvertRejectsUnknownExtension(): void
    {
        $file = $this->createUploadedImage();

        $client = static::createClient();
        $client->request('POST', '/file/convert/bmp', [], ['file' => $file]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Extension must be jpeg, png or gif', $client->getResponse()->getContent());
    }

    public function testConvertReturnsConvertedImage(): void
    {
        $file = $this->createUploadedImage('png');

        $client = static::createClient();
        $client->request('POST', '/file/convert/jpeg', [], ['file' => $file]);

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('image/jpeg', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('sample.jpeg', $response->headers->get('Content-Disposition'));
    }

    public function testCompressRejectsRatioOutsideAllowedRange(): void
    {
        $file = $this->createUploadedImage();

        $client = static::createClient();
        $client->request('POST', '/file/compress/0', [], ['file' => $file]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Compress ratio must be between 1 and 100', $client->getResponse()->getContent());
    }

    public function testCompressReturnsCompressedImage(): void
    {
        $file = $this->createUploadedImage('jpeg');

        $client = static::createClient();
        $client->request('POST', '/file/compress/40', [], ['file' => $file]);

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('image/jpeg', $response->headers->get('Content-Type'));
    }

    public function testRotateRejectsDegreesOutsideAllowedRange(): void
    {
        $file = $this->createUploadedImage();

        $client = static::createClient();
        $client->request('POST', '/file/rotate/500', [], ['file' => $file]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Degrees must be between 0 and 360', $client->getResponse()->getContent());
    }

    public function testRotateReturnsRotatedImage(): void
    {
        $file = $this->createUploadedImage(width: 40, height: 20);

        $client = static::createClient();
        $client->request('POST', '/file/rotate/90', [], ['file' => $file]);

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('sample.png', $response->headers->get('Content-Disposition'));
    }

    public function testSepiaRejectsIntensityOutsideAllowedRange(): void
    {
        $file = $this->createUploadedImage();

        $client = static::createClient();
        $client->request('POST', '/file/sepia/0', [], ['file' => $file]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Intensity must be between 1 and 100', $client->getResponse()->getContent());
    }

    public function testSepiaReturnsProcessedImage(): void
    {
        $file = $this->createUploadedImage();

        $client = static::createClient();
        $client->request('POST', '/file/sepia/80', [], ['file' => $file]);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }
}
