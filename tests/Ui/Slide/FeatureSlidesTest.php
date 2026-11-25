<?php

namespace App\Tests\Ui\Slide;

use App\Entity\Presentation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Phem\Test\InteractsWithPhem;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class FeatureSlidesTest extends KernelTestCase
{
    use InteractsWithPhem;

    protected function setUp(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $schema = new SchemaTool($em);
        $schema->dropDatabase();
        $schema->createSchema($em->getMetadataFactory()->getAllMetadata());
    }

    #[DataProvider('slides')]
    public function testTheSlideShowsOneFeature(int $slide, string $file, int $focus, string $attribute, int $notes, string $note): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $presentation = new Presentation();
        $presentation->slide = $slide;
        $em->persist($presentation);
        $em->flush();

        $this->visit('/')
            ->assertSelectorTextContains('.ph-window-tab.is-active', $file)
            ->assertSelectorCount($focus, '.ph-line.is-focus')
            ->assertSelectorTextContains('.ph-line.is-focus', $attribute)
            ->assertSelectorCount($notes, '.ph-note')
            ->assertSelectorTextContains('.ph-note', $note);
    }

    public static function slides(): iterable
    {
        yield 'Transactional' => [12, 'LikeButton.phem', 1, '#[Transactional]', 4, 'Mark the handler'];
        yield 'Broadcast' => [13, 'LikeButton.phem', 1, '#[Broadcast]', 4, 'Mark the handler'];
        yield 'RedirectTo' => [14, 'PublishButton.phem', 1, 'new RedirectTo(PostPage::class', 4, 'Return a RedirectTo'];
        yield 'ErrorPage' => [15, 'ErrorPages.phem', 2, '#[ErrorPage(404)]', 4, 'Mark a function'];
        yield 'EventAndRequest' => [17, 'SearchBox.phem', 1, 'Event $e, Request $request', 4, 'Event: what the user did'];
        yield 'Testing' => [20, 'CounterTest.php', 4, '$this->mount(Counter::class', 4, 'PHPUnit, no browser'];
    }
}
