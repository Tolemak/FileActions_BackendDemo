<?php

namespace App;

function replace_extension(string $filename, string $new_extension): string
{
    $info = pathinfo($filename);
    return $info['filename'] . '.' . $new_extension;
}
