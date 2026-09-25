<?php

namespace App\Http;

use function Symfony\Component\String\u;

final readonly class DownloadFilename
{
    private const int MAX_BASENAME_LENGTH = 100;

    private const string DEFAULT_BASENAME = 'image';

    private function __construct(
        public string $name,
        public string $asciiFallback,
    ) {
    }

    public static function fromClientName(string $clientName, string $extension): self
    {
        $basename = self::sanitizeBasename($clientName);

        return new self(
            $basename . '.' . $extension,
            self::toAscii($basename) . '.' . $extension,
        );
    }

    private static function sanitizeBasename(string $clientName): string
    {
        $name = (string) preg_replace('~^.*[/\\\\]~s', '', $clientName);
        $name = (string) preg_replace('/\.[^.]*$/', '', $name);
        $name = (string) preg_replace('/[\p{C}"*:<>?|]+/u', '_', $name);
        $name = trim($name, ' ._');

        $name = (string) iconv_substr($name, 0, self::MAX_BASENAME_LENGTH, 'UTF-8');
        $name = rtrim($name, ' ._');

        return $name === '' ? self::DEFAULT_BASENAME : $name;
    }

    private static function toAscii(string $basename): string
    {
        $ascii = (string) preg_replace('/[^\x20-\x7E]|[%"*:<>?|]/', '_', u($basename)->ascii()->toString());
        $ascii = trim($ascii, ' ._');

        return $ascii === '' ? self::DEFAULT_BASENAME : $ascii;
    }
}
