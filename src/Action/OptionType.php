<?php

namespace App\Action;

enum OptionType: string
{
    case Range = 'range';
    case Choice = 'choice';
}
