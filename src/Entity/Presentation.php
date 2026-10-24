<?php

namespace App\Entity;

use App\Repository\PresentationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * The live state of the presentation (one row): the slide that all the screens show.
 */
#[ORM\Entity(repositoryClass: PresentationRepository::class)]
class Presentation
{
    #[ORM\Id, ORM\Column]
    public int $id = 1;

    #[ORM\Column]
    public int $slide = 0;
}
