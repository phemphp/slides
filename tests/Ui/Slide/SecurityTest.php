<?php

namespace App\Tests\Ui\Slide;

use App\Entity\Presentation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Phem\Test\InteractsWithPhem;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SecurityTest extends KernelTestCase
{
    use InteractsWithPhem;

    protected function setUp(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $schema = new SchemaTool($em);
        $schema->dropDatabase();
        $schema->createSchema($em->getMetadataFactory()->getAllMetadata());
    }

    public function testTheSlideShowsTheGuardAndTheLimitOfAHandler(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $presentation = new Presentation();
        $presentation->slide = 17;
        $em->persist($presentation);
        $em->flush();

        $this->visit('/')
            ->assertSelectorTextContains('.ph-window-tab.is-active', 'PostPage.phem')
            ->assertSelectorCount(2, '.ph-line.is-focus')
            ->assertSelectorTextContains('.ph-line.is-focus:nth-of-type(9)', "IsGranted('POST_EDIT', subject: 'post')")
            ->assertSelectorTextContains('.ph-line.is-focus:nth-of-type(15)', "RateLimit('likes')")
            ->assertSelectorCount(4, '.ph-note')
            ->assertSelectorTextContains('.ph-note', 'The state has a signature')
            ->assertSelectorTextContains('.ph-note:nth-of-type(3)', 'A voter guards publish()')
            ->assertSelectorTextContains('.ph-note:nth-of-type(4)', 'like() has a rate limit');
    }
}
