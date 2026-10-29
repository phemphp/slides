<?php

namespace App\Entity;

/**
 * Where a tech of the stack slide runs: in the browser or on the server.
 */
enum Side: string
{
    case Frontend = 'frontend';
    case Backend = 'backend';
}
