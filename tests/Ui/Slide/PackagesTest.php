<?php

namespace App\Tests\Ui\Slide;

use App\Entity\Presentation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Phem\Test\InteractsWithPhem;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PackagesTest extends KernelTestCase
{
    use InteractsWithPhem;

    protected function setUp(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $schema = new SchemaTool($em);
        $schema->dropDatabase();
        $schema->createSchema($em->getMetadataFactory()->getAllMetadata());
    }

    public function testTheSlideShowsHowToInstallAndUseAPackage(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $presentation = new Presentation();
        $presentation->slide = 18;
        $em->persist($presentation);
        $em->flush();

        $this->visit('/')
            ->assertSelectorTextContains('.ph-terminal-body', 'composer require phem/ui-base')
            ->assertSelectorTextContains('.ph-window .ph-window-tab.is-active', 'Faq.phem')
            ->assertSelectorCount(2, '.ph-line.is-focus')
            ->assertSelectorTextContains('.ph-line.is-focus', 'use Phem\Ui\Base\{Accordion, AccordionItem}')
            ->assertSelectorTextContains('.ph-code', '<AccordionTrigger>What is Phem?</AccordionTrigger>')
            ->assertSelectorCount(4, '.ph-note')
            ->assertSelectorTextContains('.ph-note', 'Require the package');
    }
}
