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
    public function testTheSlideShowsOneFeature(int $slide, string $file, string $attribute, int $notes, string $note): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $presentation = new Presentation();
        $presentation->slide = $slide;
        $em->persist($presentation);
        $em->flush();

        $this->visit('/')
            ->assertSelectorTextContains('.ph-window-tab.is-active', $file)
            ->assertSelectorCount(1, '.ph-line.is-focus')
            ->assertSelectorTextContains('.ph-line.is-focus', $attribute)
            ->assertSelectorCount($notes, '.ph-note')
            ->assertSelectorTextContains('.ph-note', $note);
    }

    public static function slides(): iterable
    {
        yield 'Transactional' => [11, 'LikeButton.phem', '#[Transactional]', 4, 'Mark the handler'];
        yield 'RedirectTo' => [12, 'PublishButton.phem', 'new RedirectTo(PostPage::class', 4, 'Return a RedirectTo'];
        yield 'Broadcast' => [13, 'LikeButton.phem', '#[Transactional, Broadcast]', 4, 'Mark the handler'];
    }
}
