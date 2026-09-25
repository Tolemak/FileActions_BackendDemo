<?php

namespace App\Tests\Http;

use App\Http\DownloadFilename;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderUtils;

class DownloadFilenameTest extends TestCase
{
    public function testReplacesExtension(): void
    {
        $filename = DownloadFilename::fromClientName('photo.png', 'jpg');

        $this->assertSame('photo.jpg', $filename->name);
        $this->assertSame('photo.jpg', $filename->asciiFallback);
    }

    public function testKeepsInnerDots(): void
    {
        $this->assertSame('my.holiday.photo.gif', DownloadFilename::fromClientName('my.holiday.photo.jpeg', 'gif')->name);
    }

    public function testAddsExtensionWhenMissing(): void
    {
        $this->assertSame('photo.png', DownloadFilename::fromClientName('photo', 'png')->name);
    }

    /**
     * @dataProvider pathProvider
     */
    public function testDropsDirectoryPart(string $clientName): void
    {
        $this->assertSame('passwd.png', DownloadFilename::fromClientName($clientName, 'png')->name);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function pathProvider(): iterable
    {
        yield 'unix traversal' => ['../../etc/passwd'];
        yield 'windows traversal' => ['..\\..\\Windows\\passwd.ini'];
        yield 'absolute path' => ['/etc/passwd.txt'];
    }

    public function testStripsControlAndReservedCharacters(): void
    {
        $filename = DownloadFilename::fromClientName("evil\r\nSet-Cookie: x=\"1\";<b>|?*.png", 'png');

        $this->assertSame('evil_Set-Cookie_ x=_1_;_b.png', $filename->name);
        $this->assertDoesNotMatchRegularExpression('/[\r\n"]/', $filename->name);
    }

    public function testStripsBidiOverride(): void
    {
        $this->assertSame('invoice_gpj.exe.png', DownloadFilename::fromClientName("invoice\u{202E}gpj.exe.png", 'png')->name);
    }

    /**
     * @dataProvider emptyBasenameProvider
     */
    public function testFallsBackToDefaultBasename(string $clientName): void
    {
        $filename = DownloadFilename::fromClientName($clientName, 'gif');

        $this->assertSame('image.gif', $filename->name);
        $this->assertSame('image.gif', $filename->asciiFallback);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function emptyBasenameProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'extension only' => ['.png'];
        yield 'dots' => ['...'];
        yield 'invalid utf-8' => ["\xC3\x28.png"];
        yield 'control characters only' => ["\x00\x01.png"];
    }

    public function testLimitsBasenameLength(): void
    {
        $filename = DownloadFilename::fromClientName(str_repeat('ż', 300) . '.png', 'png');

        $this->assertSame(str_repeat('ż', 100) . '.png', $filename->name);
    }

    public function testKeepsUnicodeNameWithAsciiFallback(): void
    {
        $filename = DownloadFilename::fromClientName('zdjęcie łódź 100% ok.png', 'jpg');

        $this->assertSame('zdjęcie łódź 100% ok.jpg', $filename->name);
        $this->assertSame('zdjecie lodz 100_ ok.jpg', $filename->asciiFallback);
    }

    public function testNonLatinNameFallsBackToPrintableAscii(): void
    {
        $filename = DownloadFilename::fromClientName('写真.png', 'png');

        $this->assertSame('写真.png', $filename->name);
        $this->assertMatchesRegularExpression('/^[\x20-\x7E]+$/', $filename->asciiFallback);
    }

    /**
     * @dataProvider hostileNameProvider
     */
    public function testAlwaysProducesAValidContentDisposition(string $clientName): void
    {
        $filename = DownloadFilename::fromClientName($clientName, 'png');

        $header = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $filename->name, $filename->asciiFallback);

        $this->assertDoesNotMatchRegularExpression('/[\x00-\x1F\x7F]/', $header);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function hostileNameProvider(): iterable
    {
        yield 'crlf' => ["a\r\nb.png"];
        yield 'percent' => ['100%.png'];
        yield 'quotes and backslashes' => ['a"b\\c.png'];
        yield 'polish' => ['źdźbło.png'];
        yield 'emoji' => ["\u{1F600}.png"];
        yield 'invalid utf-8' => ["\xFF\xFE.png"];
    }
}
