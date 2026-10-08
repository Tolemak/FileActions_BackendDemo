<?php

namespace App\Action;

use App\Enum\ExtensionToConvert;

interface ChangesOutputFormat
{
    public function outputFormat(string $value): ExtensionToConvert;
}
