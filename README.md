# Phem, live

An interactive slide presentation made with Phem. Read [PLAN.md](PLAN.md) for the design and
the phases.

## Development

```sh
docker compose up --build
```

- Audience view: http://localhost:8080/
- Speaker view: http://localhost:8080/speaker?key=dev (the key of `.env.dev`)

Keys of the speaker view: `→` `↓` `PageDown` `Space` `n` (next), `←` `↑` `PageUp` `p`
(previous), `Home` (first slide).

Add the slides in `src/Ui/Slide/Slides.phpm`. `phemc` and Tailwind build again when you save.

## The live presentation

Use the prod image. Give the URL that the phones can open (your LAN IP, or a tunnel):

```sh
SPEAKER_KEY=<a secret> PUBLIC_URL=http://192.168.1.20:8080 docker compose -f compose.prod.yaml up --build
```

The SQLite database is in the `data` volume.
