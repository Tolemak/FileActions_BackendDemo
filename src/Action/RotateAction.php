<?php

namespace App\Action;

use Imagick;
use ImagickPixel;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 20)]
final class RotateAction implements FileAction
{
    public function name(): string
    {
        return 'rotate';
    }

    public function spec(): string
    {
        return '0–360°';
    }

    public function option(): ActionOption
    {
        return ActionOption::range(0, 360, 5, 90);
    }

    public function process(Imagick $image, int|string|null $value): void
    {
        $image->rotateImage(new ImagickPixel('white'), (int) $value);
    }
}
