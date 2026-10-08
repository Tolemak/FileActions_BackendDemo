<?php

namespace App\Service;

use App\Exception\ImageTooLargeException;

final class PixelBudget
{
    public const int MAX_PIXELS = 24_000_000;

    public static function assertWithin(int $width, int $height): void
    {
        if ($width * $height > self::MAX_PIXELS) {
            throw new ImageTooLargeException('Image exceeds the supported pixel budget.');
        }
    }
}
