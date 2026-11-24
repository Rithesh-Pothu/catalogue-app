<?php

namespace App\Catalogue;

/**
 * What the shop window shows. Controllers ask this module and never apply
 * catalogue rules themselves.
 */
final class Catalogue
{
    public function __construct(private readonly ProductSource $source) {}

    /**
     * The featured products, in the configured order. Featured is the only
     * highlight, so a featured product carries no rating.
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
        foreach (config('catalogue.featured_product_ids') as $id) {
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
