<?php

namespace App\Tests\Enum;

use App\Enum\ExtensionToConvert;
use PHPUnit\Framework\TestCase;

class ExtensionToConvertTest extends TestCase
{
    public function testResolvesSupportedMimeTypes(): void
    {
        $this->assertSame(ExtensionToConvert::JPEG, ExtensionToConvert::fromMimeType('image/jpeg'));
        $this->assertSame(ExtensionToConvert::PNG, ExtensionToConvert::fromMimeType('image/png'));
        $this->assertSame(ExtensionToConvert::GIF, ExtensionToConvert::fromMimeType('image/gif'));
    }

    public function testRejectsOtherMimeTypes(): void
    {
        $this->assertNull(ExtensionToConvert::fromMimeType('image/svg+xml'));
        $this->assertNull(ExtensionToConvert::fromMimeType('text/png'));
        $this->assertNull(ExtensionToConvert::fromMimeType(''));
    }

    public function testUsesJpgAsFileExtension(): void
    {
        $this->assertSame('jpg', ExtensionToConvert::JPEG->fileExtension());
        $this->assertSame('png', ExtensionToConvert::PNG->fileExtension());
        $this->assertSame('image/jpeg', ExtensionToConvert::JPEG->mimeType());
    }
}
