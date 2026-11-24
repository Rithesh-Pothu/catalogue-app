<?php

namespace App\Catalogue;

/**
 * What the shop window shows. Controllers ask this module and never apply
 * catalogue rules themselves.
 */
final class Catalogue
{
    /**
     * @param  list<int>  $featuredProductIds  the featured products, in display order
     */
    public function __construct(
        private readonly ProductSource $source,
        private readonly array $featuredProductIds,
    ) {}

    /**
     * The featured products, in the configured order. Featured is the only
     * highlight, so a featured product carries no rating. A featured id the
     * source does not have is skipped.
     *
     * @return list<Product>
     */
    public function featured(): array
    {
        $byId = [];
        foreach ($this->source->all() as $product) {
            $byId[$product->id] = $product;
        }

        $featured = [];
        foreach ($this->featuredProductIds as $id) {
            if (isset($byId[$id])) {
                $featured[] = $byId[$id]->withoutRating();
            }
        }

        return $featured;
    }

    /**
     * Every category once, in the order the source first mentions it.
     *
     * @return list<string>
     */
    public function categories(): array
    {
        return array_values(array_unique(
            array_map(fn (Product $product) => $product->category, $this->source->all()),
        ));
    }
}
