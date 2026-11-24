# 07: Cache product data for five minutes

**What to build:** Repeated page views stop calling Fake Store each time. Product data is reused for five minutes, a failed fetch is never cached, and callers of the Catalogue see no difference.

**Blocked by:** 02: Home page shows the featured products.

**Status:** done

- [x] Product data is reused for five minutes
- [x] A failed fetch is not cached, so the next view tries the source again
- [x] The cache is internal to the Catalogue module and changes none of its behaviour
- [x] A test shows the source is called once for repeated views within the time and called again after it expires
