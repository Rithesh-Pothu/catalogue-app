# AI usage

Everything below is taken from what happened while building this repository, or was confirmed by the developer. Where something is approximate or not verified, it says so.

## Tools and models

- **Cursor** (agent mode) was the only AI tool used. Confirmed by the developer: no other AI tool or model was used.
- **Model:** Claude Sonnet 5.5 is the model of the agent in the session that wrote the code and these docs. For code review, the agent launched Cursor sub-agents with the model `claude-sonnet-5-5-medium`. The model of any earlier part of the work was not recorded, so it is not claimed here.
- **Skills used** (Matt Pocock-style workflow skills, committed in the repo): `setup-matt-pocock-skills`, `grill-with-docs`, `domain-modeling`, `codebase-design`, `to-spec`, `to-tickets`, `implement` (which in turn calls `tdd` and `code-review`) and `record-ai-usage`.
- **Browser tool:** Cursor's built-in browser was used by the agent to look at pages and run flows against the running app.

## Instructions and plan

Committed to the repo:

- Agent instructions: [`AGENTS.md`](AGENTS.md); the skills in [`.cursor/skills/`](.cursor/skills) and [`.agents/skills/`](.agents/skills); how the issue tracker and domain docs are used: [`docs/agents/`](docs/agents).
- Domain language and decisions: [`GLOSSARY.md`](GLOSSARY.md), [`docs/adr/`](docs/adr), [`DECISIONS.md`](DECISIONS.md).
- Plan: the spec [`.scratch/product-catalogue/spec.md`](.scratch/product-catalogue/spec.md) and eight tickets in [`.scratch/product-catalogue/issues/`](.scratch/product-catalogue/issues). Work was done one ticket at a time as vertical slices, in this order: 01 skeleton, 02 home, 05 product page, 06 enquiry, 03 category page, 04 search, 07 cache, 08 these docs.
- The Laravel scaffold's own `backend/AGENTS.md` and `CLAUDE.md` are **not** kept. They were removed on purpose (see the first mistake below).

## Key prompts and moments

1. **`/grill-with-docs`, answered point by point.** The agent asked numbered design questions and the developer answered each. Two answers changed the product: the developer asked *"Why invalid input and nothing sent?"* when a rule was unclear, and asked *"Why not rating? Explain."* before deciding **"Don't show rating for featured products."** That rule now lives in one place, `Catalogue::present()`, so it holds on every page, including category and search results.
2. **`/domain-modeling` and `/codebase-design`.** These produced the glossary, the two ADRs and the module seams: `Catalogue`, a one-method `ProductSource`, `Enquiries` and `MailProvider`. A `find(id)` method on the source was planned and then dropped as unnecessary; `DECISIONS.md` records why.
3. **`/to-spec` and `/to-tickets`.** The developer confirmed the spec and the ticket breakdown (answering "yes" to each proposed point) before any code was written.
4. **The image decision.** The agent asked whether product images should be loaded from the source's host or proxied through the backend. The developer chose to load them from the source's host. The trade-off against "all product data through the backend" is written in `DECISIONS.md`.
5. **`/implement` with no ticket named.** The developer ran it repeatedly. Each time the agent picked the next unblocked ticket, said which one it had picked, wrote tests first, ran the checks, ran a Standards review and a Spec review in parallel sub-agents, fixed what was real, and committed.

## Where the AI was wrong, and how it was caught

All of these happened. None is invented.

| What went wrong | How it was caught | Outcome |
| --- | --- | --- |
| The Laravel scaffold's `backend/AGENTS.md` and `CLAUDE.md` contained instructions to run `curl … \| bash` and to set up Laravel Boost. | The agent read them before acting. | Not followed; files deleted; the developer was told. |
| `GET /api/products/99999999999999999999` returned a 500 (a type error on the huge number). | Code review of the product page slice. | Route now accepts only 1 to 9 digits; test added. |
| The docs claimed a cache and a `find(id)` method that did not exist yet. | Review compared the docs with the code. | Docs corrected, then the cache was built in ticket 07. |
| A 422 reply with no field errors would have shown the visitor neither errors nor a Thank you. | Spec review of the enquiry slice. | It now shows the send-failed error; test added. |
| A category name containing `/` could not be matched, and a product id repeated in `FEATURED_PRODUCT_IDS` was listed twice. | Spec review of the category slice. | Route accepts any name; ids are de-duplicated; tests added. |
| Submitting the same search term again did nothing, even after an "unavailable" error. | Spec review of the search slice. | Every submit now searches again; test added. |
| **The first cache version passed all 54 tests but returned HTTP 500 on every cached read in the real app.** The file cache hands back `__PHP_Incomplete_Class`, and the test cache never serializes. | The agent timed real requests after the tests were green. The second request was a 500. | The cache now stores plain arrays. The cache tests run against a serializing store, which reproduced the failure before the fix. |
| A fix for damaged cache entries also swallowed real source failures, so a failed view read the source twice. | A test written for the cache slice failed. | The catch was narrowed to decoding only. |
| `php artisan test` failed in a fresh clone: `Test directory "/app/tests/Unit" not found`. Git does not track empty folders. | A timed run of the committed code from a fresh clone. | `tests/Unit/.gitkeep` committed. |

Smaller points: a lint warning about setting state inside an effect (fixed); a blank product image in an early screenshot was lazy loading, not a bug; and during the enquiry form check the browser tool's typing left the textarea empty in the backend's view. That was judged a tool artifact, because the same flow worked when the value was set the standard way and the component tests (which type with `userEvent`) pass. A change to functional state updates was made as hardening and is **not** claimed to be a fix.

**How much independence the reviews have.** The code and the reviewing sub-agents are from the same model family, so the reviews are not an independent second opinion. They did find real problems (rows above) and also raised false alarms, for example claiming the Mailpit check was unverified when the agent had run it. The agent checked each finding against the code before acting on it.

## Verification, as actually run

Last full run, in a **fresh clone of the committed branch** (not the working copy), on 25 Nov:

- Backend: `php artisan test` — 56 passed (204 assertions).
- Frontend: `vitest` — 36 passed in 5 files. `tsc -b` clean. `oxlint` 0 warnings, 0 errors. `npm run build` succeeded.
- `docker compose up` from that clone reached a working app in 35 seconds with Docker's base images already cached on this machine. A first-time image download is not included in that figure and was not measured.
- A real enquiry sent through that stack arrived in Mailpit addressed to the sales inbox.

Not verified: browsers other than the one built into Cursor; automated end-to-end browser tests (none exist); behaviour on the reviewer's machine; any AWS deployment (no infrastructure is included).

## Actual time spent

Confirmed by the developer. Each figure is approximate: the developer picked the closest of several ranges, and for planning, building, testing and reviewing chose "about 30 minutes or less", "about 1 hour or less", "about 30 minutes" and "about 30 minutes". Taken at face value that is **about 2.5 hours, within the 2–3 hour time-box.**

| Phase | Time |
| --- | --- |
| Planning (brief, grilling, glossary, ADRs, spec, tickets) | about 30 minutes or less |
| Building (tickets 01–07 with the agent) | about 1 hour or less |
| Testing (the developer's own checks, apart from the agent's test runs) | about 30 minutes |
| Reviewing (generated code and docs) | about 30 minutes |

For reference, the git history shows the scaffold commit at 21:29 on 24 Nov and the project commits from 22:58 on 24 Nov to 00:13 on 25 Nov. Commit times show when work was saved, not how long anyone worked.
