<?php

namespace App;

function replace_extension($filename, $new_extension)
{
    $info = pathinfo($filename);
    return $info['filename'] . '.' . $new_extension;
}
