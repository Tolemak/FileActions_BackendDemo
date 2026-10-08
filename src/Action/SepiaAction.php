<?php

namespace App\Action;

use Imagick;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 10)]
final class SepiaAction implements FileAction
{
    public function name(): string
    {
        return 'sepia';
    }

    public function spec(): string
    {
        return '1–100';
    }

    public function option(): ActionOption
    {
        return ActionOption::range(1, 100, 1, 80);
    }

    public function process(Imagick $image, int|string|null $value): void
    {
        $image->sepiaToneImage((int) $value / 100 * $image->getQuantumRange()['quantumRangeLong']);
    }
}
