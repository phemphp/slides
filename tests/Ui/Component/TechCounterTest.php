<?php

namespace App\Tests\Ui\Component;

use App\Entity\Presentation;
use App\Entity\Side;
use App\Entity\Tech;
use App\Ui\Slide\Stack;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Phem\Test\InteractsWithPhem;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

// The TechCounters run in the stack slide: the slide loads the techs in each event, so the
// handler changes a managed entity.
final class TechCounterTest extends KernelTestCase
{
    use InteractsWithPhem;

    protected function setUp(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $schema = new SchemaTool($em);
        $schema->dropDatabase();
        $schema->createSchema($em->getMetadataFactory()->getAllMetadata());

        $react = new Tech('React', 'react', Side::Frontend);
        $react->votes = 4;
        $em->persist($react);
        $em->persist(new Tech('Twig', 'twig', Side::Backend));
        $em->flush();
    }

    public function testSlideShowsTheTechsOnTheirSide(): void
    {
        $this->mount(Stack::class)
            ->assertSelectorCount(2, 'button.ph-tech')
            ->assertSelectorTextContains('.ph-card:first-child button.ph-tech', 'React 4')
            ->assertSelectorTextContains('.ph-card:last-child button.ph-tech', 'Twig 0')
            ->assertSelectorCount(1, '.ph-tech-votes.is-voted');
    }

    public function testClickAddsAVoteToThatTechOnly(): void
    {
        $this->mount(Stack::class)
            ->click('.ph-card:last-child button.ph-tech')
            ->click('.ph-card:last-child button.ph-tech')
            ->assertSelectorTextContains('.ph-card:first-child button.ph-tech', 'React 4')
            ->assertSelectorTextContains('.ph-card:last-child button.ph-tech', 'Twig 2');

        $this->assertSame(['React' => 4, 'Twig' => 2], $this->votesInDatabase());
    }

    public function testVoteShowsOnTheOtherScreens(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $presentation = new Presentation();
        $presentation->slide = 3;
        $em->persist($presentation);
        $em->flush();

        $other = $this->visit('/')->assertSelectorTextContains('.ph-card:first-child button.ph-tech', 'React 4');

        $this->visit('/')->click('.ph-card:first-child button.ph-tech')->assertSelectorTextContains('.ph-card:first-child button.ph-tech', 'React 5');

        $other->receive()->assertSelectorTextContains('.ph-card:first-child button.ph-tech', 'React 5');
    }

    public function testEachTechOfTheStackHasALogo(): void
    {
        $dir = \dirname(__DIR__, 3).'/assets/images/tech';

        foreach (Tech::stack() as $tech) {
            $this->assertFileExists(\sprintf('%s/%s.svg', $dir, $tech->icon), $tech->name);
        }
    }

    /**
     * @return array<string, int>
     */
    private function votesInDatabase(): array
    {
        $connection = self::getContainer()->get(EntityManagerInterface::class)->getConnection();

        return array_map('intval', $connection->fetchAllKeyValue('SELECT name, votes FROM tech ORDER BY id'));
    }
}
