<?php

namespace App\Catalogue;

use Illuminate\Contracts\Cache\Repository as Cache;
use Throwable;

/**
 * What the shop window shows. Controllers ask this module and never apply
 * catalogue rules themselves.
 */
final class Catalogue
{
    private const CACHE_KEY = 'catalogue.products';

    /** @var list<int> */
    private readonly array $featuredProductIds;

    /**
     * @param  list<int>  $featuredProductIds  the featured products, in display order
     * @param  int  $cacheSeconds  how long product data from the source is reused
     */
    public function __construct(
        private readonly ProductSource $source,
        array $featuredProductIds,
        private readonly Cache $cache,
        private readonly int $cacheSeconds,
    ) {
        // A product listed twice is still one featured product.
        $this->featuredProductIds = array_values(array_unique($featuredProductIds));
    }

    /**
     * The featured products, in the configured order. A featured id the
     * source does not have is skipped.
     *
     * @return list<Product>
     */
    public function featured(): array
    {
        $byId = [];
        foreach ($this->products() as $product) {
            $byId[$product->id] = $product;
        }

        $featured = [];
        foreach ($this->featuredProductIds as $id) {
            if (isset($byId[$id])) {
                $featured[] = $this->present($byId[$id]);
            }
        }

        return $featured;
    }

    /**
     * One product, for its own page.
     *
     * @throws ProductNotFound when there is no product with that id
     */
    public function product(int $id): Product
    {
        foreach ($this->products() as $product) {
            if ($product->id === $id) {
                return $this->present($product);
            }
        }

        throw new ProductNotFound("There is no product with id {$id}.");
    }

    /**
     * Every category once, in the order the source first mentions it.
     *
     * @return list<string>
     */
    public function categories(): array
    {
        return array_values(array_unique(
            array_map(fn (Product $product) => $product->category, $this->products()),
        ));
    }

    /**
     * The products of one category: its featured products first, in the
     * configured order, then the rest as the source returned them. A category
     * is known when the source has at least one product in it; the name must
     * match exactly.
     *
     * @return list<Product>
     *
     * @throws CategoryNotFound when no product is in that category
     */
    public function inCategory(string $category): array
    {
        $inCategory = array_values(array_filter(
            $this->products(),
            fn (Product $product) => $product->category === $category,
        ));

        if ($inCategory === []) {
            throw new CategoryNotFound("There is no category called {$category}.");
        }

        $featured = [];
        foreach ($this->featuredProductIds as $id) {
            foreach ($inCategory as $product) {
                if ($product->id === $id) {
                    $featured[] = $product;
                }
            }
        }

        $rest = array_filter($inCategory, fn (Product $product) => ! in_array($product, $featured, true));

        return array_map($this->present(...), [...$featured, ...$rest]);
    }

    /**
     * Products whose title or description contains the term, ignoring case, in
     * source order. The term is matched literally and surrounding spaces are
     * ignored. A blank term finds nothing, so a visitor is never shown the
     * whole catalogue as if it were a result.
     *
     * @return list<Product>
     */
    public function search(string $term): array
    {
        $term = trim($term);
        if ($term === '') {
            return [];
        }

        $matches = array_filter(
            $this->products(),
            fn (Product $product) => mb_stripos($product->title, $term) !== false
                || mb_stripos($product->description, $term) !== false,
        );

        return array_map($this->present(...), array_values($matches));
    }

    /**
     * How a product is shown to a visitor. Featured is the only highlight, so a
     * featured product carries no rating wherever it appears. Every method that
     * returns products to show goes through here, so the rule lives once.
     */
    private function present(Product $product): Product
    {
        return in_array($product->id, $this->featuredProductIds, true)
            ? $product->withoutRating()
            : $product;
    }

    /**
     * Every product as the source returns them, reused for a few minutes so
     * page views do not each call the source. Only the source's own data is
     * cached; the featured list, ordering and rating hiding are applied on top
     * afterwards, so a changed setting is never stale. A failed read throws
     * and is therefore never cached: the next view tries the source again.
     *
     * The cache holds plain arrays, not Product objects: cache stores
     * serialize what they keep and do not turn it back into arbitrary objects.
     *
     * @return list<Product>
     *
     * @throws ProductSourceUnavailable when the source cannot be read
     */
    private function products(): array
    {
        // A failed read of the source throws from here, uncaught and uncached.
        $rows = $this->cachedRows();

        try {
            return array_map(Product::fromArray(...), $rows);
        } catch (Throwable) {
            // The stored entry is not what we wrote (damaged, or from another
            // version of the code). Drop it and read the source afresh rather
            // than failing every view until it expires.
            $this->cache->forget(self::CACHE_KEY);

            return array_map(Product::fromArray(...), $this->cachedRows());
        }
    }

    /**
     * What the cache holds, or what the source just returned. Typed `mixed`
     * because a cache can hand back anything; products() checks it.
     *
     * @return list<array<string, mixed>>|mixed
     */
    private function cachedRows(): mixed
    {
        return $this->cache->remember(
            self::CACHE_KEY,
            $this->cacheSeconds,
            fn () => array_map(fn (Product $product) => $product->toArray(), $this->source->all()),
        );
    }
}
