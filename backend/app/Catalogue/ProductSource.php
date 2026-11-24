<?php

namespace App\Catalogue;

/**
 * The seam between the catalogue and wherever products come from (ADR 0001).
 * To change the data source, add one implementation and rebind it.
 */
interface ProductSource
{
    /**
     * Every product, in the order the source returns them.
     *
     * @return list<Product>
     *
     * @throws ProductSourceUnavailable when the source cannot be read
     */
    public function all(): array;
}
