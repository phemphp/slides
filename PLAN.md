# Phem, live: the plan

One slide presentation, made with [Phem](../phem) on Symfony and FrankenPHP. The second goal is
to test Phem on a real app. Write each problem with Phem in the [friction log](#friction-log).

The presentation is not interactive: there is one view (`/`), on the projector. The audience
does not join on their phones.

## How it works

- `presentation` (SQLite, one row) keeps the index of the current slide.
- `App\Ui\Slide\Slides` is the ordered list of the slides. A slide is markup. It renders only
  when it is the current slide.
- `<Deck>` reads the current slide in `mount()`. It has no `#[State]`, so each render reads the
  database again.
- The `deck-keys` Stimulus controller changes a key into a click on a hidden button. The click
  is a normal Phem event (`move`).
- `<DeckNav>` saves the slide, then refreshes the deck.

## Phases

### Phase 1: Slide kit

- Layout components: title, section, bullets, code (syntax highlight on the server), image, two
  columns, quote.
- Theme: fonts, colors, the transition between the slides.
- Optional: a presenter view (notes, next slide, timer) on the laptop, apart from the projector.

### Phase 2: Hardening

- A reset command (current slide 0).
- A fallback: a static export of the slides (PDF or HTML).

### Phase 3: Review

- Read the friction log. Report the problems and the good points to the Phem project.

## Friction log

Write each problem with Phem here: the date, what happened, and the work-around.

- 2026-10-24: A class in a `.phpm` file is not a service (the service loader reads only
  `.php` files). Before `phemc build`, the class does not exist, so a service definition breaks
  `composer install` in the prod image. Work-around: `Navigator` makes `Slides` itself.
- 2026-10-24: A push that arrives before the page opens its Mercure connection is lost. The
  page shows an old slide until the next push.
- 2026-10-24: The Mercure `publisher_jwt` directive needs an `issuer` block in the new Mercure
  versions. The app does not need it: `mercure_publish()` publishes with no key.
- 2026-10-24: A test that mounts a `#[Transactional]` component with an entity prop
  (`mount(LikeButton::class, ['post' => $post])`) shows the new value, but saves nothing. The
  event resets the services, so the prop is a detached entity and the flush ignores it. No
  error tells you. Work-around: mount the parent that loads the entity (`Example`).
- 2026-11-24: `composer require phem/ui-base` installs v0.1.0 when the app has `phem/phem` ^0.7:
  each minor version of the package needs a newer `phem/phem`. Work-around: require the two
  packages together (`composer require phem/ui-base:^0.8 phem/phem:^0.10 -W`).
- 2026-11-24: With phem/ui-base installed, each asset gives a 500 error in dev. The bundle adds
  its `assets/` folder to AssetMapper, and `theme.css` starts with a comment that has
  `@import "tailwindcss";`. `CssAssetUrlCompiler` does not skip a comment that starts at offset 0
  (`elseif ($start)` is false for `0`), and `missing_import_mode: strict` throws. Work-around:
  an `excluded_patterns` entry for `theme.css` in `config/packages/asset_mapper.yaml`.
- 2026-11-24: The theme of phem/ui-base sets `--accent` on `:root`, and the design system of
  the slides has its own `--accent`. `@import "…/theme.css" layer(theme)` does not build: the
  `@source` of the theme is then nested. Work-around: `--accent: revert-layer` on `:root`, and a
  `.ph-ui` class that gives the `--accent` of the theme to the components.
- 2026-11-24: `<TabsIndicator>` and the popup of `<NavigationMenu>` use sizes from
  `getBoundingClientRect()`. A slide has a `scale`, so the indicator has the wrong width and the
  popup opens at the wrong place. The popups in the top layer (select, menu, popover, tooltip,
  dialog, drawer) are correct. Work-around: no indicator (classes on the active tab), and no
  `<NavigationMenu>` on the slide.
