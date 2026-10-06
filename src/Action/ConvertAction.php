<?php

namespace App\Action;

use App\Enum\ExtensionToConvert;
use Imagick;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 40)]
final class ConvertAction implements FileAction, ChangesOutputFormat
{
    public function name(): string
    {
        return 'convert';
    }

    public function spec(): string
    {
        return 'JPG / PNG / GIF';
    }

    public function option(): ActionOption
    {
        $choices = [];
        foreach (ExtensionToConvert::cases() as $case) {
            $choices[$case->value] = strtoupper($case->value);
        }

        return ActionOption::choice($choices);
    }

    public function outputFormat(string $value): ExtensionToConvert
    {
        return ExtensionToConvert::from($value);
    }

    public function process(Imagick $image, int|string|null $value): void
    {
        $image->setImageFormat((string) $value);
    }
}
