<?php

namespace App\Command;

use App\Entity\Post;
use App\Entity\Tech;
use App\Repository\PostRepository;
use App\Repository\TechRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:seed', 'Add the data that the slides need, if it is not there')]
final class SeedCommand
{
    public function __construct(
        private readonly PostRepository $posts,
        private readonly TechRepository $techs,
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

        if ([] !== $this->techs->stack()) {
            $io->writeln('The techs of the stack slide are there.');
        } else {
            foreach (Tech::stack() as $tech) {
                $this->em->persist($tech);
            }
            $io->writeln('Added the techs of the stack slide.');
        }

        $this->em->flush();

        return 0;
    }
}
