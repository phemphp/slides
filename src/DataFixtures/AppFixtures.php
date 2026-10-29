<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\Tech;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist(Post::example());

        foreach (Tech::stack() as $tech) {
            $manager->persist($tech);
        }

        $manager->flush();
    }
}
