<?php

namespace App\Deck;

/**
 * The current point of the current slide. A slide with points shows them one at a time: the
 * points up to the current one show, and the current one is highlighted.
 *
 * A context of Phem has a class as its key, so the number is in an object.
 */
final readonly class Step
{
    public function __construct(
        public int $current = 1,
    ) {
    }

    public function shows(int $n): bool
    {
        return $n <= $this->current;
    }

    public function isDone(int $n): bool
    {
        return $n < $this->current;
    }
}
