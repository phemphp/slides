<?php

namespace App\Deck;

use App\Repository\PresentationRepository;
use App\Ui\Slide\Slides;
use Phem\Node;

/**
 * The current slide of the presentation.
 */
final class Navigator
{
    // A class of a .phpm file: phemc builds it after the container, so it is not a service.
    private ?Slides $slides = null;

    public function __construct(
        private readonly PresentationRepository $presentation,
    ) {
    }

    public function current(): int
    {
        return $this->clamp($this->presentation->get()->slide);
    }

    public function count(): int
    {
        return \count($this->slides());
    }

    public function slide(int $index): ?Node
    {
        return $this->slides()[$index] ?? null;
    }

    public function go(int $index): int
    {
        $presentation = $this->presentation->get();
        $presentation->slide = $this->clamp($index);
        $this->presentation->save($presentation);

        return $presentation->slide;
    }

    /**
     * @return list<Node>
     */
    private function slides(): array
    {
        return ($this->slides ??= new Slides())->all();
    }

    private function clamp(int $index): int
    {
        return max(0, min($index, $this->count() - 1));
    }
}
