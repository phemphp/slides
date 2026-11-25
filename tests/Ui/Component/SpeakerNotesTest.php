<?php

namespace App\Tests\Ui\Component;

use App\Entity\Post;
use App\Entity\Presentation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Phem\Test\{ComponentTester, InteractsWithPhem};
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SpeakerNotesTest extends KernelTestCase
{
    use InteractsWithPhem;

    protected function setUp(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $schema = new SchemaTool($em);
        $schema->dropDatabase();
        $schema->createSchema($em->getMetadataFactory()->getAllMetadata());

        $em->persist(new Presentation());
        // The example slide shows a post.
        $em->persist(Post::example());
        $em->flush();
    }

    public function testEachSlideHasItsNotesBelowTheSlideAndOutOfItsFrame(): void
    {
        $deck = $this->deck();
        $total = (int) explode('/', $deck->document()->querySelector('nav .tabular-nums')->textContent)[1];
        $this->assertGreaterThan(1, $total);

        $seen = [];
        for ($number = 1; $number <= $total; ++$number) {
            $document = $deck->document();
            // The frame scales its content (1280×720): notes in the frame would change the slide.
            $this->assertCount(1, $document->querySelectorAll('.ph-deck-stage .ph-frame + aside.ph-speaker-notes'), "Slide $number has no notes after its frame.");
            $this->assertCount(0, $document->querySelectorAll('.ph-frame .ph-speaker-notes'), "Slide $number has notes in its frame.");

            $notes = trim($document->querySelector('.ph-speaker-notes')->textContent);
            $this->assertNotSame('', $notes, "The notes of slide $number are empty.");
            $this->assertNotContains($notes, $seen, "Slide $number shows the notes of another slide.");
            $seen[] = $notes;

            $deck->click('[data-deck-swipe-target="next"]');
        }
    }

    public function testTheNotesStartAtTheirTopAfterAMove(): void
    {
        // The deck-notes controller moves the scroll to the top when the notes change.
        $this->deck()->assertSelectorCount(1, '.ph-speaker-notes[data-controller="deck-notes"]:not([data-action])');
    }

    private function deck(): ComponentTester
    {
        return $this->visit('/');
    }
}
