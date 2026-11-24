# Product catalogue

A proof of concept for a product catalogue: visitors browse featured products and categories, search, open a product and send an enquiry to the sales inbox. It is built as a foundation to grow, not a throwaway: the product source and the email provider each sit behind an interface, so replacing one means adding a class and changing one binding.

Challenge brief: `docs/CHALLENGE.pdf` (not committed). Decisions and assumptions: [`DECISIONS.md`](DECISIONS.md). AI usage notes: [`AI_USAGE.md`](AI_USAGE.md).

## Stack and why

| Layer | Choice | Why |
| --- | --- | --- |
| Client | React 19 + TypeScript (Vite) | The brief prefers TypeScript. The client only knows our own product shape, so its types are the contract with the backend. |
| Backend | PHP 8.4 + Laravel 13 | Validation, mail, caching, HTTP client and request-level testing are built in, which keeps the code small and conventional. |
| Product source | [Fake Store API](https://fakestoreapi.com), behind a `ProductSource` interface | The brief allows it. Replacing it is one new class and one binding ([ADR 0001](docs/adr/0001-products-only-through-our-backend.md)). |
| Email | Laravel mail behind a `MailProvider` interface; Mailpit locally | No paid account needed, and the sales inbox can be read in a browser ([ADR 0002](docs/adr/0002-email-sent-through-a-replaceable-provider.md)). |

The browser never calls the Fake Store API. Every product request goes to our backend at `/api/...`. One exception, recorded in `DECISIONS.md`: product *images* are loaded by the browser from the source's image host.

## Run it

You need Docker (with Compose) and nothing else: no PHP, Node or accounts.

```sh
docker compose up
```

The first start installs dependencies inside the containers and takes a minute or two (about 35 seconds on a machine with Docker's base images already cached; downloading the images the first time takes longer). Wait until the frontend log says it is ready, then open:

| What | Address |
| --- | --- |
| The catalogue | <http://localhost:5173> |
| **Sales inbox (Mailpit)** | <http://localhost:8025> |
| The API directly | <http://localhost:8000/api/health> |

To see an enquiry arrive: open any product, click **Enquire**, fill in the form and send it, then open Mailpit. The email is addressed to the sales inbox (`sales@catalogue.test`) with the enquirer as `Reply-To`.

Stop with `Ctrl+C`; `docker compose down` removes the containers.

If a port is taken, free `5173`, `8000` or `8025` and start again.

### Configuration

Everything is configured in `backend/.env.example`. `docker compose up` copies it to `backend/.env` on first start, so there is nothing to set up. The file contains no secrets, and `.env` is git-ignored.

| Variable | Default | Meaning |
| --- | --- | --- |
| `FEATURED_PRODUCT_IDS` | `3,5,11,12,18` | The five featured products, in display order |
| `SALES_INBOX_EMAIL` | `sales@catalogue.test` | Where enquiries are delivered |
| `CATALOGUE_CACHE_SECONDS` | `300` | How long product data is reused (`0` turns the cache off) |
| `FAKE_STORE_URL` / `FAKE_STORE_TIMEOUT` | `https://fakestoreapi.com` / `5` | The product source |
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT` | `smtp`, `mailpit`, `1025` | How email is sent |

> **Development setup only.** `docker compose up` runs the Laravel development server and the Vite dev server with the source mounted into the containers. It is meant for review and local work, not for deployment. No infrastructure is included, so nothing here is tied to AWS or any other host.

## Tests and checks

With the stack running (`docker compose up`), in another terminal:

```sh
docker compose exec backend php artisan test      # backend: request-level tests
docker compose exec frontend npm test             # frontend: component tests
docker compose exec frontend npx tsc -b           # type check
docker compose exec frontend npm run lint         # lint
docker compose exec frontend npm run build        # production build
```

Backend tests run through the HTTP API with an in-memory product source and an in-memory mail provider, plus one test that reads a recorded Fake Store response. Frontend tests stub `fetch`, the network boundary, and drive the real components.

## How it fits together

```
React app  ──/api──▶  Laravel backend ──▶ ProductSource  ──▶ Fake Store (or any other source)
                         │
                         └─ Catalogue (featured, category, search, product, 5-minute cache)
                         └─ Enquiries ──▶ MailProvider ──▶ SMTP / Mailpit (or any other provider)
```

| Route | Purpose |
| --- | --- |
| `GET /api/health` | `{"status":"ok"}`, used by Docker's health check |
| `GET /api/featured-products` | The featured products, without ratings |
| `GET /api/categories` | Category names |
| `GET /api/categories/{category}/products` | Featured products first, then the rest in source order |
| `GET /api/search?q=` | Products whose title or description contains the term, ignoring case |
| `GET /api/products/{id}` | One product; `404` if there is none (ids are 1 to 9 digits, anything else is a `404`) |
| `POST /api/products/{id}/enquiries` | Emails the sales inbox and returns `201`; `422` with field errors if invalid, `404` for an unknown product, `502` if the email could not be sent |

Any route that reads products returns `503` with "Products are unavailable right now." when the product source cannot be read; unknown categories return `404`.

Where to look: `backend/app/Catalogue` (products and rules), `backend/app/Enquiries` (enquiries and mail), `backend/tests/Feature` (tests), `frontend/src/api/client.ts` (the only place that talks to the backend), `frontend/src/pages` and `frontend/src/components` (the UI). Domain terms are defined in [`GLOSSARY.md`](GLOSSARY.md).

## Known limits

Listed with reasons and next steps in [`DECISIONS.md`](DECISIONS.md): no rate limit on enquiries, enquiries are not stored, no paging, images come from the source's host, and a damaged or expired cache entry makes one visitor wait for the source.
