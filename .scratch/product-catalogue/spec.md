Status: ready-for-agent

# Product catalogue

Vocabulary is from `GLOSSARY.md`. Decisions and assumptions are in `DECISIONS.md`. Architecture is in `docs/adr/0001` and `docs/adr/0002`.

## Problem Statement

An entrepreneur needs an interactive proof of concept of a product catalogue so they can firm up their vision. Visitors must be able to browse featured products and categories, search, view a product, and send an enquiry to the sales inbox. The product data source, the look and feel, and the email provider will all change, so the proof of concept must be a foundation that grows and is not thrown away.

## Solution

A React TypeScript web app talks only to a Laravel backend. The backend reads products from Fake Store through a swappable product source and sends enquiry email through a swappable mail provider. Locally everything runs with one command, and the sales inbox is read in Mailpit.

## User Stories

1. As a visitor, I want to see the five featured products on the home page, so that I can find what the business recommends.
2. As a visitor, I want the home page to show category links, so that I can start browsing by category.
3. As a visitor, I want category links in the header on every page, so that I can switch category from anywhere.
4. As a visitor, I want a category page to list the featured products in that category first, so that I see the recommended ones before the rest.
5. As a visitor, I want a category page to list the remaining products in that category, so that I can see everything on offer.
6. As a visitor, I want a category with no featured products to still show its products, so that no category looks empty.
7. As a visitor, I want to enter a search term and click search, so that I can find a specific product.
8. As a visitor, I want search to match a product's title or description, ignoring case, so that I find products by what they are or what they do.
9. As a visitor, I want a blank search to show a prompt and no products, so that I'm not shown an unfiltered list as if it were a result.
10. As a visitor, I want a search with no matches to say nothing was found, so that I know the search worked.
11. As a visitor, I want to click a product tile to see its details page, so that I can learn more.
12. As a visitor, I want the product page to show image, title, price, description, category and an Enquire button, so that I can decide whether to ask about it.
13. As a visitor, I want to see a rating on a non-featured product's page, so that I have a sense of its quality.
14. As a visitor, I want a featured product to show no rating anywhere, so that Featured is the only highlight.
15. As a visitor, I want prices shown in US dollars with no cart, so that I'm not led to expect checkout.
16. As an enquirer, I want to click Enquire on a product and see a form, so that I can ask about that product.
17. As an enquirer, I want to enter my name, email address and a message, so that the sales team can reply.
18. As an enquirer, I want to see which field is wrong when I submit invalid input, so that I can fix it without losing what I typed.
19. As an enquirer, I want nothing sent when my input is invalid, so that the sales inbox only gets usable enquiries.
20. As an enquirer, I want to see a Thank you message after a successful submit, so that I know the enquiry was received.
21. As an enquirer, I want an error and not a Thank you when sending fails, so that I know to try again.
22. As an enquirer, I want to need no account, so that asking is quick.
23. As a salesperson, I want each enquiry to arrive as one email in the sales inbox, so that I can follow it up.
24. As a salesperson, I want the email to name the product and include the enquirer's name, email address and message, so that I can reply with context.
25. As a visitor, I want an unknown product or category to show a not-found page, so that I'm not left on a broken page.
26. As a visitor, I want a clear unavailable message with a retry when products can't be loaded, so that I'm not shown an empty catalogue.
27. As a visitor, I want a missing featured product to be skipped quietly, so that the home page never breaks.
28. As the entrepreneur, I want the app not to call Fake Store from the browser, so that the data source can change without touching the client.
29. As the entrepreneur, I want to change which five products are featured by editing configuration, so that I can adjust without code changes.
30. As a developer, I want to swap the product source by adding one implementation, so that the catalogue can move off Fake Store.
31. As a developer, I want to swap the email provider by adding one implementation, so that real email can replace Mailpit.
32. As a reviewer, I want to run everything with one command and read the sales inbox in Mailpit, so that I can try the app in under ten minutes without paid accounts.

## Implementation Decisions

- The React app calls only the Laravel backend. It never calls Fake Store (ADR 0001).
- The backend returns our own product shape: `id`, `title`, `price`, `description`, `category`, `image` and an optional `rating`. Source field names never reach the browser.
- **Catalogue module** with `featured`, `inCategory`, `search`, `product` and `categories`. It owns:
  - the featured list and skipping missing products;
  - featured-first ordering in a category, then source order;
  - title-or-description case-insensitive search and the blank-term rule;
  - hiding the rating for featured products everywhere;
  - the not-found and source-unavailable outcomes;
  - a five-minute cache of product data, with failures not cached, kept internal to the module.
- **Product source seam** with a single method, `all()`. Categories, search and single-product lookups are derived from it, because the source has only about twenty products. Adapters: Fake Store and in-memory.
- **Enquiries module** with `submit`. It owns validation of name, email address and message, getting the product from Catalogue, and composing the email. It returns sent, invalid with field errors, or failed. A missing product is not-found.
- **Mail provider seam** with `send`. Adapters: SMTP (Mailpit locally) and in-memory (ADR 0002).
- Controllers only translate requests and responses and hold no rules.
- The five featured product ids are Laravel configuration.
- Enquiries are not stored and no confirmation email goes to the enquirer. There is no rate limiting on the enquiry endpoint (recorded as a cut).
- Pages: home, category, search results, product, and enquiry form, plus not-found and unavailable states. Category links appear in the header and on the home page.
- `docker compose up` starts the React app, the backend and Mailpit. Configuration comes from an example env file and no secrets are committed.
- Laravel and React TypeScript are the chosen stack. The README states the choice and why.

## Testing Decisions

- A good test checks external behaviour through an interface and survives refactoring. It does not assert implementation details.
- **Main seam: the backend HTTP API**, with the in-memory product source and in-memory mailer bound in tests. Each acceptance flow is a request-level test: home, category, search, product, enquiry. The same seam covers:
  - the rating hidden on featured products;
  - a missing featured product skipped;
  - the blank search;
  - unknown product and category giving not-found;
  - source down giving unavailable;
  - an invalid enquiry sending nothing;
  - a failed send never reporting success.
- **Fake Store adapter:** one small test that maps a recorded sample response into our product shape.
- **Frontend:** a few component tests with the API client mocked. They cover the enquiry form states (invalid, sending, thank you, error) and the unavailable and not-found pages. Layout is not tested.
- Catalogue and Enquiries have no tests of their own; they are exercised through the API.
- There is no prior art. This is a new codebase.

## Out of Scope

- Accounts, a cart, checkout and payments.
- Paging.
- A screen to change featured products.
- Stored enquiries and a confirmation email to the enquirer.
- Rate limiting the enquiry endpoint.
- A real email provider.
- Final look and feel.

## Further Notes

- The challenge says "top rated" for the home page and for each category. This spec reads both as Featured product, and a category with none shows only its remaining products. This is an assumption to defend in the interview.
- Assumptions, cuts and next steps are kept in `DECISIONS.md`.
- AI usage is recorded in `AI_USAGE.md` only from what actually happened.
