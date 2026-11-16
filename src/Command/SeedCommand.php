<?php

namespace App\Command;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        } else {
            $this->em->persist(Post::example());
            $io->writeln('Added the post of the example slide.');
        }

        $this->em->flush();

        return 0;
    }
}
