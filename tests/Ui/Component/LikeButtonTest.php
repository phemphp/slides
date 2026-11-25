<?php

namespace App\Tests\Ui\Component;

use App\Entity\Post;
use App\Entity\Presentation;
use App\Ui\Slide\Example;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Phem\Test\InteractsWithPhem;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class LikeButtonTest extends KernelTestCase
{
    use InteractsWithPhem;

    private const int EXAMPLE_SLIDE = 1;

    private int $postId;

    protected function setUp(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $schema = new SchemaTool($em);
        $schema->dropDatabase();
        $schema->createSchema($em->getMetadataFactory()->getAllMetadata());

        $post = new Post('Hello, components', new \DateTimeImmutable('2026-10-22'), 'A short post.');
        $post->likes = 7;
        $em->persist($post);
        $em->flush();
        $this->postId = $post->id;
    }

    public function testSlideShowsThePost(): void
    {
        $this->mount(Example::class)
            ->assertSee('Blog · 22 October')
            ->assertSee('Hello, components')
            ->assertSee('A short post.')
            ->assertSelectorTextContains('.ph-like', '♥ Like 7');
    }

    public function testClickSavesTheLike(): void
    {
        $this->mount(Example::class)
            ->click('.ph-like')
            ->assertSelectorTextContains('.ph-like', '♥ Like 8');

        $this->assertSame(8, $this->likesInDatabase());
    }

    public function testEachClickAddsALike(): void
    {
        $this->mount(Example::class)
            ->click('.ph-like')
            ->click('.ph-like')
            ->click('.ph-like')
            ->assertSelectorTextContains('.ph-like', '♥ Like 10');

        $this->assertSame(10, $this->likesInDatabase());
    }

    public function testALikeShowsInTheOtherBrowsers(): void
    {
        $this->showTheExampleSlide();

        $other = $this->visit('/');

        $this->visit('/')->click('.ph-like');

        $other->assertSelectorTextContains('.ph-like', '♥ Like 7');
        $other->receive()->assertSelectorTextContains('.ph-like', '♥ Like 8');
    }

    public function testTheResetButtonSetsTheLikesToZero(): void
    {
        $this->showTheExampleSlide();

        $other = $this->visit('/');
        $deck = $this->visit('/');

        $deck->assertSelectorTextContains('.ph-like', '♥ Like 7')
            ->click('[data-deck-reset]');

        $this->assertSame(0, $this->likesInDatabase());

        // The reset renders its own button only: the like button gets the refresh of the change
        // of Post::$likes in each browser.
        $deck->receive()
            ->assertSelectorTextContains('.ph-like', '♡ Like')
            ->assertSelectorCount(0, '.ph-like .tabular-nums');
        $other->receive()
            ->assertSelectorTextContains('.ph-like', '♡ Like')
            ->assertSelectorCount(0, '.ph-like .tabular-nums');
    }

    public function testOnlyTheExampleSlideHasTheResetButton(): void
    {
        $this->showTheExampleSlide();

        $this->visit('/')
            ->assertSelectorCount(1, '.ph-preview-bar [data-deck-reset] + .ph-url')
            ->click('[data-deck-keys-target="next"]')
            ->assertSelectorCount(0, '[data-deck-reset]')
            ->click('[data-deck-keys-target="first"]')
            ->assertSelectorCount(0, '[data-deck-reset]');
    }

    public function testALikeAfterTheResetCountsFromOne(): void
    {
        $this->showTheExampleSlide();

        $deck = $this->visit('/');
        $deck->click('[data-deck-reset]');
        $deck->receive()
            ->click('.ph-like')
            ->assertSelectorTextContains('.ph-like', '♥ Like 1');

        $this->assertSame(1, $this->likesInDatabase());
    }

    public function testEmptyHeartAndNoCounterWithNoLikes(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->getConnection()->executeStatement('UPDATE post SET likes = 0');
        $em->clear();

        $this->mount(Example::class)
            ->assertSelectorTextContains('.ph-like', '♡ Like')
            ->assertSelectorCount(0, '.ph-like .tabular-nums')
            ->click('.ph-like')
            ->assertSelectorTextContains('.ph-like', '♥ Like 1');
    }

    /**
     * Only a page gets the pushes: the deck shows the example slide.
     */
    private function showTheExampleSlide(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $presentation = new Presentation();
        $presentation->slide = self::EXAMPLE_SLIDE;
        $em->persist($presentation);
        $em->flush();
    }

    private function likesInDatabase(): int
    {
        $connection = self::getContainer()->get(EntityManagerInterface::class)->getConnection();

        return (int) $connection->fetchOne('SELECT likes FROM post WHERE id = ?', [$this->postId]);
    }
}
