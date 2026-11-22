<?php

namespace App\Tests\Ui\Slide;

use App\Entity\Presentation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Phem\Test\InteractsWithPhem;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class EditorSupportTest extends KernelTestCase
{
    use InteractsWithPhem;

    protected function setUp(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $schema = new SchemaTool($em);
        $schema->dropDatabase();
        $schema->createSchema($em->getMetadataFactory()->getAllMetadata());
    }

    public function testTheSlideShowsTheTwoEditorsTheCodingAgentAndTheCompiler(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $presentation = new Presentation();
        $presentation->slide = 17;
        $em->persist($presentation);
        $em->flush();

        $this->visit('/')
            ->assertSelectorTextContains('.ph-title', 'Your editor reads .phem')
            ->assertSelectorCount(4, '.ph-node')
            ->assertSelectorTextContains('.ph-node:nth-of-type(1)', 'VS Code')
            ->assertSelectorTextContains('.ph-node:nth-of-type(2)', 'PhpStorm')
            ->assertSelectorTextContains('.ph-node:nth-of-type(4)', 'Claude Code')
            ->assertSelectorCount(1, '.ph-node .ph-orb.is-active')
            ->assertSelectorTextContains('.ph-node:has(.ph-orb.is-active)', 'phemc')
            ->assertSelectorCount(2, '.ph-arrow')
            ->assertSelectorTextContains('.ph-arrow.is-accent', 'LSP')
            ->assertSelectorTextContains('.ph-arrow.is-cool.is-back', 'MCP')
            ->assertSelectorCount(8, '.ph-chip')
            ->assertSelectorCount(0, '.ph-footer');
    }
}
