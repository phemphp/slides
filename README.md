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

## The presentation

Use the prod image:

```sh
make live
```

Open http://localhost:8080/. The SQLite database is in the `data` volume.
