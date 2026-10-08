<?php

namespace App\Action;

use Imagick;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 30)]
final class CompressAction implements FileAction
{
    public function name(): string
    {
        return 'compress';
    }

    public function spec(): string
    {
        return '1–100';
    }

    public function option(): ActionOption
    {
        return ActionOption::range(1, 100, 1, 50);
    }

    public function process(Imagick $image, int|string|null $value): void
    {
        $image->setImageCompressionQuality((int) $value);
    }
}
