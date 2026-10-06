<?php

namespace App\Service;

use App\Exception\ImageTooLargeException;

final class PixelBudget
{
    /**
     * A 5 MB upload can still decode to hundreds of megapixels, so the byte
     * limit alone does not bound memory. Dimensions are read from the header
     * before decoding and checked against this. At Q16 a pixel costs 8 bytes,
     * so this has to stay comfortably under FileService::MEMORY_LIMIT_BYTES.
     */
    public const int MAX_PIXELS = 24_000_000;

    public static function assertWithin(int $width, int $height): void
    {
        if ($width * $height > self::MAX_PIXELS) {
            throw new ImageTooLargeException('Image exceeds the supported pixel budget.');
        }
    }
}
