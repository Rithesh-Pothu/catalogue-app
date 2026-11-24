# Decisions and assumptions

Architecture decisions are in `docs/adr/`. Domain terms are in `GLOSSARY.md`.

## Assumptions

- Featured products are a fixed list of five, shipped with the app. There is no screen to change them. A missing product is skipped.
- A product belongs to exactly one category, using the source's categories.
- The home page shows the featured products and category links only. Category links are also in the header.
- A category page shows its featured products first, then the rest in source order.
- Search matches the title or the description, ignoring case. A blank term shows a prompt and no products.
- An enquiry is about one product, with name, email address and message. A valid enquiry emails the sales inbox and shows a Thank you. The enquirer gets no email. Invalid input stays on the form and sends nothing. A failed send shows an error, never a Thank you.
- There are no accounts and enquiries are not stored. The email is the only record.
- The product page shows image, title, price, description, category and Enquire. It shows the rating, except for featured products. The rating never chooses or orders products.
- Price is shown in US dollars. There is no cart or checkout.
- Unknown products and categories show a not-found page. If the product source is down, the page says products are unavailable and offers a retry.
- Product data is cached for five minutes. Failures are not cached.
- Product images are loaded by the browser straight from the image address the source gives us (Fake Store's image host). The product data itself goes only through our backend, but the image bytes do not. This is a known gap against "all product data must go through your backend" and ADR 0001. The alternative is an image route on our backend that fetches and returns the image; it is not built yet.
- The featured product ids default to `3,5,11,12,18` (one or more from each Fake Store category) and are set with `FEATURED_PRODUCT_IDS`.
- Fake Store spells one category "jewelery". We show the source's own spelling.

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
- Each seam has two adapters: Fake Store and in-memory for products, SMTP (Mailpit) and in-memory for mail.
- Tests cross the module interfaces, using the in-memory adapters.

## Next with another day

- Rate limit enquiries.
- Paging and a real admin for featured products.
- Store enquiries.
- A real email provider.
