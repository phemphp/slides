<?php

namespace App\Tests\Ui\Component;

use App\Entity\Presentation;
use App\Repository\PresentationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Phem\Test\{ComponentTester, InteractsWithPhem};
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DeckNavTest extends KernelTestCase
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

    public function testTheSlideShowsAllItsNotes(): void
    {
        $this->deck()
            ->assertSelectorCount(3, '.ph-note:not([hidden])')
            ->assertSelectorCount(3, '.ph-note .ph-step.is-done')
            ->assertSelectorTextContains('.ph-note:not([hidden])', 'A page is a route');
    }

    public function testTheDeckHasNoUpAndDownButtons(): void
    {
        $this->deck()
            ->assertSelectorNotExists('[data-deck-keys-target="up"]')
            ->assertSelectorNotExists('[data-deck-keys-target="down"]');
    }

    public function testPreviousShowsThePageAnatomy(): void
    {
        $this->deck()
            ->click('[data-deck-keys-target="previous"]')
            ->assertSelectorTextContains('h2', '#[Page]')
            ->assertSelectorCount(0, '.ph-note');

        $this->assertSame(self::PAGE_COMPONENT - 1, $this->slide());
    }

    public function testNextGoesBackToTheSlide(): void
    {
        $this->deck()
            ->click('[data-deck-keys-target="previous"]')
            ->click('[data-deck-keys-target="next"]')
            ->assertSelectorTextContains('.ph-note:not([hidden])', 'A page is a route');

        $this->assertSame(self::PAGE_COMPONENT, $this->slide());
    }

    private function deck(): ComponentTester
    {
        return $this->visit('/');
    }

    private function slide(): int
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        return self::getContainer()->get(PresentationRepository::class)->get()->slide;
    }
}
