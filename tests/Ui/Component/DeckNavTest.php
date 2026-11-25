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

    private const int PAGE_COMPONENT = 9;

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

    public function testAMoveShowsInTheOtherBrowsers(): void
    {
        $other = $this->deck();

        $this->deck()->click('[data-deck-keys-target="previous"]');

        $other->assertSelectorCount(3, '.ph-note');
        $other->receive()
            ->assertSelectorTextContains('h2', '#[Page]')
            ->assertSelectorCount(0, '.ph-note');
    }

    public function testTheDeckKeepsTheScreenOn(): void
    {
        $this->deck()->assertSelectorCount(1, '#deck[data-controller="wake-lock"]');
    }

    public function testTheTouchButtonTurnsOnTheSwipes(): void
    {
        $this->deck()
            ->assertSelectorCount(1, 'nav[data-controller~="deck-swipe"] [data-deck-touch][data-controller="phem-toggle"][data-deck-swipe-target="toggle"]')
            ->assertSelectorCount(1, 'nav[data-action~="touchstart@window->deck-swipe#start:passive"][data-action~="touchend@window->deck-swipe#end"][data-action~="touchcancel@window->deck-swipe#cancel"]');
    }

    public function testATapDoesNotMoveTheDeck(): void
    {
        // The two halves of the screen were buttons. Now only a swipe moves the deck.
        $this->deck()
            ->assertSelectorNotExists('[data-deck-tap]')
            ->assertSelectorNotExists('[data-deck-touchable]');
    }

    public function testASwipeToTheRightShowsThePreviousSlide(): void
    {
        // The deck-swipe controller clicks the target of the swipe.
        $this->deck()
            ->click('[data-deck-swipe-target="previous"]')
            ->assertSelectorTextContains('h2', '#[Page]');

        $this->assertSame(self::PAGE_COMPONENT - 1, $this->slide());
    }

    public function testASwipeToTheLeftShowsTheNextSlide(): void
    {
        $this->deck()
            ->click('[data-deck-swipe-target="previous"]')
            ->click('[data-deck-swipe-target="next"]')
            ->assertSelectorTextContains('.ph-note:not([hidden])', 'A page is a route');

        $this->assertSame(self::PAGE_COMPONENT, $this->slide());
    }

    public function testThePhoneButtonHasTheQrCodeOfTheNetworkAddress(): void
    {
        // phpunit.dist.xml sets HOST_IP. The port is the port of the request.
        $this->deck()
            ->assertSelectorCount(1, 'nav [data-deck-qr] img[src^="data:image/svg+xml;base64,"]')
            ->assertSelectorTextContains('[data-deck-qr]', 'http://192.0.2.10/');
    }

    public function testTheQrCodeStaysAfterAMove(): void
    {
        $this->deck()
            ->click('[data-deck-keys-target="previous"]')
            ->assertSelectorCount(1, '[data-deck-qr] img')
            ->assertSelectorTextContains('[data-deck-qr]', 'http://192.0.2.10/');
    }

    public function testTheQrCodeHasACloseButtonThatReleasesThePhoneButton(): void
    {
        $this->deck()
            ->assertSelectorCount(1, 'nav[data-controller~="phone-qr"][data-action~="keydown.esc@window->phone-qr#close"]')
            ->assertSelectorCount(1, 'nav [data-deck-touch][data-phone-qr-target="button"]')
            ->assertSelectorCount(1, 'nav [data-deck-qr] button[data-action="phone-qr#close"]');
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
