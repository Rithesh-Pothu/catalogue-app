<?php

namespace App\Catalogue;

/**
 * A product source that holds its products in memory. Used by tests, and a
 * handy stand-in when the real source is unavailable.
 */
final class InMemoryProductSource implements ProductSource
{
    /**
     * @param  list<Product>  $products
     */
    public function __construct(private readonly array $products = []) {}

    public function all(): array
    {
        return $this->products;
    }
}
