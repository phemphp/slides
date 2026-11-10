<?php

namespace App\Deck;

enum Role: string
{
    case Speaker = 'speaker';
    case Audience = 'audience';
}
