# 02: Home page shows the featured products

**What to build:** A visitor opens the home page and sees the five featured products and links to the categories. The header shows the category links on every page. If products can't be loaded, the page says products are unavailable and offers a retry, not an empty catalogue. This slice introduces the product source seam (ADR 0001) and the Catalogue module.

**Blocked by:** 01: Project skeleton runs with one command.

**Status:** done

- [x] The home page shows the five featured products, chosen by product ids in configuration
- [x] A featured product missing from the source is skipped and the page still loads
- [x] A featured product shows no rating
- [x] The home page and the header show category links
- [x] The backend returns our own product shape and never Fake Store's field names
- [x] The product source has a Fake Store adapter and an in-memory adapter, and the Fake Store adapter has one test against a recorded sample response
- [x] When the source is down, the page says products are unavailable and offers a retry
- [x] Backend request-level tests cover the home flow, a missing featured product, and the source being down
