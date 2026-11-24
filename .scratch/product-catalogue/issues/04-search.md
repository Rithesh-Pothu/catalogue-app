# 04: Search

**What to build:** A visitor types a term in the search bar, clicks the search button, and sees only the products whose title or description contains the term, ignoring case. A blank term shows a prompt and no products. A term with no matches says nothing was found.

**Blocked by:** 02: Home page shows the featured products.

**Status:** done

- [x] The search bar and button are available on every page
- [x] Results include a product when the term appears in its title
- [x] Results include a product when the term appears only in its description
- [x] Matching ignores case
- [x] A blank term shows a prompt and no products
- [x] A term with no matches shows a nothing-found message
- [x] Backend request-level tests cover title match, description match, case, blank term and no match
