import { Controller } from '@hotwired/stimulus';

/*
 * The speaker notes of a slide (see SpeakerNotes.phem). A long text scrolls. The morph of a move
 * puts the notes of the new slide in the same element, so the controller moves the scroll to
 * the top again.
 */
export default class extends Controller {
    #observer = null;

    connect() {
        this.#observer = new MutationObserver(() => {
            this.element.scrollTop = 0;
        });
        this.#observer.observe(this.element, { childList: true, subtree: true, characterData: true });
    }

    disconnect() {
        this.#observer?.disconnect();
        this.#observer = null;
    }
}
