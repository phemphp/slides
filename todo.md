# TODO

Ideas for the presentation.

## Features of phem.dev that the deck does not show

I compared the phem.dev documentation with the 21 slides. The deck covers components, pages, services, the document, `#[Transactional]`, `#[Broadcast]`, `RedirectTo`, the compiler, the event flow, the editor support, and the packages. These features are not in the deck. All of them are in the installed version (0.7.0).

### Add these first

| # | Feature | Why it helps the talk | Key API |
|---|---|---|---|
| 1 | **Forms and live validation** | Each Symfony developer writes forms. Symfony constraints on state show errors while the user types. | `phem:bind`, `#[Validated]`, `phem:error`, `#[MapForm]` |
| 2 | **Security** | The state goes to the browser, so the audience will ask if it is safe. The state has a signature, and `#[IsGranted]` works on one handler. | signed state, `#[IsGranted]`, `#[RateLimit]` |
| 3 | **Tests without a browser** | A PHPUnit test mounts a component, clicks, and asserts the HTML. This is a strong contrast with JavaScript test tools. | `InteractsWithPhem`, `mount()`, `click()`, `assertSee()` |
| 4 | **Streaming and errors** | A slow part of the page shows a fallback first, and the content arrives later. This is the answer to "the server is slow". | `<Suspense>`, `<ErrorBoundary>` |
| 5 | **URL state** | Filters and page numbers stay in the URL, so the user can share the link. One attribute does it. | `#[Url]` |

The security slide fits best after `EventFlow`, because that slide shows the state in transit. The other four fit in the feature group, after `RedirectTo`.

### Good second choices

- **Coding agents**: `phemc mcp` and the Claude Code plugin. This fits as one more node on the `EditorSupport` slide.
- **Profiler panel**: the Symfony profiler shows the component tree, the state size, and the event passes.
- **Feeds**: `Phem\Feed` with `onVisible` gives infinite scroll, and the items stay out of the state.
- **Context**: `#[Provide]` and `#[Context]` pass a value to deep children without props.
- **Failed events**: automatic retries and an offline queue. This balances the "each event is a round trip" item on the `TradeOffs` slide.

### Lower priority

- `#[ErrorPage]` for 404 and 500 pages.
- Uploads: chunked, direct to S3, with progress.
- Optimistic updates with the `phem-optimistic` controller.
- Component replacement: `#[Component(replaces: ...)]`.
- Polling and shortcuts: `onPoll`, `phem:shortcut`, `phem:debounce`.

### Limits of this check

- The fetch read 33 pages. It read only a part of `/controllers/`, which is a very long page about the UI controllers.
- I did not change any file.

My recommendation is to add items 1, 2 and 3. They answer the three questions that a Symfony audience asks first: forms, safety, and tests.
