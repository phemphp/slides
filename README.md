# Phem, live

A slide presentation made with Phem. Read [PLAN.md](PLAN.md) for the design and the phases.

Run `make` to see the common tasks (start the dev server, run a console command, load the
fixtures, run the tests, start the presentation).

## Development

```sh
make up
```

Open http://localhost:8080/.

Keys of the deck: `→` `↓` `PageDown` `Space` `n` (next), `←` `↑` `PageUp` `p`
(previous), `Home` (first slide).

Put each slide in its own file in `src/Ui/Slide/`, named as its component (for example
`Cover.phem`). Then add it to the list in `src/Ui/Slide/Slides.phem`. The list sets the order. `phemc` and Tailwind build
again when you save.

## Phone

`make up` and `make live` find the address of this machine in the current network (`bin/host-ip`)
and print the URL for a phone, for example `http://192.168.1.20:8080/`. The phone must be in the
same network.

Click the phone button of the deck to show the QR code of this URL. To close the QR code, click
its close button, press `Esc`, or click the phone button again. On the phone, the same button turns on the touch navigation: tap the left or the right
half of the screen.

The screen of the phone stays on while the deck shows, after the first tap. On plain HTTP, the
deck plays a silent video that you cannot see to keep the screen on, so the phone can show the
page in its media controls and can stop other audio.

After a change of network, run `make up` (or `make live`) again: the QR code gets the new
address. If the address is wrong, set it: `make up HOST_IP=192.168.1.20`.

## The presentation

Use the prod image:

```sh
make live
```

Open http://localhost:8080/. The SQLite database is in the `data` volume.
