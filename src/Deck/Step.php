<?php

namespace App\Deck;

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
