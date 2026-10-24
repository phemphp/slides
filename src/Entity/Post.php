<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * The blog post of the example slide, with the like counter of its LikeButton.
 */
#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    public ?int $id = null;

    #[ORM\Column]
    public int $likes = 0;

    public function __construct(
        #[ORM\Column]
        public string $title,

        #[ORM\Column(type: Types::DATE_IMMUTABLE)]
        public \DateTimeImmutable $date,

        #[ORM\Column(type: Types::TEXT)]
        public string $summary,
    ) {
    }
}
