import { Controller } from '@hotwired/stimulus';

// The shortest move of the finger that is a swipe, in px.
const DISTANCE = 48;
// A swipe goes this many times more to the side than up or down. A move that is more vertical
// is the scroll of the speaker notes.
const RATIO = 1.5;
// A move that starts in a control with its own drag is not a swipe of the deck.
const OWN_DRAG = 'input, textarea, select, [contenteditable], [role="slider"]';

/*
 * The touch navigation of the deck, on a phone with the touch button pressed: a swipe to the
 * left shows the next slide, and a swipe to the right shows the previous slide. A swipe clicks
 * the button of its action, so the move is a normal Phem event. A tap does not move the deck,
 * so the buttons of a slide work as usual.
 */
export default class extends Controller {
    static targets = ['toggle', 'next', 'previous'];

    #start = null;

    start(event) {
        this.#start = null;
        // Two fingers are a pinch, not a swipe.
        if (1 !== event.touches.length || !this.#isOn() || event.target.closest?.(OWN_DRAG)) {
            return;
        }

        const { clientX: x, clientY: y } = event.touches[0];
        this.#start = { x, y };
    }

    end(event) {
        const start = this.#start;
        this.#start = null;
        if (!start || event.touches.length > 0) {
            return;
        }

        const { clientX, clientY } = event.changedTouches[0];
        const dx = clientX - start.x;
        const dy = clientY - start.y;
        if (Math.abs(dx) < DISTANCE || Math.abs(dx) < RATIO * Math.abs(dy)) {
            return;
        }

        (dx < 0 ? this.nextTarget : this.previousTarget).click();
    }

    cancel() {
        this.#start = null;
    }

    // A screen with a mouse shows the QR code while the touch button is pressed, not the touch
    // navigation.
    #isOn() {
        return this.toggleTarget.hasAttribute('data-pressed') && window.matchMedia('(pointer: coarse)').matches;
    }
}
