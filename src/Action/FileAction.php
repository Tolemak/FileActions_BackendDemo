<?php

namespace App\Action;

use Imagick;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.file_action')]
interface FileAction
{
    public function name(): string;

    public function spec(): string;

    public function option(): ?ActionOption;

    public function process(Imagick $image, int|string|null $value): void;
}
