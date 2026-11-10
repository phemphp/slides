<?php

namespace App\Entity;

use App\Repository\TechRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TechRepository::class)]
class Tech
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    public ?int $id = null;

    #[ORM\Column]
    public int $votes = 0;

    public function __construct(
        #[ORM\Column(unique: true)]
        public string $name,

        // The file name in assets/images/tech/, with no extension.
        #[ORM\Column]
        public string $icon,

        #[ORM\Column(enumType: Side::class)]
        public Side $side,
    ) {
    }

    public static function stack(): array
    {
        return [
            new self('React', 'react', Side::Frontend),
            new self('TypeScript', 'typescript', Side::Frontend),
            new self('JavaScript', 'javascript', Side::Frontend),
            new self('Turbo', 'hotwire', Side::Frontend),
            new self('Stimulus', 'stimulus', Side::Frontend),
            new self('htmx', 'htmx', Side::Frontend),
            new self('PHP', 'php', Side::Backend),
            new self('Symfony', 'symfony', Side::Backend),
            new self('API Platform', 'api-platform', Side::Backend),
            new self('Symfony UX', 'symfony-ux', Side::Backend),
            new self('Doctrine', 'doctrine', Side::Backend),
            new self('Twig', 'twig', Side::Backend),
            new self('Mercure', 'mercure', Side::Backend),
            new self('PostgreSQL', 'postgresql', Side::Backend),
        ];
    }
}
