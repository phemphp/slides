<?php

namespace App\Tests\Ui\Component;

use App\Entity\Presentation;
use App\Repository\PresentationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Phem\Test\{ComponentTester, InteractsWithPhem};
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DeckStepTest extends KernelTestCase
{
    use InteractsWithPhem;

    private const int PAGE_COMPONENT = 8;

    protected function setUp(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $schema = new SchemaTool($em);
        $schema->dropDatabase();
        $schema->createSchema($em->getMetadataFactory()->getAllMetadata());

        $presentation = new Presentation();
        $presentation->slide = self::PAGE_COMPONENT;
        $em->persist($presentation);
        $em->flush();
    }

    public function testTheSlideShowsAllItsPointsAtOnce(): void
    {
        $this->deck()
            ->assertSelectorNotExists('[data-deck-steps]')
            ->assertSelectorCount(3, '.ph-note:not([hidden])')
            ->assertSelectorCount(3, '.ph-note .ph-step.is-done')
            ->assertSelectorTextContains('.ph-note:not([hidden])', 'A page is a route')
            ->assertSelectorCount(0, '.ph-line .ph-step')
            ->assertSelectorCount(0, '.ph-line.is-focus');
    }

    public function testDownKeepsAllThePointsOnTheSlide(): void
    {
        $this->deck()
            ->click('[data-deck-keys-target="down"]')
            ->click('[data-deck-keys-target="down"]')
            ->assertSelectorNotExists('[data-deck-steps]')
            ->assertSelectorCount(3, '.ph-note:not([hidden])')
            ->assertSelectorCount(3, '.ph-note .ph-step.is-done');

        $this->assertSame(3, $this->step());
    }

    public function testUpStopsAtThePoint1(): void
    {
        $this->deck()
            ->click('[data-deck-keys-target="down"]')
            ->click('[data-deck-keys-target="up"]')
            ->click('[data-deck-keys-target="up"]')
            ->assertSelectorCount(3, '.ph-note:not([hidden])');

        $this->assertSame(1, $this->step());
    }

    public function testANewSlideStartsAtItsFirstPoint(): void
    {
        $this->deck()
            ->click('[data-deck-keys-target="down"]')
            ->click('[data-deck-keys-target="previous"]')
            ->click('[data-deck-keys-target="next"]')
            ->assertSelectorTextContains('.ph-note:not([hidden])', 'A page is a route');

        $this->assertSame(1, $this->step());
    }

    public function testPreviousShowsThePageAnatomy(): void
    {
        $this->deck()
            ->click('[data-deck-keys-target="previous"]')
            ->assertSelectorTextContains('h2', '#[Page]')
            ->assertSelectorCount(0, '.ph-note');
    }

    private function deck(): ComponentTester
    {
        return $this->visit('/');
    }

    private function step(): int
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        return self::getContainer()->get(PresentationRepository::class)->get()->step;
    }
}
