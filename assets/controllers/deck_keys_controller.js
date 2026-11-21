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
};

/*
 * The keyboard of the speaker: a key clicks the button of its action, so the move is a normal
 * Phem event.
 */
export default class extends Controller {
    static targets = ['next', 'previous', 'first'];

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

        // A focused button would also get the Space key.
        document.activeElement?.blur();
        button.click();
    }
}
