## Product catalogue: React + Laravel proof of concept

A catalogue where visitors see five featured products, browse categories, search, open a product and send an enquiry that arrives in the sales inbox. It is built as a foundation to grow rather than a throwaway.

**Run it:** `docker compose up`, then open <http://localhost:5173>. Read the sales inbox in Mailpit at <http://localhost:8025>. No accounts, no secrets, no manual setup. Details are in the [README](https://github.com/Rithesh-Pothu/catalogue-app/blob/feature/product-catalogue/README.md).

### Choices and why

- **React + TypeScript, Laravel.** Laravel gives validation, mail, caching, an HTTP client and request-level testing out of the box, so the code stays small and conventional.
- **Everything product-related goes through the backend.** The browser only calls `/api/...`; it never calls Fake Store (ADR 0001). One recorded exception: product *images* load from the source's image host. That is a deliberate trade-off against a strict reading of the brief, with the way out written in `DECISIONS.md`.
- **Two seams so the things that will change can change.** `ProductSource` (one method, `all()`) hides Fake Store; `MailProvider` hides how email is sent (ADR 0002). Replacing either means adding a class and changing one binding. Fake Store and Laravel/SMTP-to-Mailpit are the current adapters, and each seam also has an in-memory one for tests.
- **Two backend modules own the rules.** `Catalogue` (featured, category, search, product, 5-minute cache) and `Enquiries`; controllers only translate requests and responses.
- **Rule worth a second look:** featured products never show a rating, anywhere (product page, category page, search). It is implemented once, in `Catalogue::present()`, after a decision made during design.

### Assumptions I made where the brief was unclear

Full list in [`DECISIONS.md`](https://github.com/Rithesh-Pothu/catalogue-app/blob/feature/product-catalogue/DECISIONS.md). The main ones: the featured five are configured, not edited in a screen (`FEATURED_PRODUCT_IDS`); search matches title or description, ignoring case, and a blank term shows a prompt instead of everything; a category page shows its featured products first; the enquirer gets no confirmation email; an enquiry that fails to send shows an error, never a Thank you.

### What I cut or simplified, and why

No rate limit on enquiries, no stored enquiries, no paging, no admin screen, no real email provider, no infrastructure (the compose setup runs development servers and is for review only). Each has a reason and a next step in `DECISIONS.md`.

### How it was tested

Tests go through the HTTP API with in-memory adapters, plus one test against a recorded Fake Store response; the frontend tests stub `fetch` and drive the real components. Last run in a **fresh clone of this branch**: backend 56 passed (204 assertions), frontend 36 passed, type check clean, lint 0 warnings, production build succeeded. A real enquiry sent through that stack arrived in Mailpit. Not covered: automated end-to-end browser tests, browsers other than Cursor's built-in one.

### Things worth knowing

- The cache was first built with objects in the cache; it passed every test and returned a 500 in the real app, because real cache stores do not turn data back into objects. It now caches plain arrays and the tests run against a serializing store.
- A fresh-clone run caught that `php artisan test` failed without `tests/Unit`; fixed.
- Under the Vite dev server, a URL with a malformed `%` escape returns an empty 404 before the app loads. This is dev-server behaviour.

### AI usage and time

See [`AI_USAGE.md`](https://github.com/Rithesh-Pothu/catalogue-app/blob/feature/product-catalogue/AI_USAGE.md): tools and models, the instruction files and plan, five key moments, the mistakes the AI made and how each was caught, and the time split (developer-confirmed and approximate).
