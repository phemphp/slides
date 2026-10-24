# Phem, live: the plan

One interactive slide presentation, made with [Phem](../phem) on Symfony and FrankenPHP. The
second goal is to test Phem on a real app. Write each problem with Phem in the
[friction log](#friction-log).

## Two views

| View | URL | Can do |
|---|---|---|
| Speaker (the projector) | `/speaker` (open `/speaker?key=<SPEAKER_KEY>` one time) | Move the slides with the keyboard or a clicker. All the screens follow. |
| Audience (the phones, from the QR code) | `/` | See the current slide. Use the dynamic parts of the current slide. Cannot move the slides. |

## How it works

- `presentation` (SQLite, one row) keeps the index of the current slide.
- `App\Ui\Slide\Slides` is the ordered list of the slides. A slide is markup. It renders only
  when it is the current slide.
- `<Deck>` reads the current slide in `mount()`. It has no `#[State]`, so each render reads the
  database again.
- On the speaker view, the `deck-keys` Stimulus controller changes a key into a click on a hidden
  button. The click is a normal Phem event (`next`, `previous`, `first`).
- `Navigator::go()` saves the index and pushes `refresh('deck')` to all the `#[Subscribe]`
  pages. Each browser then renders its deck again through the Mercure hub of FrankenPHP.
- `SpeakerAccess` gives the speaker access through the session. Without it, `/speaker` is a 404.
  The handlers check the access again.
- `<Deck>` provides the `Role` (speaker or audience) as a context. A dynamic slide reads it with
  `#[Context] Role $role` and shows the part for that role.

## Phases

### Phase 1: Foundation (done)

- Symfony 8.1 + Phem 0.1.2, as in the getting-started guide of Phem.
- Docker: `compose.yaml` (dev, watch mode) and `compose.prod.yaml` (the live presentation, prod
  image, database in a volume).
- SQLite (Doctrine ORM, migrations) and Tailwind CSS 4 (symfonycasts/tailwind-bundle).
- The speaker and audience views, keyboard control, real-time sync, and the QR code
  (`<JoinQr />`).
- The slides of the talk "Cut the wires" (20 slides, ported from the slide artifact), and two
  extra slides for reference: the QR code and the design system sheet.

### Phase 2: Slide kit

- Layout components: title, section, bullets, code (syntax highlight on the server), image, two
  columns, quote.
- Steps in a slide (show the items one by one). Keep the step with the slide index, so the
  audience follows the steps too.
- Theme: fonts, colors, the transition between the slides.
- Optional: a presenter view (notes, next slide, timer) on the laptop, apart from the projector.

### Phase 3: Dynamic slides

- Decide the interactions of the talk (to do: you give the list).
- Make one component for each interaction. It reads `Role` to show the audience part or the
  speaker part.
- Keep the data of the audience in SQLite. Push the changes with `Streams::refresh()`.
- Add `#[RateLimit]` to the handlers of the audience.

### Phase 4: Live hardening

- Fix the lost push: a push between the page render and the Mercure connection does not arrive.
  Refresh the deck when the connection opens.
- HTTPS for the phones (a tunnel such as Cloudflare Tunnel, or Caddy with a domain).
- A load test: about 200 phones on the hub, and a burst of audience events.
- A reset command (current slide 0, delete the data of the audience).
- A fallback: a static export of the slides (PDF or HTML).

### Phase 5: Review

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
