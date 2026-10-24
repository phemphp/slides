<?php

namespace App\Command;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Adds the data that the slides need, if it is not there. The start scripts run it, so a new
 * database (the prod volume) works without the fixtures.
 */
#[AsCommand('app:seed', 'Add the data that the slides need, if it is not there')]
final class SeedCommand
{
    public function __construct(
        private readonly PostRepository $posts,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        if (null !== $this->posts->first()) {
            $io->writeln('The post of the example slide is there.');

            return 0;
        }

        $this->em->persist(Post::example());
        $this->em->flush();
        $io->writeln('Added the post of the example slide.');

        return 0;
    }
}
