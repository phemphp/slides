<?php

namespace App\Tests\Ui\Slide;

use App\Entity\Presentation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Phem\Test\{ComponentTester, InteractsWithPhem};
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SuspenseTest extends KernelTestCase
{
    use InteractsWithPhem;

    private const int SLIDE = 19;

    protected function setUp(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $schema = new SchemaTool($em);
        $schema->dropDatabase();
        $schema->createSchema($em->getMetadataFactory()->getAllMetadata());

        $presentation = new Presentation();
        $presentation->slide = self::SLIDE;
        $em->persist($presentation);
        $em->flush();
    }

    public function testTheSlideHasOneCodeWindowAndNoTitle(): void
    {
        $this->slide()
            ->assertSelectorNotExists('.ph-slide h2')
            ->assertSelectorCount(1, '.ph-window')
            ->assertSelectorTextContains('.ph-window-tab.is-active', 'SalesPage.phem')
            ->assertSelectorExists('.ph-code.no-numbers')
            ->assertSelectorNotExists('.ph-code.has-focus')
            ->assertSelectorCount(17, '.ph-line');
    }

    public function testTheLegendIsAfterTheCodeWindow(): void
    {
        $this->slide()
            ->assertSelectorCount(4, '.ph-note')
            ->assertSelectorCount(4, '.ph-note .ph-step.is-done')
            ->assertSelectorTextContains('.ph-note:nth-of-type(1)', 'The fallback goes first')
            ->assertSelectorTextContains('.ph-note:nth-of-type(2)', 'The content streams')
            ->assertSelectorTextContains('.ph-note:nth-of-type(3)', 'lazy waits for the scroll')
            ->assertSelectorTextContains('.ph-note:nth-of-type(4)', 'error catches a throw')
            ->assertSelectorExists('.grid > .ph-window:first-child + div > .ph-note');
    }

    public function testTheCodeMarksTheBoundaryTheLazyBoundaryAndTheErrorSlot(): void
    {
        $this->slide()
            ->assertSelectorCount(3, '.ph-line.is-focus')
            ->assertSelectorTextContains('.ph-line.is-focus:nth-of-type(2)', '<Suspense>')
            ->assertSelectorTextContains('.ph-line.is-focus:nth-of-type(8)', '<Suspense lazy>')
            ->assertSelectorTextContains('.ph-line.is-focus:nth-of-type(12)', '<Suspense.error>');
    }

    public function testTheCodeHasTheFallbackSlotOfEachBoundary(): void
    {
        $document = $this->slide()->document();

        $slots = [];
        foreach ($document->querySelectorAll('.ph-line') as $line) {
            $text = trim($line->textContent);
            if (str_starts_with($text, '<Suspense.')) {
                $slots[] = $text;
            }
        }

        $this->assertSame(['<Suspense.fallback>', '<Suspense.fallback>', '<Suspense.error>'], $slots);
    }

    public function testASlotTagHasOneColor(): void
    {
        $document = $this->slide()->document();

        $names = [];
        foreach ($document->querySelectorAll('.ph-line:nth-of-type(12) .tok-component') as $token) {
            $names[] = $token->textContent;
        }

        $this->assertSame(['Suspense', '.', 'error'], $names);
    }

    public function testAnAttributeWithoutAValueHasTheAttributeColor(): void
    {
        $this->slide()
            ->assertSelectorCount(1, '.ph-line:nth-of-type(8) .tok-attr')
            ->assertSelectorTextContains('.ph-line:nth-of-type(8) .tok-attr', 'lazy');
    }

    private function slide(): ComponentTester
    {
        return $this->visit('/');
    }
}
