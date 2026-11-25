import { Controller } from '@hotwired/stimulus';

/*
 * The screen stays on while the deck shows: a phone does not lock and a laptop does not sleep
 * during the talk. The browser releases the lock when the page is hidden, so the controller asks
 * again when the page shows again. A browser can refuse the lock (low battery, no action of the
 * user yet), so each tap and each key also asks again while there is no lock.
 *
 * Only a secure page (HTTPS or localhost) has navigator.wakeLock.
 */
export default class extends Controller {
    #lock = null;
    #asking = false;
    #connected = false;

    connect() {
        this.#connected = true;
        document.addEventListener('visibilitychange', this.#request);
        document.addEventListener('pointerdown', this.#request);
        document.addEventListener('keydown', this.#request);
        this.#request();
    }

    disconnect() {
        this.#connected = false;
        document.removeEventListener('visibilitychange', this.#request);
        document.removeEventListener('pointerdown', this.#request);
        document.removeEventListener('keydown', this.#request);
        this.#lock?.release();
        this.#lock = null;
    }

    #request = async () => {
        if (!navigator.wakeLock || this.#lock || this.#asking || 'visible' !== document.visibilityState) {
            return;
        }

        this.#asking = true;
        try {
            const lock = await navigator.wakeLock.request('screen');
            if (!this.#connected) {
                lock.release();

                return;
            }

            lock.addEventListener('release', () => {
                if (this.#lock === lock) {
                    this.#lock = null;
                }
            });
            this.#lock = lock;
        } catch {
            // The browser refused: the next tap, key or return to the page asks again.
        } finally {
            this.#asking = false;
        }
    };
}
