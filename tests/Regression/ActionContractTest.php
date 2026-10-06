<?php

namespace App\Tests\Regression;

use Imagick;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ActionContractTest extends WebTestCase
{
    /** @var list<string> */
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            @unlink($file);
        }
        parent::tearDown();
    }

    private function upload(string $format = 'png', int $width = 100, int $height = 50, string $color = 'blue'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'fixture') . '.' . $format;
        $image = new Imagick();
        $image->newImage($width, $height, $color);
        $image->setImageFormat($format);
        $image->writeImage($path);
        $image->destroy();
        $this->tempFiles[] = $path;

        return new UploadedFile($path, 'sample.' . $format, 'image/' . $format, null, true);
    }

    private function post(KernelBrowser $client, string $path, UploadedFile $file): Imagick
    {
        $client->request('POST', $path, [], ['file' => $file]);
        $this->assertSame(200, $client->getResponse()->getStatusCode(), $path);

        $image = new Imagick();
        $image->readImageBlob($client->getInternalResponse()->getContent());

        return $image;
    }

    /**
     * @return iterable<string, array{string, string, int, int, string}>
     */
    public static function successfulRequests(): iterable
    {
        yield 'resize down' => ['/file/resize/50', 'png', 50, 25, 'PNG'];
        yield 'resize minimum' => ['/file/resize/10', 'png', 10, 5, 'PNG'];
        yield 'resize up' => ['/file/resize/200', 'png', 200, 100, 'PNG'];
        yield 'resize maximum' => ['/file/resize/300', 'png', 300, 150, 'PNG'];
        yield 'convert to jpeg' => ['/file/convert/jpeg', 'png', 100, 50, 'JPEG'];
        yield 'convert to png' => ['/file/convert/png', 'jpeg', 100, 50, 'PNG'];
        yield 'convert to gif' => ['/file/convert/gif', 'png', 100, 50, 'GIF'];
        yield 'compress' => ['/file/compress/30', 'jpeg', 100, 50, 'JPEG'];
        yield 'compress minimum' => ['/file/compress/1', 'jpeg', 100, 50, 'JPEG'];
        yield 'compress maximum' => ['/file/compress/100', 'jpeg', 100, 50, 'JPEG'];
        yield 'rotate quarter turn' => ['/file/rotate/90', 'png', 50, 100, 'PNG'];
        yield 'rotate zero' => ['/file/rotate/0', 'png', 100, 50, 'PNG'];
        yield 'rotate full circle' => ['/file/rotate/360', 'png', 100, 50, 'PNG'];
        yield 'sepia' => ['/file/sepia/80', 'png', 100, 50, 'PNG'];
        yield 'sepia minimum' => ['/file/sepia/1', 'png', 100, 50, 'PNG'];
        yield 'sepia maximum' => ['/file/sepia/100', 'png', 100, 50, 'PNG'];
    }

    /**
     * @dataProvider successfulRequests
     */
    public function testActionOutputFormatAndDimensions(string $path, string $sourceFormat, int $width, int $height, string $format): void
    {
        $client = static::createClient();
        $result = $this->post($client, $path, $this->upload($sourceFormat));

        $this->assertSame($width, $result->getImageWidth());
        $this->assertSame($height, $result->getImageHeight());
        $this->assertSame($format, $result->getImageFormat());
    }

    public function testCompressAppliesTheRequestedQuality(): void
    {
        $client = static::createClient();
        $result = $this->post($client, '/file/compress/30', $this->upload('jpeg'));

        $this->assertSame(30, $result->getImageCompressionQuality());
    }

    public function testSepiaRewritesPureBlueToCyan(): void
    {
        $client = static::createClient();
        $result = $this->post($client, '/file/sepia/80', $this->upload('png', 10, 10, 'blue'));

        $pixel = $result->getImagePixelColor(0, 0)->getColor();
        $this->assertSame([0, 255, 255], [$pixel['r'], $pixel['g'], $pixel['b']]);
    }

    public function testRotateFillsCornersWithWhite(): void
    {
        $client = static::createClient();
        $result = $this->post($client, '/file/rotate/45', $this->upload('png', 40, 40));

        $corner = $result->getImagePixelColor(0, 0)->getColor();
        $this->assertSame(255, $corner['r']);
        $this->assertSame(255, $corner['g']);
        $this->assertSame(255, $corner['b']);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function rejectedValues(): iterable
    {
        yield 'resize below' => ['/file/resize/9', 'Size must be between 10 and 300'];
        yield 'resize above' => ['/file/resize/301', 'Size must be between 10 and 300'];
        yield 'convert unknown' => ['/file/convert/bmp', 'Extension must be jpeg, png or gif'];
        yield 'convert uppercase' => ['/file/convert/PNG', 'Extension must be jpeg, png or gif'];
        yield 'compress below' => ['/file/compress/0', 'Compress ratio must be between 1 and 100'];
        yield 'compress above' => ['/file/compress/101', 'Compress ratio must be between 1 and 100'];
        yield 'rotate above' => ['/file/rotate/361', 'Degrees must be between 0 and 360'];
        yield 'sepia below' => ['/file/sepia/0', 'Intensity must be between 1 and 100'];
        yield 'sepia above' => ['/file/sepia/101', 'Intensity must be between 1 and 100'];
    }

    /**
     * @dataProvider rejectedValues
     */
    public function testOutOfRangeValueIsRejectedWithAMessage(string $path, string $message): void
    {
        $client = static::createClient();
        $client->request('POST', $path, [], ['file' => $this->upload()]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString($message, (string) $client->getResponse()->getContent());
    }

    public function testMissingFileIsReportedBeforeAnInvalidValue(): void
    {
        $client = static::createClient();
        $client->request('POST', '/file/resize/9999');

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('File not found', (string) $client->getResponse()->getContent());
    }

    public function testOversizedUploadIsRejected(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'fixture') . '.png';
        $image = new Imagick();
        $image->newImage(1, 1, 'blue');
        $image->setImageFormat('png');
        $image->writeImage($path);
        file_put_contents($path, str_repeat("\0", 5_000_001), FILE_APPEND);
        $this->tempFiles[] = $path;

        $client = static::createClient();
        $client->request('POST', '/file/rotate/90', [], ['file' => new UploadedFile($path, 'big.png', 'image/png', null, true)]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('File size must be less than 5MB', (string) $client->getResponse()->getContent());
    }

    public function testImageOverThePixelBudgetIsRejected(): void
    {
        $client = static::createClient();
        $client->request('POST', '/file/resize/300', [], ['file' => $this->upload('png', 3000, 3000)]);

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Image dimensions are too large to process', (string) $client->getResponse()->getContent());
    }

    /**
     * @return iterable<string, array{string, string, int}>
     */
    public static function routeShapes(): iterable
    {
        yield 'get on an action' => ['GET', '/file/resize/50', 405];
        yield 'non numeric range value' => ['POST', '/file/resize/abc', 404];
        yield 'negative range value' => ['POST', '/file/rotate/-5', 404];
        yield 'decimal range value' => ['POST', '/file/sepia/1.5', 404];
        yield 'missing value' => ['POST', '/file/resize', 404];
        yield 'unknown action' => ['POST', '/file/blur/5', 404];
        yield 'unknown view' => ['GET', '/file-view/blur', 404];
    }

    /**
     * @dataProvider routeShapes
     */
    public function testRouteShape(string $method, string $path, int $status): void
    {
        $client = static::createClient();
        $client->request($method, $path);

        $this->assertSame($status, $client->getResponse()->getStatusCode());
    }

    /**
     * @return iterable<string, array{string, string, string, string}>
     */
    public static function pages(): iterable
    {
        yield 'resize' => ['resize', 'Resize an image', 'Resize', 'Resize'];
        yield 'convert' => ['convert', 'Change the image format', 'Convert', 'Convert'];
        yield 'compress' => ['compress', 'Compress an image', 'Compress', 'Compress'];
        yield 'rotate' => ['rotate', 'Rotate an image', 'Rotate', 'Rotate'];
        yield 'sepia' => ['sepia', 'Apply a sepia filter', 'Apply sepia', 'Sepia filter'];
    }

    /**
     * @dataProvider pages
     */
    public function testPageFormFields(string $name, string $header, string $button, string $title): void
    {
        $client = static::createClient();
        $client->request('GET', '/file-view/' . $name);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextSame('h1', $header);
        $this->assertSelectorTextContains('title', $title . ' · File Actions');
        $this->assertSelectorCount(1, 'input.filepond[type=file][name=filepond][accept="image/png, image/jpeg, image/gif"]');
        $this->assertSelectorTextSame('button.button-process', $button);
        $this->assertSelectorCount(1, 'nav.tools a[aria-current=page][href="/file-view/' . $name . '"]');
        $this->assertSelectorCount(1, 'nav.tools a[aria-current]');
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function rangePrompts(): iterable
    {
        yield 'resize' => ['resize', 'New size in % of the original'];
        yield 'compress' => ['compress', 'Compression level (1–100)'];
        yield 'rotate' => ['rotate', 'Rotation angle in degrees'];
        yield 'sepia' => ['sepia', 'Sepia intensity (1–100)'];
    }

    /**
     * @dataProvider rangePrompts
     */
    public function testRangeActionsCarryAPromptTitleAndNoSelect(string $name, string $prompt): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/file-view/' . $name);

        $this->assertSame($prompt, $crawler->filter('input.filepond')->attr('data-prompt-title'));
        $this->assertSelectorNotExists('.sheet-controls select');
    }

    public function testConvertPageOffersTheThreeTargetFormats(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/file-view/convert');

        $options = $crawler->filter('.sheet-controls select option');
        $this->assertSame(['jpeg', 'png', 'gif'], $options->each(static fn ($o): string => (string) $o->attr('value')));
        $this->assertSame(['JPEG', 'PNG', 'GIF'], $options->each(static fn ($o): string => $o->text()));
        $this->assertSelectorTextSame('.sheet-controls label', 'Target format');
    }

    public function testNavigationListsTheFiveToolsFirstAndInOrder(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/file-view/main');

        $this->assertSame(
            ['/file-view/resize', '/file-view/convert', '/file-view/compress', '/file-view/rotate', '/file-view/sepia'],
            array_slice($crawler->filter('nav.tools a')->each(static fn ($a): string => (string) $a->attr('href')), 0, 5),
        );
        $this->assertSame(
            ['Resize', 'Convert', 'Compress', 'Rotate', 'Sepia'],
            array_slice($crawler->filter('nav.tools a')->each(static fn ($a): string => $a->text()), 0, 5),
        );
    }

    public function testHomeCardsShowSpecAndLinkToEachTool(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/file-view/main');

        $this->assertSame(
            ['10–300 %', 'JPG / PNG / GIF', '1–100', '0–360°', '1–100'],
            array_slice($crawler->filter('.paper-spec')->each(static fn ($n): string => $n->text()), 0, 5),
        );
        $this->assertSame(
            ['/file-view/resize', '/file-view/convert', '/file-view/compress', '/file-view/rotate', '/file-view/sepia'],
            array_slice($crawler->filter('.paper a')->each(static fn ($a): string => (string) $a->attr('href')), 0, 5),
        );
    }

    public function testPolishPageTexts(): void
    {
        $client = static::createClient();
        $client->request('GET', '/file-view/rotate?_locale=pl');

        $this->assertSelectorTextSame('h1', 'Obróć obraz');
        $this->assertSelectorTextSame('button.button-process', 'Obróć');
    }
}
