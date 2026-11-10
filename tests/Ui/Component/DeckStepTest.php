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

    private const int CLASS_COMPONENT = 7;

    protected function setUp(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $schema = new SchemaTool($em);
        $schema->dropDatabase();
        $schema->createSchema($em->getMetadataFactory()->getAllMetadata());

        $presentation = new Presentation();
        $presentation->slide = self::CLASS_COMPONENT;
        $em->persist($presentation);
        $em->flush();
    }

    public function testTheSlideStartsAtThePoint1(): void
    {
        $this->speaker()
            ->assertSelectorExists('[data-deck-steps="4"][data-deck-step="1"]')
            ->assertSelectorCount(1, '.ph-line .ph-step')
            ->assertSelectorCount(0, '.ph-step.is-done')
            ->assertSelectorCount(1, '.ph-note:not([hidden])')
            ->assertSelectorTextContains('.ph-note:not([hidden])', 'Mark a class')
            ->assertSelectorCount(2, '.ph-line.is-focus');
    }

    public function testDownShowsTheNextPoint(): void
    {
        $this->speaker()
            ->click('[data-deck-keys-target="down"]')
            ->click('[data-deck-keys-target="down"]')
            ->assertSelectorExists('[data-deck-step="3"]')
            ->assertSelectorCount(3, '.ph-line .ph-step')
            ->assertSelectorCount(2, '.ph-line .ph-step.is-done')
            ->assertSelectorTextContains('.ph-line .ph-step:not(.is-done)', '3')
            ->assertSelectorCount(3, '.ph-note:not([hidden])')
            ->assertSelectorCount(2, '.ph-note .ph-step.is-done')
            ->assertSelectorCount(3, '.ph-line.is-focus');

        $this->assertSame(3, $this->step());
    }

    public function testUpHidesTheCurrentPoint(): void
    {
        $this->speaker()
            ->click('[data-deck-keys-target="down"]')
            ->click('[data-deck-keys-target="up"]')
            ->click('[data-deck-keys-target="up"]')
            ->assertSelectorExists('[data-deck-step="1"]');

        $this->assertSame(1, $this->step());
    }

    public function testANewSlideStartsAtItsFirstPoint(): void
    {
        $this->speaker()
            ->click('[data-deck-keys-target="down"]')
            ->click('[data-deck-keys-target="next"]')
            ->assertSelectorExists('[data-deck-steps="3"][data-deck-step="1"]')
            ->assertSelectorTextContains('.ph-note:not([hidden])', 'A page is a route');

        $this->assertSame(1, $this->step());
    }

    private function speaker(): ComponentTester
    {
        return $this->visit('/speaker?key=test')->followRedirect();
    }

    private function step(): int
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        return self::getContainer()->get(PresentationRepository::class)->get()->step;
    }
}
