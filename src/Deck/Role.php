<?php

namespace App\Deck;

/**
 * Who looks at the deck. Only the speaker moves between the slides.
 */
enum Role: string
{
    case Speaker = 'speaker';
    case Audience = 'audience';
}
