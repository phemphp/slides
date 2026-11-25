<?php

namespace App\Tests\Ui\Slide;

use App\Entity\Presentation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Phem\Test\InteractsWithPhem;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UiBaseCollageTest extends KernelTestCase
{
    use InteractsWithPhem;

    private const int SLIDE = 20;

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

    public function testTheSlideIsAGridOfTilesAroundTheInstallCommand(): void
    {
        $this->visit('/')
            ->assertSelectorCount(1, '.ph-slide > .grid.grid-cols-7')
            ->assertSelectorCount(33, '.grid > figure.ph-ui')
            ->assertSelectorCount(33, '.grid > figure.ph-ui > figcaption')
            ->assertSelectorCount(1, '.grid > .ph-terminal.ph-glow.col-span-3.col-start-3.row-start-4')
            // The terminal fills its three columns: it does not have the width of its text.
            ->assertSelectorCount(0, '.ph-terminal.place-self-center')
            ->assertSelectorTextContains('.ph-terminal-body', 'composer require phem/ui-base')
            // The terminal is not in a tile: it keeps the --accent of the slides.
            ->assertSelectorCount(0, '.ph-ui .ph-terminal')
            // The indicator measures the tab on the screen: it is wrong on a slide with a scale.
            ->assertSelectorCount(0, '[data-slot="tabs-indicator"]')
            ->assertSelectorTextContains('[data-slot="tabs-tab"][data-active]', 'Overview');
    }

    #[DataProvider('components')]
    public function testATileShowsTheComponent(string $name, string $slot): void
    {
        $tiles = [];
        foreach ($this->visit('/')->document()->querySelectorAll('.grid > figure.ph-ui') as $tile) {
            $tiles[trim($tile->querySelector('figcaption')->textContent)] = $tile;
        }

        self::assertArrayHasKey($name, $tiles);
        self::assertNotNull($tiles[$name]->querySelector(\sprintf('[data-slot="%s"]', $slot)), \sprintf('The "%s" tile has no "%s" part.', $name, $slot));
    }

    public static function components(): iterable
    {
        $components = [
            'Tabs' => 'tabs', 'Button' => 'button', 'Field' => 'field', 'Select' => 'select-trigger',
            'Checkbox' => 'checkbox', 'RadioGroup' => 'radio-group', 'Switch' => 'switch',
            'NumberField' => 'number-field', 'Slider' => 'slider', 'Progress' => 'progress',
            'Meter' => 'meter', 'Combobox' => 'combobox-input', 'Autocomplete' => 'combobox-input',
            'Avatar' => 'avatar', 'ToggleGroup' => 'toggle-group', 'Toggle' => 'toggle',
            'Menubar' => 'menubar', 'Toolbar' => 'toolbar', 'OTPField' => 'otp-field',
            'CheckboxGroup' => 'checkbox-group', 'Accordion' => 'accordion', 'Fieldset' => 'fieldset',
            'Tooltip' => 'tooltip-trigger', 'ScrollArea' => 'scroll-area', 'Collapsible' => 'collapsible',
            'Separator' => 'separator', 'Dialog' => 'dialog-trigger', 'AlertDialog' => 'alert-dialog-trigger',
            'Drawer' => 'drawer-trigger', 'Popover' => 'popover-trigger', 'Menu' => 'menu-trigger',
            'ContextMenu' => 'context-menu-trigger', 'PreviewCard' => 'preview-card-trigger',
        ];

        foreach ($components as $name => $slot) {
            yield $name => [$name, $slot];
        }
    }

    public function testNoComponentHasAServerHandler(): void
    {
        $document = $this->visit('/')->document();
        $slide = $document->saveHtml($document->querySelector('.ph-slide'));

        // A server handler is a data-phem-on-<event> attribute, and a poll is data-phem-poll. The
        // buttons of the deck navigation have one, but they are not in the slide.
        self::assertStringContainsString('data-phem-on-click', $document->saveHtml());
        self::assertDoesNotMatchRegularExpression('/data-phem-(on-|poll|bind)/', $slide);
    }
}
