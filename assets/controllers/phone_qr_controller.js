import { Controller } from '@hotwired/stimulus';

/*
 * The QR code of the deck shows while the phone button is pressed (see PhoneQr.phem). To close
 * the QR code, release the button with a click, so phem-toggle keeps the state.
 */
export default class extends Controller {
    static targets = ['button'];

    close() {
        if (this.buttonTarget.hasAttribute('data-pressed')) {
            this.buttonTarget.click();
        }
    }
}
