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
