<?php

namespace App\Tests\Fixtures;

use App\Action\ActionOption;
use App\Action\FileAction;
use Imagick;

final class GrayscaleAction implements FileAction
{
    public function name(): string
    {
        return 'grayscale';
    }

    public function spec(): string
    {
        return 'B&W';
    }

    public function option(): ?ActionOption
    {
        return null;
    }

    public function process(Imagick $image, int|string|null $value): void
    {
        $image->transformImageColorspace(Imagick::COLORSPACE_GRAY);
    }
}
