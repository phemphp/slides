<?php

namespace App\Entity;

enum Side: string
{
    case Frontend = 'frontend';
    case Backend = 'backend';
}
