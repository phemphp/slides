<?php

namespace App\DataFixtures;

use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // The post of the example slide (slide 3).
        $manager->persist(new Post(
            title: 'Hello, components',
            date: new \DateTimeImmutable('2026-10-22'),
            summary: 'A short post with one button under it. You click it, the heart fills, and the server saves your like.',
        ));

        $manager->flush();
    }
}
