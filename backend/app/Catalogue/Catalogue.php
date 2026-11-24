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
     * The featured products, in the configured order. A featured id the
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
        foreach ($this->source->all() as $product) {
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
            array_map(fn (Product $product) => $product->category, $this->source->all()),
        ));
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
}
