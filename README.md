# Phem, live

A slide presentation made with Phem. Read [PLAN.md](PLAN.md) for the design and the phases.

Run `make` to see the common tasks (start the dev server, run a console command, load the
fixtures, run the tests, open a tunnel).

## Development

```sh
make up
```

Open http://localhost:8080/. The presentation uses the dev server too: the deck shows the debug
toolbar of Symfony.

Docker publishes the port to this machine only, so the network cannot open the dev server. A
phone opens the slides through a tunnel (see [Phone](#phone)). The toolbar also shows through the
tunnel, but the profiler behind it opens only on `localhost`: the tunnel gets a 404 for it (see
the `Caddyfile`).

Keys of the deck: `→` `↓` `PageDown` `Space` `n` (next), `←` `↑` `PageUp` `p`
(previous), `Home` (first slide).

Put each slide in its own file in `src/Ui/Slide/`, named as its component (for example
`Cover.phem`). Then add it to the list in `src/Ui/Slide/Slides.phem`. The list sets the order. `phemc` and Tailwind build
again when you save.

## Phone

A phone opens the slides through a public URL: a tunnel from the internet to the dev server. The
phone needs only the internet, so it also works in a network that blocks the traffic between its
clients (guest networks, venue networks).

```sh
make tunnel                                            # prints https://<name>.trycloudflare.com
make up PHONE_URL=https://<name>.trycloudflare.com     # in a second terminal
```

`make tunnel` needs [cloudflared](https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/).
All persons who have the URL can open the slides while the tunnel runs. Each start of this tunnel
gives a new URL.

For a URL that stays the same, use a named tunnel with a domain of your Cloudflare account. Do
the setup one time:

```sh
cloudflared tunnel login                                      # select the zone of the domain
cloudflared tunnel create phem-slides-local
cloudflared tunnel route dns phem-slides-local slides.phem.dev
```

Then start the tunnel and the server:

```sh
make tunnel TUNNEL=phem-slides-local
make up PHONE_URL=https://slides.phem.dev                     # in a second terminal
```

Click the phone button of the deck to show the QR code of this URL. To close the QR code, click
its close button, press `Esc`, or click the phone button again. On the phone, the same button turns on the touch navigation: swipe to the
left for the next slide, and to the right for the previous slide.

With the touch navigation on, a phone in portrait shows the slide at the top of the screen and
the speaker notes of the slide below it. A swipe on the notes also moves the deck, and a long text
scrolls. A screen with a mouse never shows the notes. Write the notes of a slide in the
`speakerNotes` slot of its layout, for example `<CodeSlide.speakerNotes>`.

The screen of the phone stays on while the deck shows, after the first tap. On plain HTTP, the
deck plays a silent video that you cannot see to keep the screen on, so the phone can show the
page in its media controls and can stop other audio.
