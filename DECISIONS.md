# Decisions and assumptions

Architecture decisions are in `docs/adr/`. Domain terms are in `GLOSSARY.md`.

## Assumptions

- Featured products are a fixed list of five, shipped with the app. There is no screen to change them. A missing product is skipped.
- A product belongs to exactly one category, using the source's categories.
- The home page shows the featured products and category links only. Category links are also in the header.
- A category page shows its featured products first, then the rest in source order.
- Search matches the title or the description, ignoring case. A blank term shows a prompt and no products.
  - The backend owns the rule: `GET /api/search?q=` trims the term, matches it literally anywhere in the title or description (including inside words), and returns no products for a blank or missing term. The search page also shows the prompt for a blank term without calling the backend, so a blank search costs no request. The page's idea of "blank" is only a shortcut for the same rule.
  - Results are in source order, and a featured product shows no rating, as everywhere. Submitting the same term again searches again.
  - Cut: after a search the keyboard focus stays in the header and the page title does not change; the status messages are announced to screen readers, the results heading is not focused.
- An enquiry is about one product, with name, email address and message. A valid enquiry emails the sales inbox and shows a Thank you. The enquirer gets no email. Invalid input stays on the form and sends nothing. A failed send shows an error, never a Thank you.
- There are no accounts and enquiries are not stored. The email is the only record.
- The product page shows image, title, price, description, category and Enquire. It shows the rating, except for featured products. The rating never chooses or orders products.
- Price is shown in US dollars. There is no cart or checkout.
- Unknown products and categories show a not-found page. If the product source is down, the page says products are unavailable and offers a retry.
- Product data will be cached for five minutes, and failures will not be cached. This is not built yet (ticket 07). Until then every page view reads the source.
- Product images are loaded by the browser straight from the image address the source gives us (Fake Store's image host). The product data itself goes only through our backend, but the image bytes do not. This is a deliberate trade-off against a strict reading of "all product data must go through your backend" and ADR 0001: the data and every API call go through our backend, while the image files are static assets on the source's host. We chose it to keep the proof of concept small. The way out is an image route on our backend that fetches and returns the image, so the browser never contacts the source; it would be a small ticket if a reviewer wants the strict reading.
- The featured product ids default to `3,5,11,12,18` (one or more from each Fake Store category) and are set with `FEATURED_PRODUCT_IDS`.
- Fake Store spells one category "jewelery". We show the source's own spelling.
- A category is known when the source has at least one product in it, and its name must match exactly, including case. A category page lists its featured products first (in the configured order), then the rest in source order. A product id repeated in `FEATURED_PRODUCT_IDS` counts once.
- Any address that is not one of our pages shows a not-found page. Under the Vite dev server, a URL with a malformed `%` escape (such as `/category/100%`) gets an empty 404 from the dev server before the app loads. That is dev-server behaviour that the app cannot change.

## Dropped or simplified

- No rate limit on the enquiry endpoint. A script could flood the sales inbox. Next step: limit enquiries per visitor.
- No paging. The source has about twenty products.
- No stored enquiries and no confirmation email to the enquirer.

## How the design grows

- The product source and the email provider are each behind an interface (ADR 0001, ADR 0002).
- The frontend only knows our own product shape: `id`, `title`, `price`, `description`, `category`, `image` and an optional `rating`. Source field names never reach the browser.
- Backend modules:
  - **Catalogue** (`featured`, `inCategory`, `search`, `product`, `categories`) holds the featured list, ordering, search, rating hiding, not-found and unavailable outcomes, and the cache. It reads products through the product source, which has a single method, `all()`. A `find(id)` was planned but dropped: the source has about twenty products and nothing needs a single-product lookup that `all()` cannot answer, so the smaller interface wins.
  - **Enquiries** (`submit`) holds validation and email composition. It gets the product from Catalogue and sends through the mail provider (`send`).
  - Controllers only translate requests and responses.
- The five featured product ids live in Laravel config, not in code.
- Each seam has two adapters: Fake Store and in-memory for products, for mail, a Laravel adapter (sends through whichever `MAIL_MAILER` is configured: SMTP to Mailpit locally) and an in-memory one for tests.
- An enquiry for an unknown product answers 404 before the input is validated, so nothing is checked or sent for a product that does not exist. The inbox address is `SALES_INBOX_EMAIL`.
- Tests cross the module interfaces, using the in-memory adapters.

## Next with another day

- Rate limit enquiries.
- Paging and a real admin for featured products.
- Store enquiries.
- A real email provider.
