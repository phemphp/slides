import { Controller } from '@hotwired/stimulus';
import silentVideo from '../wake_lock_video.js';

/*
 * The screen stays on while the deck shows: a phone does not lock and a laptop does not sleep
 * during the talk. The browser releases the lock when the page is hidden, so the controller asks
 * again when the page shows again. A browser can refuse the lock (low battery, no action of the
 * user yet), so each tap and each key also asks again while there is no lock.
 *
 * Only a secure page (HTTPS or localhost) has navigator.wakeLock. A phone opens the deck with
 * the address of the network, on plain HTTP. There, the controller plays a video that the user
 * cannot see: a phone keeps its screen on while a video plays (the method of NoSleep.js).
 */
// A click is the action of the user that lets a phone start a video with sound.
const EVENTS = ['visibilitychange', 'pointerdown', 'keydown', 'click'];

export default class extends Controller {
    #lock = null;
    #asking = false;
    #connected = false;
    #video = null;

    connect() {
        this.#connected = true;
        const request = navigator.wakeLock ? this.#request : this.#play;
        EVENTS.forEach((event) => document.addEventListener(event, request));
        request();
    }

    disconnect() {
        this.#connected = false;
        EVENTS.forEach((event) => {
            document.removeEventListener(event, this.#request);
            document.removeEventListener(event, this.#play);
        });
        this.#lock?.release();
        this.#lock = null;
        this.#video?.remove();
        this.#video = null;
    }

    #request = async () => {
        if (this.#lock || this.#asking || 'visible' !== document.visibilityState) {
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

    #play = async () => {
        if ('visible' !== document.visibilityState) {
            // A hidden page does not keep the screen on, and the sound track must not play on.
            this.#video?.pause();

            return;
        }

        this.#video ??= this.#createVideo();
        if (!this.#video.paused) {
            return;
        }

        try {
            await this.#video.play();
        } catch {
            // The browser refused (no action of the user yet): the next tap or key asks again.
        }
    };

    /*
     * The rules are in the source of the browsers (HTMLMediaElement::shouldDisableSleep of
     * WebKit, VideoWakeLock::ShouldBeActive of Chromium):
     * - Safari keeps the screen on only for a video that can make sound, so the video has a
     *   silent sound track and is not muted.
     * - Chrome accepts a video with sound, or a video that shows in a large part of the page.
     *   The video covers the page, so it obeys the two rules. It is transparent and takes no
     *   taps.
     * The video is in the body, out of the components, so a render does not remove it.
     */
    #createVideo() {
        const video = document.createElement('video');
        video.src = silentVideo;
        video.playsInline = true;
        video.setAttribute('playsinline', '');
        video.disableRemotePlayback = true;
        video.tabIndex = -1;
        video.setAttribute('aria-hidden', 'true');
        video.style.cssText = 'position:fixed;inset:0;width:100%;height:100%;opacity:0;pointer-events:none';
        // Safari lets the screen go off for a video at its end and for a video with the loop
        // attribute. Thus the video (3 seconds) moves back before its end, as NoSleep.js does.
        video.addEventListener('timeupdate', () => {
            if (video.currentTime > 2) {
                video.currentTime = 0;
            }
        });
        document.body.append(video);

        return video;
    }
}
