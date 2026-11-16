<?php

namespace App\Tests\Ui\Component;

use App\Entity\Post;
use App\Ui\Slide\Example;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Phem\Test\InteractsWithPhem;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class LikeButtonTest extends KernelTestCase
{
    use InteractsWithPhem;

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
            ->assertSelectorTextContains('button', '♥ Like 7');
    }

    public function testClickSavesTheLike(): void
    {
        $this->mount(Example::class)
            ->click('button')
            ->assertSelectorTextContains('button', '♥ Like 8');

        $this->assertSame(8, $this->likesInDatabase());
    }

    public function testEachClickAddsALike(): void
    {
        $this->mount(Example::class)
            ->click('button')
            ->click('button')
            ->click('button')
            ->assertSelectorTextContains('button', '♥ Like 10');

        $this->assertSame(10, $this->likesInDatabase());
    }

    public function testEmptyHeartAndNoCounterWithNoLikes(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->getConnection()->executeStatement('UPDATE post SET likes = 0');
        $em->clear();

        $this->mount(Example::class)
            ->assertSelectorTextContains('button', '♡ Like')
            ->assertSelectorCount(0, 'button .tabular-nums')
            ->click('button')
            ->assertSelectorTextContains('button', '♥ Like 1');
    }

    private function likesInDatabase(): int
    {
        $connection = self::getContainer()->get(EntityManagerInterface::class)->getConnection();

        return (int) $connection->fetchOne('SELECT likes FROM post WHERE id = ?', [$this->postId]);
    }
}
