import { Controller } from '@hotwired/stimulus';

// The keys of a keyboard and of a presentation clicker, by the target that they click.
const KEYS = {
    ArrowRight: 'next',
    PageDown: 'next',
    ' ': 'next',
    n: 'next',
    ArrowLeft: 'previous',
    PageUp: 'previous',
    p: 'previous',
    Home: 'first',
    ArrowDown: 'down',
    ArrowUp: 'up',
};

/*
 * The keyboard of the speaker: a key clicks the button of its action, so the move is a normal
 * Phem event.
 *
 * Down and up show and hide the points of a slide one at a time. The slide gives the count of
 * its points (data-deck-steps) and the current point (data-deck-step). On a slide without
 * points, and past the first or the last point, the key does nothing.
 */
export default class extends Controller {
    static targets = ['next', 'previous', 'first', 'down', 'up'];

    press(event) {
        if (event.defaultPrevented || event.altKey || event.ctrlKey || event.metaKey) {
            return;
        }

        if (event.target.closest?.('input, textarea, select, [contenteditable]')) {
            return;
        }

        const action = KEYS[event.key];
        const button = action && this.targets.find(action);
        if (!button) {
            return;
        }

        event.preventDefault();
        if (!this.canStep(action)) {
            return;
        }

        // A focused button would also get the Space key.
        document.activeElement?.blur();
        button.click();
    }

    canStep(action) {
        if ('down' !== action && 'up' !== action) {
            return true;
        }

        const slide = document.querySelector('#deck [data-deck-steps]');
        if (!slide) {
            return false;
        }

        const count = Number(slide.dataset.deckSteps);
        const current = Number(slide.dataset.deckStep);

        return 'down' === action ? current < count : current > 1;
    }
}
