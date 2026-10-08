<?php

namespace App\Action;

use App\Service\PixelBudget;
use Imagick;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 50)]
final class ResizeAction implements FileAction
{
    public function name(): string
    {
        return 'resize';
    }

    public function spec(): string
    {
        return '10–300 %';
    }

    public function option(): ActionOption
    {
        return ActionOption::range(10, 300, 5, 100);
    }

    public function process(Imagick $image, int|string|null $value): void
    {
        $factor = (int) $value / 100;
        $width = (int) ($image->getImageWidth() * $factor);
        $height = (int) ($image->getImageHeight() * $factor);
        PixelBudget::assertWithin($width, $height);

        $image->scaleImage($width, $height);
    }
}
