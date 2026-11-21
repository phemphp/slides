<?php

namespace App\Entity;

use App\Repository\PresentationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PresentationRepository::class)]
class Presentation
{
    #[ORM\Id, ORM\Column]
    public int $id = 1;

    #[ORM\Column]
    public int $slide = 0;
}
