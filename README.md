# Phem, live

An interactive slide presentation made with Phem. Read [PLAN.md](PLAN.md) for the design and
the phases.

Run `make` to see the common tasks (start the dev server, run a console command, load the
fixtures, run the tests, start the live presentation).

## Development

```sh
make up
```

- Audience view: http://localhost:8080/
- Speaker view: http://localhost:8080/speaker?key=dev (the key of `.env.dev`)

Keys of the speaker view: `→` `↓` `PageDown` `Space` `n` (next), `←` `↑` `PageUp` `p`
(previous), `Home` (first slide).

Put each slide in its own file in `src/Ui/Slide/`, with its number as the prefix (for example
`01-Cover.phpm`). Then add it to the list in `src/Ui/Slide/Slides.phpm`. `phemc` and Tailwind build
again when you save.

### Development with the tunnel

To test on phones during dev, use the tunnel of the live presentation (see
[HTTPS with a Cloudflare Tunnel](#https-with-a-cloudflare-tunnel)). Its route goes to
`http://app:8080`, and the dev app has the same service name. Stop the live presentation first
(`make live-down`): two connectors with the same token share the traffic.

```sh
TUNNEL_TOKEN=<the token> make up-tunnel
```

Open `https://slides.example.com/speaker?key=dev`. The QR code shows the URL of the request, so
open the speaker view through the tunnel, not through `localhost`. `make down` stops the app and
the tunnel.

## The live presentation

Use the prod image. Give the URL that the phones can open (your LAN IP, or a tunnel):

```sh
SPEAKER_KEY=<a secret> PUBLIC_URL=http://192.168.1.20:8080 make live
```

The SQLite database is in the `data` volume.

### HTTPS with a Cloudflare Tunnel

Use a tunnel when the phones are not on your LAN, or when the venue Wi-Fi isolates the clients.
You need a Cloudflare account with a domain. A quick tunnel (`trycloudflare.com`) does not work:
it does not support Server-Sent Events, so the slides do not follow the speaker.

1. In the Cloudflare dashboard, go to Networking > Tunnels. Create a tunnel. On the install
   page, select Docker and copy the token (the long `eyJ…` string after `--token`). Do not run
   the command: `compose.prod.yaml` runs `cloudflared`.
2. On the Routes tab of the tunnel, add a route of the type "Published application", for
   example `slides.example.com`. Set the service URL to `http://app:8080`.
3. Start the app and the tunnel:

```sh
SPEAKER_KEY=<a secret> TUNNEL_TOKEN=<the token> PUBLIC_URL=https://slides.example.com make live-tunnel
```

Open `https://slides.example.com/speaker?key=<a secret>` on the laptop. The QR code shows the
HTTPS URL.
