<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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

    public static function example(): self
    {
        return new self(
            title: 'Hello, components',
            date: new \DateTimeImmutable('2026-10-22'),
            summary: 'A short post with one button under it. You click it, the heart fills, and the server saves your like.',
        );
    }
}
