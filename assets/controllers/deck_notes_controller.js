import { Controller } from '@hotwired/stimulus';

/*
 * The speaker notes of a slide (see SpeakerNotes.phem). The notes take the touches, so a long
 * text scrolls. Thus a tap on the notes does not go through to the two halves of the touch
 * navigation: the controller clicks the button of the half that has the tap.
 */
export default class extends Controller {
    #observer = null;

    connect() {
        // The morph of a move puts the notes of the new slide in the same element: start at
        // their top again.
        this.#observer = new MutationObserver(() => {
            this.element.scrollTop = 0;
        });
        this.#observer.observe(this.element, { childList: true, subtree: true, characterData: true });
    }

    disconnect() {
        this.#observer?.disconnect();
        this.#observer = null;
    }

    tap(event) {
        const half = event.clientX < window.innerWidth / 2 ? 'previous' : 'next';

        this.element.closest('#deck')?.querySelector(`[data-deck-tap="${half}"]`)?.click();
    }
}
