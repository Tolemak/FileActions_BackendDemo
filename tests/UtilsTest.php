<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

use function App\replace_extension;

class UtilsTest extends TestCase
{
    public function testReplaceExtensionSwapsSuffix(): void
    {
        $this->assertSame('photo.png', replace_extension('photo.jpg', 'png'));
    }

    public function testReplaceExtensionKeepsFilenameWithMultipleDots(): void
    {
        $this->assertSame('my.holiday.photo.webp', replace_extension('my.holiday.photo.jpeg', 'webp'));
    }

    public function testReplaceExtensionHandlesFilenameWithoutExtension(): void
    {
        $this->assertSame('photo.png', replace_extension('photo', 'png'));
    }
}
