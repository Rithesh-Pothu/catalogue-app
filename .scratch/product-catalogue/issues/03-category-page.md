# 03: Category page

**What to build:** A visitor clicks a category link and sees the featured products in that category first, then the remaining products in that category in the order the source returned them. A category with no featured products still shows its products. An unknown category shows a not-found page.

**Blocked by:** 02: Home page shows the featured products.

**Status:** done

- [x] A category page lists its featured products first
- [x] The remaining products in that category follow in source order
- [x] A category with no featured products shows only its remaining products
- [x] An unknown category shows a not-found page
- [x] Backend request-level tests cover featured-first ordering, a category with no featured products, and an unknown category
