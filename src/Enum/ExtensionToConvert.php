<?php

namespace App\Enum;

enum ExtensionToConvert: string
{
    case JPEG = 'jpeg';
    case PNG = 'png';
    case GIF = 'gif';

    public static function fromMimeType(string $mimeType): ?self
    {
        return str_starts_with($mimeType, 'image/') ? self::tryFrom(substr($mimeType, 6)) : null;
    }

    public function mimeType(): string
    {
        return 'image/' . $this->value;
    }

    public function fileExtension(): string
    {
        return $this === self::JPEG ? 'jpg' : $this->value;
    }
}
